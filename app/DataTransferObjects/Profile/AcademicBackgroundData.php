<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use App\Http\Requests\Profile\AcademicBackgroundRequest;

final readonly class AcademicBackgroundData
{
    /**
     * Create academic background data transfer object.
     */
    public function __construct(
        public string $degree,
        public string $fieldOfStudy,
        public string $startDate,
        public ?string $endDate,
        public string $institution,
    ) {}

    /**
     * Create from array data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            degree: $data['degree'],
            fieldOfStudy: $data['field_of_study'],
            startDate: $data['start_date'],
            endDate: $data['end_date'] ?? null,
            institution: $data['institution'],
        );
    }

    /**
     * Create from Academic request.
     */
    public static function fromRequest(AcademicBackgroundRequest $request): self
    {
        return new self(
            degree: $request->input('degree'),
            fieldOfStudy: $request->input('field_of_study'),
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            institution: $request->input('institution')
        );
    }

    /**
     * Get data as array for model creation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'degree' => $this->degree,
            'field_of_study' => $this->fieldOfStudy,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'institution' => $this->institution,
        ];
    }
}
