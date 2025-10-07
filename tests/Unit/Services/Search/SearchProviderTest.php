<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use App\Services\Search\Providers\HuntSearchProvider;
use App\Services\Search\Providers\UserSearchProvider;

use function Pest\Laravel\actingAs;

describe('UserSearchProvider', function () {
    beforeEach(function () {
        $this->provider = new UserSearchProvider;
    });

    describe('Contract Implementation', function () {
        test('implements GlobalSearchable interface', function () {
            expect($this->provider)->toBeInstanceOf(App\Contracts\Search\GlobalSearchable::class);
        });

        test('getType returns correct value', function () {
            expect($this->provider->getType())->toBe('users');
        });

        test('getLabel returns correct value', function () {
            expect($this->provider->getLabel())->toBe('Hunters');
        });

        test('getIcon returns correct value', function () {
            expect($this->provider->getIcon())->toBe('user');
        });

        test('getPriority returns positive integer', function () {
            expect($this->provider->getPriority())
                ->toBeInt()
                ->toBe(1);
        });
    });

    describe('getRedirectUrl', function () {
        test('returns valid URL for user', function () {
            $user = User::factory()->create();
            $url = $this->provider->getRedirectUrl($user);

            expect($url)
                ->toBeString()
                ->toContain('/public-profile/')
                ->toContain((string) $user->id);
        });

        test('returns different URLs for different users', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            $url1 = $this->provider->getRedirectUrl($user1);
            $url2 = $this->provider->getRedirectUrl($user2);

            expect($url1)->not->toBe($url2);
        });

        test('throws exception for non-user model', function () {
            $hunt = Hunt::factory()->create();

            expect(fn () => $this->provider->getRedirectUrl($hunt))
                ->toThrow(InvalidArgumentException::class);
        });

        test('URL is accessible', function () {
            $user = User::factory()->create();
            $url = $this->provider->getRedirectUrl($user);

            actingAs($user)
                ->get($url)
                ->assertSuccessful();
        });
    });

    describe('mapToDto', function () {
        test('returns SearchResult DTO with correct structure', function () {
            $user = User::factory()->create([
                'name' => 'John Doe',
                'user_name' => 'johndoe',
                'location' => 'New York',
                'bio' => 'Software Developer',
                'skills' => ['PHP', 'Laravel'],
            ]);

            $dto = $this->provider->mapToSearchResults($user);

            expect($dto)->toBeInstanceOf(App\DataTransferObjects\Search\SearchResult::class)
                ->and($dto->id)->toBe((string) $user->id)
                ->and($dto->title)->toBe('John Doe')
                ->and($dto->subtitle)->toBe('@johndoe')
                ->and($dto->description)->toBe('New York')
                ->and($dto->image)->toBe($user->avatar_url)
                ->and($dto->metadata)->toHaveKey('bio')
                ->and($dto->metadata)->toHaveKey('skills')
                ->and($dto->metadata)->toHaveKey('location');
        });

        test('handles null values correctly', function () {
            $user = User::factory()->create([
                'location' => null,
                'bio' => null,
                'avatar_url' => null,
            ]);

            $dto = $this->provider->mapToSearchResults($user);

            expect($dto->description)->toBeNull()
                ->and($dto->image)->toBeNull()
                ->and($dto->metadata['bio'])->toBeNull();
        });
    });

    describe('buildRedirectUrl', function () {
        test('builds correct URL for user', function () {
            $user = User::factory()->create();
            $url = $this->provider->buildRedirectUrl($user);

            expect($url)
                ->toBeString()
                ->toContain('/public-profile/')
                ->toContain((string) $user->id);
        });
    });

    describe('getModelClass', function () {
        test('returns User model class', function () {
            expect($this->provider->getModelClass())->toBe(User::class);
        });
    });

    describe('search', function () {
        test('returns collection', function () {
            $results = $this->provider->search('test');

            expect($results)->toBeInstanceOf(Illuminate\Support\Collection::class);
        });

        test('returns empty collection for short query', function () {
            expect($this->provider->search(''))->toBeEmpty();
            expect($this->provider->search('a'))->toBeEmpty();
        });
    });
});

