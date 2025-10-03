<?php

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
            expect($this->provider)->toBeInstanceOf(\App\Contracts\Search\GlobalSearchable::class);
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
                ->toThrow(\InvalidArgumentException::class);
        });

        test('URL is accessible', function () {
            $user = User::factory()->create();
            $url = $this->provider->getRedirectUrl($user);

            actingAs($user)
                ->get($url)
                ->assertSuccessful();
        });
    });

    describe('search', function () {
        test('returns collection', function () {
            $results = $this->provider->search('test');

            expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
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
            expect($this->provider)->toBeInstanceOf(\App\Contracts\Search\GlobalSearchable::class);
        });

        test('getType returns correct value', function () {
            expect($this->provider->getType())->toBe('hunts');
        });

        test('getLabel returns correct value', function () {
            expect($this->provider->getLabel())->toBe('Projetos');
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
                ->toContain('#hunt-')
                ->toContain((string) $hunt->id);
        });

        test('throws exception for non-hunt model', function () {
            $user = User::factory()->create();

            expect(fn () => $this->provider->getRedirectUrl($user))
                ->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('search', function () {
        test('returns collection', function () {
            $results = $this->provider->search('test');

            expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
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
