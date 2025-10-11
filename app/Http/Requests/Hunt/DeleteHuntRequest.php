<?php

declare(strict_types=1);

namespace App\Http\Requests\Hunt;

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteHuntRequest extends FormRequest
{
    /**
     * Rules for the request.
     *
     * @return array<string, array<string>>
     */
    public function rules(): array
    {

        return [
            //
        ];
    }

    /**
     * Authorize the request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = type($this->user())->as(User::class);

        /** @var mixed $hunt */
        $hunt = $this->route('hunt');

        return (bool) $user->can('delete', $hunt);
    }

    /**
     * Delete the hunt.
     */
    public function destroy(Hunt $hunt): ?bool
    {
        /** @var bool|null $deleted */
        $deleted = $hunt->delete();

        return $deleted;
    }
}