describe('HuntSearchProvider', function () {
    beforeEach(function () {
        $this->provider = new HuntSearchProvider;
    });

    describe('Contract Implementation', function () {
        test('implements GlobalSearchable interface', function () {
            expect($this->provider)->toBeInstanceOf(App\Contracts\Search\GlobalSearchable::class);
        });

        test('getType returns correct value', function () {
            expect($this->provider->getType())->toBe('hunts');
        });

        test('getLabel returns correct value', function () {
            expect($this->provider->getLabel())->toBe('Hunts');
        });

        test('getIcon returns correct value', function () {
            expect($this->provider->getIcon())->toBe('file-text');
        });

        test('getPriority returns positive integer', function () {
            expect($this->provider->getPriority())
                ->toBeInt()
                ->toBe(2);
        });
    });

    describe('getRedirectUrl', function () {
        test('returns valid URL for hunt', function () {
            $hunt = Hunt::factory()->create();
            $url = $this->provider->getRedirectUrl($hunt);

            expect($url)
                ->toBeString()
                ->toContain('/hunts')
                ->toContain($hunt->id);
        });

        test('throws exception for non-hunt model', function () {
            $user = User::factory()->create();

            expect(fn () => $this->provider->getRedirectUrl($user))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('mapToDto', function () {
        test('returns SearchResult DTO with correct structure', function () {
            $owner = User::factory()->create(['name' => 'Jane Doe', 'user_name' => 'janedoe']);
            $hunt = Hunt::factory()->create([
                'content' => 'Looking for developers',
                'owner_id' => $owner->id,
            ]);

            $dto = $this->provider->mapToSearchResults($hunt);

            expect($dto)->toBeInstanceOf(App\DataTransferObjects\Search\SearchResult::class)
                ->and($dto->id)->toBe((string) $hunt->id)
                ->and($dto->title)->toBe('Looking for developers')
                ->and($dto->subtitle)->toBe('por Jane Doe')
                ->and($dto->image)->toBe($owner->avatar_url)
                ->and($dto->metadata)->toHaveKey('owner_id')
                ->and($dto->metadata)->toHaveKey('owner_name')
                ->and($dto->metadata)->toHaveKey('owner_username')
                ->and($dto->metadata)->toHaveKey('created_at');
        });

        test('includes owner information', function () {
            $owner = User::factory()->create(['name' => 'Test Owner', 'user_name' => 'testowner']);
            $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

            $dto = $this->provider->mapToSearchResults($hunt);

            expect($dto->metadata['owner_id'])->toBe($owner->id)
                ->and($dto->metadata['owner_name'])->toBe('Test Owner')
                ->and($dto->metadata['owner_username'])->toBe('testowner');
        });
    });

    describe('buildRedirectUrl', function () {
        test('builds correct URL with anchor for hunt', function () {
            $hunt = Hunt::factory()->create();
            $url = $this->provider->buildRedirectUrl($hunt);

            expect($url)
                ->toBeString()
                ->toContain('/hunts')
                ->toContain($hunt->id);
        });
    });

    describe('getModelClass', function () {
        test('returns Hunt model class', function () {
            expect($this->provider->getModelClass())->toBe(Hunt::class);
        });
    });

    describe('search', function () {
        test('returns collection', function () {
            $results = $this->provider->search('test');

            expect($results)->toBeInstanceOf(Illuminate\Support\Collection::class);
        });

        test('returns empty collection for short query', function () {
            expect($this->provider->search(''))->toBeEmpty();
        });
    });
});

describe('Search Provider Priority', function () {
    test('users have higher priority than hunts', function () {
        $userProvider = new UserSearchProvider;
        $huntProvider = new HuntSearchProvider;

        expect($userProvider->getPriority())
            ->toBeLessThan($huntProvider->getPriority());
    });
});
