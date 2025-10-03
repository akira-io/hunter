<?php

declare(strict_types=1);

use App\DataTransferObjects\Search\GlobalSearchResponseDto;
use App\DataTransferObjects\Search\SearchGroupDto;
use App\DataTransferObjects\Search\SearchResult;

describe('SearchResultDto', function () {
    test('can be instantiated with all properties', function () {
        $dto = new SearchResult(
            id: '123',
            title: 'Test Title',
            subtitle: 'Test Subtitle',
            description: 'Test Description',
            image: 'https://example.com/image.jpg',
            url: '/test-url',
            metadata: ['key' => 'value'],
        );

        expect($dto->id)->toBe('123');
        expect($dto->title)->toBe('Test Title');
        expect($dto->subtitle)->toBe('Test Subtitle');
        expect($dto->description)->toBe('Test Description');
        expect($dto->image)->toBe('https://example.com/image.jpg');
        expect($dto->url)->toBe('/test-url');
        expect($dto->metadata)->toBe(['key' => 'value']);
    });

    test('can be instantiated with null optional fields', function () {
        $dto = new SearchResult(
            id: '123',
            title: 'Test Title',
            subtitle: null,
            description: null,
            image: null,
            url: '/test-url',
            metadata: [],
        );

        expect($dto->subtitle)->toBeNull();
        expect($dto->description)->toBeNull();
        expect($dto->image)->toBeNull();
    });

    test('toArray returns all properties', function () {
        $dto = new SearchResult(
            id: '123',
            title: 'Test Title',
            subtitle: 'Test Subtitle',
            description: 'Test Description',
            image: 'https://example.com/image.jpg',
            url: '/test-url',
            metadata: ['key' => 'value'],
        );

        $array = $dto->toArray();

        expect($array)->toHaveKeys(['id', 'title', 'subtitle', 'description', 'image', 'url', 'metadata']);
        expect($array['id'])->toBe('123');
        expect($array['title'])->toBe('Test Title');
        expect($array['metadata'])->toBe(['key' => 'value']);
    });

    test('toArray preserves null values', function () {
        $dto = new SearchResult(
            id: '123',
            title: 'Test Title',
            subtitle: null,
            description: null,
            image: null,
            url: '/test-url',
        );

        $array = $dto->toArray();

        expect($array['subtitle'])->toBeNull();
        expect($array['description'])->toBeNull();
        expect($array['image'])->toBeNull();
    });

    test('is readonly', function () {
        $dto = new SearchResult(
            id: '123',
            title: 'Test Title',
            subtitle: null,
            description: null,
            image: null,
            url: '/test-url',
        );

        expect(fn () => $dto->id = '456')->toThrow(Error::class);
    });

    test('can be serialized to JSON', function () {
        $dto = new SearchResult(
            id: '123',
            title: 'Test Title',
            subtitle: 'Test Subtitle',
            description: 'Test Description',
            image: 'https://example.com/image.jpg',
            url: '/test-url',
            metadata: ['key' => 'value'],
        );

        $json = json_encode($dto->toArray());

        expect($json)->toBeString();
        expect(json_last_error())->toBe(JSON_ERROR_NONE);

        $decoded = json_decode($json, true);
        expect($decoded['id'])->toBe('123');
        expect($decoded['title'])->toBe('Test Title');
    });

    test('handles empty metadata', function () {
        $dto = new SearchResult(
            id: '123',
            title: 'Test',
            subtitle: null,
            description: null,
            image: null,
            url: '/test',
            metadata: [],
        );

        expect($dto->metadata)->toBeArray()->toBeEmpty();
    });

    test('handles complex metadata', function () {
        $metadata = [
            'nested' => ['key' => 'value'],
            'array' => [1, 2, 3],
            'string' => 'test',
            'number' => 123,
            'bool' => true,
        ];

        $dto = new SearchResult(
            id: '123',
            title: 'Test',
            subtitle: null,
            description: null,
            image: null,
            url: '/test',
            metadata: $metadata,
        );

        expect($dto->metadata)->toBe($metadata);
    });
});

