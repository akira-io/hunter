<?php

use App\DataTransferObjects\Search\GlobalSearchResponseDto;
use App\Models\Hunt;
use App\Models\User;
use App\Services\Search\GlobalSearchService;
use App\Services\Search\Providers\HuntSearchProvider;
use App\Services\Search\Providers\UserSearchProvider;

describe('GlobalSearchService', function () {
    beforeEach(function () {
        $this->service = new GlobalSearchService(
            new UserSearchProvider,
            new HuntSearchProvider,
        );
    });

    describe('Search Functionality', function () {
        test('returns GlobalSearchResponseDto', function () {
            $result = $this->service->search('test');

            expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
        });

        test('returns empty groups for empty query', function () {
            $result = $this->service->search('');

            expect($result->groups)->toBeEmpty();
        });

        test('returns empty groups for single character query', function () {
            $result = $this->service->search('a');

            expect($result->groups)->toBeEmpty();
        });

        test('returns empty groups for whitespace only', function () {
            $result = $this->service->search('   ');

            expect($result->groups)->toBeEmpty();
        });

        test('handles special characters in query', function () {
            $result = $this->service->search('test@#$%');

            expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
        });

        test('handles unicode characters in query', function () {
            $result = $this->service->search('João');

            expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
        });

        test('handles very long query strings', function () {
            $longQuery = str_repeat('test ', 100);
            $result = $this->service->search($longQuery);

            expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
        });

        test('respects limit per provider', function () {
            User::factory()->count(20)->create();
            Hunt::factory()->count(20)->create();

            $result = $this->service->search('test', limitPerProvider: 3);

            foreach ($result->groups as $group) {
                expect($group->results->count())->toBeLessThanOrEqual(3);
            }
        })->skip('Requires Meilisearch');
    });

    describe('Group Behavior', function () {
        test('groups are ordered by priority', function () {
            User::factory()->create(['name' => 'TestUser '.uniqid()]);
            Hunt::factory()->create(['content' => 'TestHunt '.uniqid()]);

            $result = $this->service->search('Test');

            if (count($result->groups) > 1) {
                $priorities = array_map(fn ($group) => $group->priority, $result->groups);
                $sortedPriorities = $priorities;
                sort($sortedPriorities);

                expect($priorities)->toBe($sortedPriorities);
            }
        })->skip('Requires Meilisearch');

        test('filters out empty groups', function () {
            // Create only users, no hunts
            User::factory()->create(['name' => 'OnlyUser '.uniqid()]);

            $result = $this->service->search('OnlyUser');

            foreach ($result->groups as $group) {
                expect($group->results)->not->toBeEmpty();
            }
        })->skip('Requires Meilisearch');

        test('each group has required properties', function () {
            $result = $this->service->search('test');

            foreach ($result->groups as $group) {
                expect($group->type)->toBeString()->not->toBeEmpty();
                expect($group->label)->toBeString()->not->toBeEmpty();
                expect($group->icon)->toBeString()->not->toBeEmpty();
                expect($group->results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
                expect($group->priority)->toBeInt()->toBeGreaterThan(0);
            }
        })->skip('Requires Meilisearch');

        test('group types are unique', function () {
            User::factory()->create(['name' => 'Test '.uniqid()]);
            Hunt::factory()->create(['content' => 'Test '.uniqid()]);

            $result = $this->service->search('Test');

            $types = array_map(fn ($group) => $group->type, $result->groups);
            $uniqueTypes = array_unique($types);

            expect(count($types))->toBe(count($uniqueTypes));
        })->skip('Requires Meilisearch');
    });

    describe('Result Structure', function () {
        test('toArray returns valid structure', function () {
            $result = $this->service->search('test');
            $array = $result->toArray();

            expect($array)->toHaveKey('groups');
            expect($array['groups'])->toBeArray();
        });

        test('groups can be serialized to JSON', function () {
            $result = $this->service->search('test');
            $json = json_encode($result->toArray());

            expect($json)->toBeString();
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });

        test('each result has all required fields', function () {
            User::factory()->create(['name' => 'ResultTest '.uniqid()]);

            $result = $this->service->search('ResultTest');

            foreach ($result->groups as $group) {
                foreach ($group->results as $item) {
                    expect($item->id)->not->toBeEmpty();
                    expect($item->title)->not->toBeEmpty();
                    expect($item->url)->not->toBeEmpty();
                    expect($item->metadata)->toBeArray();
                }
            }
        })->skip('Requires Meilisearch');
    });

    describe('Edge Cases', function () {
        test('handles null gracefully', function () {
            expect(fn () => $this->service->search(null))
                ->toThrow(\TypeError::class);
        });

        test('handles numeric query', function () {
            $result = $this->service->search('123');

            expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
        });

        test('handles query with only numbers', function () {
            $result = $this->service->search('12345678');

            expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
        });

        test('handles query with mixed case', function () {
            $result = $this->service->search('TeSt');

            expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
        });

        test('handles zero limit', function () {
            $result = $this->service->search('test', limitPerProvider: 0);

            foreach ($result->groups as $group) {
                expect($group->results)->toBeEmpty();
            }
        })->skip('Requires Meilisearch');

        test('handles negative limit as zero', function () {
            $result = $this->service->search('test', limitPerProvider: -1);

            foreach ($result->groups as $group) {
                expect($group->results)->toBeEmpty();
            }
        })->skip('Requires Meilisearch');
    });

    describe('Performance', function () {
        test('completes search in reasonable time', function () {
            $start = microtime(true);
            $this->service->search('test');
            $duration = microtime(true) - $start;

            expect($duration)->toBeLessThan(5); // 5 seconds max
        });

        test('handles concurrent searches without errors', function () {
            // Simulate multiple searches
            for ($i = 0; $i < 5; $i++) {
                $result = $this->service->search("test{$i}");
                expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
            }
        });
    });
});

describe('GlobalSearchService Integration', function () {
    test('service is registered as singleton in container', function () {
        $service1 = app(GlobalSearchService::class);
        $service2 = app(GlobalSearchService::class);

        expect($service1)->toBe($service2);
    });

    test('can resolve service from container', function () {
        $service = app(GlobalSearchService::class);

        expect($service)->toBeInstanceOf(GlobalSearchService::class);
    });

    test('service has required providers injected', function () {
        $service = app(GlobalSearchService::class);
        $result = $service->search('test');

        expect($result)->toBeInstanceOf(GlobalSearchResponseDto::class);
    });
});
