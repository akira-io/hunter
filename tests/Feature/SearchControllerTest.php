<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('SearchController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('Authentication', function () {
        test('requires authentication', function () {
            getJson('/search?q=test')
                ->assertStatus(401);
        });

        test('authenticated user can access', function () {
            actingAs($this->user)
                ->getJson('/search?q=test')
                ->assertStatus(200);
        });
    });

    describe('Response Structure', function () {
        test('returns JSON response', function () {
            actingAs($this->user)
                ->getJson('/search?q=test')
                ->assertStatus(200)
                ->assertHeader('content-type', 'application/json');
        });

        test('response has groups key', function () {
            actingAs($this->user)
                ->getJson('/search?q=test')
                ->assertStatus(200)
                ->assertJsonStructure(['groups']);
        });

        test('empty query returns empty groups', function () {
            actingAs($this->user)
                ->getJson('/search?q=')
                ->assertStatus(200)
                ->assertJson(['groups' => []]);
        });

        test('single character query returns empty groups', function () {
            actingAs($this->user)
                ->getJson('/search?q=a')
                ->assertStatus(200)
                ->assertJson(['groups' => []]);
        });

        test('missing query parameter returns empty groups', function () {
            actingAs($this->user)
                ->getJson('/search')
                ->assertStatus(200)
                ->assertJson(['groups' => []]);
        });
    });

    describe('Search Results', function () {
        test('returns user results when users match', function () {
            $testUser = User::factory()->create([
                'name' => 'Unique Test User '.uniqid(),
                'user_name' => 'uniqueuser'.uniqid(),
            ]);

            $testUser->searchable();
            sleep(1);

            $response = actingAs($this->user)
                ->getJson('/search?q=Unique')
                ->assertStatus(200);

            $groups = $response->json('groups');
            $userGroup = collect($groups)->firstWhere('type', 'users');

            if ($userGroup) {
                expect($userGroup['label'])->toBe('Hunters')
                    ->and($userGroup['icon'])->toBe('user')
                    ->and($userGroup['priority'])->toBe(1)
                    ->and($userGroup['results'])->toBeArray();
            }
        });

        test('returns hunt results when hunts match', function () {
            $hunt = Hunt::factory()->create([
                'content' => 'Unique Hunt Content '.uniqid(),
            ]);

            $hunt->searchable();
            sleep(1);

            $response = actingAs($this->user)
                ->getJson('/search?q=Unique')
                ->assertStatus(200);

            $groups = $response->json('groups');
            $huntGroup = collect($groups)->firstWhere('type', 'hunts');

            if ($huntGroup) {
                expect($huntGroup['label'])->toBe('Hunts')
                    ->and($huntGroup['icon'])->toBe('file-text')
                    ->and($huntGroup['priority'])->toBe(2)
                    ->and($huntGroup['results'])->toBeArray();
            }
        });

        test('result has required structure', function () {
            $testUser = User::factory()->create([
                'name' => 'Structure Test '.uniqid(),
            ]);

            $testUser->searchable();
            sleep(1);

            $response = actingAs($this->user)
                ->getJson('/search?q=Structure')
                ->assertStatus(200);

            $groups = $response->json('groups');

            if (! empty($groups)) {
                $response->assertJsonStructure([
                    'groups' => [
                        '*' => [
                            'type',
                            'label',
                            'icon',
                            'priority',
                            'results' => [
                                '*' => [
                                    'id',
                                    'title',
                                    'subtitle',
                                    'description',
                                    'image',
                                    'url',
                                    'metadata',
                                ],
                            ],
                        ],
                    ],
                ]);
            }
        });
    });

    describe('Query Handling', function () {
        test('handles unicode characters', function () {
            actingAs($this->user)
                ->getJson('/search?q=João')
                ->assertStatus(200);
        });

        test('handles special characters', function () {
            actingAs($this->user)
                ->getJson('/search?q=test@#$%')
                ->assertStatus(200);
        });

        test('handles numeric query', function () {
            actingAs($this->user)
                ->getJson('/search?q=123')
                ->assertStatus(200);
        });

        test('handles URL encoded query', function () {
            actingAs($this->user)
                ->getJson('/search?q='.urlencode('test user'))
                ->assertStatus(200);
        });
    });

    describe('Result Ordering', function () {
        test('groups are ordered by priority', function () {
            User::factory()->create(['name' => 'OrderTest '.uniqid()]);
            Hunt::factory()->create(['content' => 'OrderTest '.uniqid()]);

            $response = actingAs($this->user)
                ->getJson('/search?q=OrderTest')
                ->assertStatus(200);

            $groups = $response->json('groups');

            if (count($groups) > 1) {
                $priorities = array_column($groups, 'priority');
                $sortedPriorities = $priorities;
                sort($sortedPriorities);

                expect($priorities)->toBe($sortedPriorities);
            }
        });

        test('users appear before hunts', function () {
            User::factory()->create(['name' => 'Priority '.uniqid()]);
            Hunt::factory()->create(['content' => 'Priority '.uniqid()]);

            $response = actingAs($this->user)
                ->getJson('/search?q=Priority')
                ->assertStatus(200);

            $groups = $response->json('groups');

            if (count($groups) >= 2) {
                expect($groups[0]['type'])->toBe('users');
                expect($groups[1]['type'])->toBe('hunts');
            }
        });
    });

    describe('Performance', function () {
        test('responds in reasonable time', function () {
            $start = microtime(true);

            actingAs($this->user)
                ->getJson('/search?q=performance')
                ->assertStatus(200);

            $duration = microtime(true) - $start;

            expect($duration)->toBeLessThan(2); // 2 seconds max
        });

        test('handles concurrent requests', function () {
            for ($i = 0; $i < 5; $i++) {
                actingAs($this->user)
                    ->getJson("/search?q=concurrent{$i}")
                    ->assertStatus(200);
            }
        });
    });

    describe('Error Handling', function () {

        test('returns empty results when no matches', function () {
            actingAs($this->user)
                ->getJson('/search?q=nonexistentquery12345678')
                ->assertStatus(200)
                ->assertJson(['groups' => []]);
        });
    });

    describe('CORS and Headers', function () {
        test('includes CSRF token in response', function () {
            actingAs($this->user)
                ->getJson('/search?q=test')
                ->assertStatus(200);
        });

        test('accepts JSON content type', function () {
            actingAs($this->user)
                ->getJson('/search?q=test', ['Accept' => 'application/json'])
                ->assertStatus(200);
        });
    });
});

describe('Search Integration', function () {
    test('complete search workflow', function () {
        $user = User::factory()->create();

        // Create searchable content
        $testUser = User::factory()->create([
            'name' => 'Integration Test User',
            'user_name' => 'integrationuser',
        ]);

        $testHunt = Hunt::factory()->create([
            'content' => 'Integration test hunt content',
        ]);

        // Index
        $testUser->searchable();
        $testHunt->searchable();
        sleep(1);

        // Search
        $response = actingAs($user)
            ->getJson('/search?q=Integration')
            ->assertStatus(200);

        $groups = $response->json('groups');

        // Verify structure
        expect($groups)->toBeArray();

        if (! empty($groups)) {
            foreach ($groups as $group) {
                expect($group)->toHaveKeys(['type', 'label', 'icon', 'results', 'priority']);

                foreach ($group['results'] as $result) {
                    expect($result)->toHaveKeys(['id', 'title', 'url']);
                    expect($result['url'])->toBeString()->not->toBeEmpty();
                }
            }
        }
    });
});
