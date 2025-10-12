<?php

declare(strict_types=1);

use App\Contracts\Search\GlobalSearchable;
use App\DataTransferObjects\Search\SearchResult;
use App\Services\Search\Concerns\HasScoutSearch;
use Illuminate\Database\Eloquent\Model;

beforeEach(function () {
    // Create a test provider implementation
    $this->provider = new class implements GlobalSearchable
    {
        use HasScoutSearch;

        public function getType(): string
        {
            return 'test';
        }

        public function getLabel(): string
        {
            return 'Test';
        }

        public function getIcon(): string
        {
            return 'test-icon';
        }

        public function getPriority(): int
        {
            return 1;
        }

        public function getModelClass(): string
        {
            return App\Models\User::class;
        }

        public function mapToSearchResults(Model $model): SearchResult
        {
            return new SearchResult(
                id: (string) $model->id,
                title: 'Test',
                subtitle: null,
                description: null,
                image: null,
                metadata: []
            );
        }

        public function buildRedirectUrl(Model $model): string
        {
            return '/test/'.$model->id;
        }
    };
});

it('throws exception when model class does not have search method', function () {
    $provider = new class implements GlobalSearchable
    {
        use HasScoutSearch;

        public function getType(): string
        {
            return 'test';
        }

        public function getLabel(): string
        {
            return 'Test';
        }

        public function getIcon(): string
        {
            return 'test';
        }

        public function getPriority(): int
        {
            return 1;
        }

        public function getModelClass(): string
        {
            // Return a class without search method
            return stdClass::class;
        }

        public function mapToSearchResults(Model $model): SearchResult
        {
            return new SearchResult('1', 'Test', null, null, null, []);
        }

        public function buildRedirectUrl(Model $model): string
        {
            return '/test';
        }
    };

    expect(fn () => $provider->search('test'))
        ->toThrow(RuntimeException::class, 'Model class must have search method');
});

it('throws exception when search method does not return Scout Builder', function () {
    // Create a mock model with search method that returns wrong type
    $mockModelClass = new class extends Model
    {
        public static function search(string $query)
        {
            return 'not-a-builder';
        }
    };

    $provider = new class($mockModelClass) implements GlobalSearchable
    {
        use HasScoutSearch;

        private $mockClass;

        public function __construct($mockClass)
        {
            $this->mockClass = get_class($mockClass);
        }

        public function getType(): string
        {
            return 'test';
        }

        public function getLabel(): string
        {
            return 'Test';
        }

        public function getIcon(): string
        {
            return 'test';
        }

        public function getPriority(): int
        {
            return 1;
        }

        public function getModelClass(): string
        {
            return $this->mockClass;
        }

        public function mapToSearchResults(Model $model): SearchResult
        {
            return new SearchResult('1', 'Test', null, null, null, []);
        }

        public function buildRedirectUrl(Model $model): string
        {
            return '/test';
        }
    };

    expect(fn () => $provider->search('test'))
        ->toThrow(RuntimeException::class, 'search() must return Scout Builder instance');
});

it('default shouldIncludeInResults returns true', function () {
    // Test that the default implementation returns true for all models
    $user = App\Models\User::factory()->create();

    // Use reflection to access the protected method
    $reflection = new ReflectionClass($this->provider);
    $method = $reflection->getMethod('shouldIncludeInResults');
    $method->setAccessible(true);

    $result = $method->invoke($this->provider, $user);

    expect($result)->toBeTrue();
});

it('shouldIncludeInResults can be overridden for custom filtering', function () {
    // Create a provider with custom filtering logic
    $provider = new class implements GlobalSearchable
    {
        use HasScoutSearch;

        public function getType(): string
        {
            return 'test';
        }

        public function getLabel(): string
        {
            return 'Test';
        }

        public function getIcon(): string
        {
            return 'test';
        }

        public function getPriority(): int
        {
            return 1;
        }

        public function getModelClass(): string
        {
            return App\Models\User::class;
        }

        public function mapToSearchResults(Model $model): SearchResult
        {
            return new SearchResult((string) $model->id, 'Test', null, null, null, []);
        }

        public function buildRedirectUrl(Model $model): string
        {
            return '/test';
        }

        // Override shouldIncludeInResults with custom logic
        protected function shouldIncludeInResults(Model $model): bool
        {
            // Example: only include users with specific property
            return $model->email !== 'filtered@example.com';
        }
    };

    $normalUser = App\Models\User::factory()->create(['email' => 'normal@example.com']);
    $filteredUser = App\Models\User::factory()->create(['email' => 'filtered@example.com']);

    // Use reflection to test the protected method
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('shouldIncludeInResults');
    $method->setAccessible(true);

    expect($method->invoke($provider, $normalUser))->toBeTrue()
        ->and($method->invoke($provider, $filteredUser))->toBeFalse();
});