describe('SearchGroupDto', function () {
    test('can be instantiated with all properties', function () {
        $results = collect([
            new SearchResult('1', 'Title 1', null, null, null, '/url-1'),
            new SearchResult('2', 'Title 2', null, null, null, '/url-2'),
        ]);

        $dto = new SearchGroupDto(
            type: 'users',
            label: 'Hunters',
            icon: 'user',
            results: $results,
            priority: 1,
        );

        expect($dto->type)->toBe('users');
        expect($dto->label)->toBe('Hunters');
        expect($dto->icon)->toBe('user');
        expect($dto->results)->toBe($results);
        expect($dto->priority)->toBe(1);
    });

    test('toArray returns all properties with mapped results', function () {
        $results = collect([
            new SearchResult('1', 'Title 1', null, null, null, '/url-1'),
            new SearchResult('2', 'Title 2', null, null, null, '/url-2'),
        ]);

        $dto = new SearchGroupDto(
            type: 'users',
            label: 'Hunters',
            icon: 'user',
            results: $results,
            priority: 1,
        );

        $array = $dto->toArray();

        expect($array)->toHaveKeys(['type', 'label', 'icon', 'results', 'priority']);
        expect($array['type'])->toBe('users');
        expect($array['results'])->toBeArray();
        expect($array['results'])->toHaveCount(2);
        expect($array['results'][0])->toHaveKey('id');
        expect($array['results'][0]['id'])->toBe('1');
    });

    test('handles empty results collection', function () {
        $dto = new SearchGroupDto(
            type: 'users',
            label: 'Hunters',
            icon: 'user',
            results: collect(),
            priority: 1,
        );

        $array = $dto->toArray();

        expect($array['results'])->toBeArray()->toBeEmpty();
    });

    test('is readonly', function () {
        $dto = new SearchGroupDto(
            type: 'users',
            label: 'Hunters',
            icon: 'user',
            results: collect(),
            priority: 1,
        );

        expect(fn () => $dto->type = 'hunts')->toThrow(Error::class);
    });

    test('can be serialized to JSON', function () {
        $results = collect([
            new SearchResult('1', 'Title', null, null, null, '/url'),
        ]);

        $dto = new SearchGroupDto(
            type: 'users',
            label: 'Hunters',
            icon: 'user',
            results: $results,
            priority: 1,
        );

        $json = json_encode($dto->toArray());

        expect($json)->toBeString();
        expect(json_last_error())->toBe(JSON_ERROR_NONE);
    });
});

describe('GlobalSearchResponseDto', function () {
    test('can be instantiated with groups', function () {
        $groups = [
            new SearchGroupDto('users', 'Hunters', 'user', collect(), 1),
            new SearchGroupDto('hunts', 'Projetos', 'file-text', collect(), 2),
        ];

        $dto = new GlobalSearchResponseDto(groups: $groups);

        expect($dto->groups)->toBe($groups);
    });

    test('can be instantiated with empty groups', function () {
        $dto = new GlobalSearchResponseDto(groups: []);

        expect($dto->groups)->toBeArray()->toBeEmpty();
    });

    test('toArray returns groups array', function () {
        $groups = [
            new SearchGroupDto('users', 'Hunters', 'user', collect(), 1),
        ];

        $dto = new GlobalSearchResponseDto(groups: $groups);
        $array = $dto->toArray();

        expect($array)->toHaveKey('groups');
        expect($array['groups'])->toBeArray();
        expect($array['groups'])->toHaveCount(1);
    });

    test('toArray maps all groups correctly', function () {
        $results = collect([
            new SearchResult('1', 'Title', null, null, null, '/url'),
        ]);

        $groups = [
            new SearchGroupDto('users', 'Hunters', 'user', $results, 1),
            new SearchGroupDto('hunts', 'Projetos', 'file-text', collect(), 2),
        ];

        $dto = new GlobalSearchResponseDto(groups: $groups);
        $array = $dto->toArray();

        expect($array['groups'][0])->toHaveKey('type');
        expect($array['groups'][0]['type'])->toBe('users');
        expect($array['groups'][0]['results'])->toHaveCount(1);
        expect($array['groups'][1]['type'])->toBe('hunts');
        expect($array['groups'][1]['results'])->toBeEmpty();
    });

    test('is readonly', function () {
        $dto = new GlobalSearchResponseDto(groups: []);

        expect(fn () => $dto->groups = [])->toThrow(Error::class);
    });

    test('can be serialized to JSON', function () {
        $results = collect([
            new SearchResult('1', 'Title', null, null, null, '/url'),
        ]);

        $groups = [
            new SearchGroupDto('users', 'Hunters', 'user', $results, 1),
        ];

        $dto = new GlobalSearchResponseDto(groups: $groups);
        $json = json_encode($dto->toArray());

        expect($json)->toBeString();
        expect(json_last_error())->toBe(JSON_ERROR_NONE);

        $decoded = json_decode($json, true);
        expect($decoded)->toHaveKey('groups');
        expect($decoded['groups'])->toHaveCount(1);
    });

    test('handles complex nested structure', function () {
        $results1 = collect([
            new SearchResult('1', 'User 1', 'subtitle', 'desc', 'img', '/u1', ['meta' => 'data']),
            new SearchResult('2', 'User 2', null, null, null, '/u2'),
        ]);

        $results2 = collect([
            new SearchResult('3', 'Hunt 1', 'by User', '2 hours ago', null, '/h1'),
        ]);

        $groups = [
            new SearchGroupDto('users', 'Hunters', 'user', $results1, 1),
            new SearchGroupDto('hunts', 'Projetos', 'file-text', $results2, 2),
        ];

        $dto = new GlobalSearchResponseDto(groups: $groups);
        $array = $dto->toArray();

        expect($array['groups'])->toHaveCount(2);
        expect($array['groups'][0]['results'])->toHaveCount(2);
        expect($array['groups'][1]['results'])->toHaveCount(1);
        expect($array['groups'][0]['results'][0]['metadata'])->toBe(['meta' => 'data']);
    });
});
