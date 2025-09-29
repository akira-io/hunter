<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Hunt;

use App\Http\Requests\Hunt\CreateHuntRequest;
use Illuminate\Http\UploadedFile;

final readonly class CreateHuntData
{
    /**
     * Create hunt data transfer object.
     */
    public function __construct(
        public string $content,
        public bool $isReported = false,
        public bool $isPinned = false,
        public bool $isIgnored = false,
        public ?UploadedFile $image = null,
    ) {}

    /**
     * Create from array data and optional image.
     *
     * @param  array<string, mixed>  $data
     */
    public static function from(array $data, ?UploadedFile $image = null): self
    {
        return new self(
            content: (string) $data['content'],
            isReported: (bool) ($data['is_reported'] ?? false),
            isPinned: (bool) ($data['is_pinned'] ?? false),
            isIgnored: (bool) ($data['is_ignored'] ?? false),
            image: $image,
        );
    }

    /**
     * Create from create hunt request.
     */
    public static function fromRequest(CreateHuntRequest $request): self
    {
        $image = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file instanceof UploadedFile) {
                $image = $file;
            }
        }

        return new self(
            content: (string) $request->input('content'),
            isReported: $request->boolean('is_reported'),
            isPinned: $request->boolean('is_pinned'),
            isIgnored: $request->boolean('is_ignored'),
            image: $image,
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
            'content' => $this->content,
            'is_reported' => $this->isReported,
            'is_pinned' => $this->isPinned,
            'is_ignored' => $this->isIgnored,
        ];
    }
}
