<?php

declare(strict_types=1);

use App\DataTransferObjects\Profile\AcademicBackgroundData;
use App\Http\Requests\Profile\AcademicBackgroundRequest;

test('it can be created from array data', function () {
    $data = AcademicBackgroundData::fromArray([
        'degree' => 'Bachelor of Science',
        'field_of_study' => 'Computer Science',
        'start_date' => '2020-09-01',
        'end_date' => '2024-06-01',
        'institution' => 'University of Technology',
    ]);

    expect($data->degree)->toBe('Bachelor of Science')
        ->and($data->fieldOfStudy)->toBe('Computer Science')
        ->and($data->startDate)->toBe('2020-09-01')
        ->and($data->endDate)->toBe('2024-06-01')
        ->and($data->institution)->toBe('University of Technology');
});

test('it can be created from array data with null end date', function () {
    $data = AcademicBackgroundData::fromArray([
        'degree' => 'Master of Science',
        'field_of_study' => 'Data Science',
        'start_date' => '2024-09-01',
        'end_date' => null,
        'institution' => 'Graduate University',
    ]);

    expect($data->degree)->toBe('Master of Science')
        ->and($data->fieldOfStudy)->toBe('Data Science')
        ->and($data->startDate)->toBe('2024-09-01')
        ->and($data->endDate)->toBeNull()
        ->and($data->institution)->toBe('Graduate University');
});

test('it can be created from academic background request', function () {
    $request = AcademicBackgroundRequest::create('/profile/academic-background', 'POST', [
        'degree' => 'PhD',
        'field_of_study' => 'Artificial Intelligence',
        'start_date' => '2025-01-01',
        'end_date' => '2028-12-01',
        'institution' => 'Research Institute',
    ]);

    $data = AcademicBackgroundData::fromRequest($request);

    expect($data->degree)->toBe('PhD')
        ->and($data->fieldOfStudy)->toBe('Artificial Intelligence')
        ->and($data->startDate)->toBe('2025-01-01')
        ->and($data->endDate)->toBe('2028-12-01')
        ->and($data->institution)->toBe('Research Institute');
});

test('it can be created from request with null end date', function () {
    $request = AcademicBackgroundRequest::create('/profile/academic-background', 'POST', [
        'degree' => 'Certificate',
        'field_of_study' => 'Web Development',
        'start_date' => '2024-01-01',
        'end_date' => null,
        'institution' => 'Online Academy',
    ]);

    $data = AcademicBackgroundData::fromRequest($request);

    expect($data->degree)->toBe('Certificate')
        ->and($data->fieldOfStudy)->toBe('Web Development')
        ->and($data->startDate)->toBe('2024-01-01')
        ->and($data->endDate)->toBeNull()
        ->and($data->institution)->toBe('Online Academy');
});

test('it converts to array correctly', function () {
    $data = new AcademicBackgroundData(
        degree: 'Bachelor',
        fieldOfStudy: 'Engineering',
        startDate: '2020-01-01',
        endDate: '2024-01-01',
        institution: 'Tech University'
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'degree' => 'Bachelor',
        'field_of_study' => 'Engineering',
        'start_date' => '2020-01-01',
        'end_date' => '2024-01-01',
        'institution' => 'Tech University',
    ]);
});
