<?php

declare(strict_types=1);

use App\Enums\SkillsEnum;

it('get returns array of associative arrays', function () {
    $result = SkillsEnum::get();

    expect($result)->toBeArray()->not->toBeEmpty();

    $firstItem = $result[0];
    expect(array_key_exists('value', $firstItem))->toBeTrue();
    expect(array_key_exists('label', $firstItem))->toBeTrue();
    expect($firstItem['value'])->toBe($firstItem['label']);

    expect(count($result))->toBe(count(SkillsEnum::cases()));
});

it('get from db returns formatted array', function () {
    $skills = ['PHP', 'JavaScript', 'Laravel'];
    $result = SkillsEnum::getFromDb($skills);

    expect($result)->toBeArray();
    expect(count($result))->toBe(count($skills));

    foreach ($result as $index => $item) {
        expect(array_key_exists('value', $item))->toBeTrue();
        expect(array_key_exists('label', $item))->toBeTrue();
        expect($item['value'])->toBe($skills[$index]);
        expect($item['label'])->toBe($skills[$index]);
    }
});

it('get from db with null returns empty array', function () {
    $result = SkillsEnum::getFromDb(null);

    expect($result)->toBeArray()->toBeEmpty();
});

it('get values returns array of strings', function () {
    $result = SkillsEnum::getValues();

    expect($result)->toBeArray()->not->toBeEmpty();

    foreach ($result as $item) {
        expect($item)->toBeString();
    }

    expect(count($result))->toBe(count(SkillsEnum::cases()));

    expect($result)->toContain(SkillsEnum::PHP->value);
    expect($result)->toContain(SkillsEnum::JavaScript->value);
    expect($result)->toContain(SkillsEnum::Laravel->value);
});
