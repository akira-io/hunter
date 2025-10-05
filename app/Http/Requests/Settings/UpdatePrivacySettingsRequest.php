<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePrivacySettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'profile_visibility' => ['required', 'string', Rule::in(['public', 'followers', 'private'])],
            'who_can_message' => ['required', 'string', Rule::in(['everyone', 'followers', 'none'])],
            'who_can_comment' => ['required', 'string', Rule::in(['everyone', 'followers', 'disabled'])],
            'searchable' => ['required', 'boolean'],
            'show_activity_status' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'profile_visibility' => 'visibilidade do perfil',
            'who_can_message' => 'quem pode enviar mensagens',
            'who_can_comment' => 'quem pode comentar',
            'searchable' => 'pesquisável',
            'show_activity_status' => 'mostrar status de atividade',
        ];
    }
}
