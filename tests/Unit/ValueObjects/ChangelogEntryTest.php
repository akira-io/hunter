<?php

declare(strict_types=1);

use App\ValueObjects\ChangelogEntry;
use Carbon\Carbon;

it('detects unreleased as latest and formats version accordingly', function () {
    $entry = new ChangelogEntry(version: 'Unreleased', date: null, sections: [], rawContent: '');

    expect($entry->isLatest())->toBeTrue()
        ->and($entry->getFormattedVersion())->toBe('Unreleased');
});

it('formats date and human date when date is present', function () {
    $date = Carbon::create(2025, 10, 11);
    $entry = new ChangelogEntry(version: '0.1.0', date: $date, sections: [], rawContent: '');

    $formatted = $entry->getFormattedDate();
    $human = $entry->getHumanDate();

    expect($formatted)->toBeString()
        ->and($human)->toBeString();
});

it('handles sections and totals correctly', function () {
    $sections = [
        'Added' => ['Feature A', 'Feature B'],
        'Fixed' => ['Bug 1'],
    ];

    $entry = new ChangelogEntry(version: '0.2.0', date: null, sections: $sections, rawContent: 'raw');

    expect($entry->hasSection('Added'))->toBeTrue()
        ->and($entry->hasSection('Missing'))->toBeFalse()
        ->and($entry->getSection('Added'))->toBe($sections['Added'])
        ->and($entry->getSectionNames())->toEqual(['Added', 'Fixed'])
        ->and($entry->getTotalChanges())->toBe(3);

    $array = $entry->toArray();

    expect($array['version'])->toBe('0.2.0')
        ->and($array['total_changes'])->toBe(3)
        ->and($array['raw_content'])->toBe('raw');
});
