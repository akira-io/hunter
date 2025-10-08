<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Actions\User\Profile\UpdateProfileAvatarAction;
use App\Actions\User\Profile\UpdateProfileBackgroundAction;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

final class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = type($this->user())->as(User::class)->id;

        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],

            'bio' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:100'],

            'avatar_url' => $this->avatarRules(),
            'background_image_url' => $this->backgroundRules(),
        ];
    }

    /**
     * Update the user's profile images
     *
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function updateImages(UpdateProfileBackgroundAction $profileBackgroundAction, UpdateProfileAvatarAction $profileAvatarAction): void
    {

        $user = type($this->user())->as(User::class);

        if ($this->hasFile('avatar_url')) {
            $avatarUrl = type($this->file('avatar_url'))->as(UploadedFile::class);
            $profileAvatarAction->handle(user: $user, avatarUrl: $avatarUrl);
        }

        if ($this->hasFile('background_image_url')) {
            $backgroundUrl = type($this->file('background_image_url'))->as(UploadedFile::class);
            $profileBackgroundAction->handle(user: $user, backgroundUrl: $backgroundUrl);
        }
    }

    /**
     * Get the avatar rules
     *
     * @return string[]
     */
    private function avatarRules(): array
    {
        if ($this->hasFile('avatar_url')) {
            return ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png'];
        }

        if (is_string($this->input('avatar_url'))) {
            return ['nullable', 'url'];
        }

        return ['nullable'];
    }

    /**
     * Get background rules
     *
     * @return string[]
     */
    private function backgroundRules(): array
    {
        if ($this->hasFile('background_image_url')) {
            return ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png'];
        }

        if (is_string($this->input('background_image_url'))) {
            return ['nullable', 'url'];
        }

        return ['nullable'];
    }
}
