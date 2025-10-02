<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class NotificationUpdateRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'follow_notifications' => ['required', 'boolean'],
            'email_notifications' => ['required', 'boolean'],
            'browser_notifications' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'follow_notifications.required' => 'A preferência de notificações de seguidores é obrigatória.',
            'follow_notifications.boolean' => 'A preferência de notificações de seguidores deve ser verdadeiro ou falso.',
            'email_notifications.required' => 'A preferência de notificações por email é obrigatória.',
            'email_notifications.boolean' => 'A preferência de notificações por email deve ser verdadeiro ou falso.',
            'browser_notifications.required' => 'A preferência de notificações do navegador é obrigatória.',
            'browser_notifications.boolean' => 'A preferência de notificações do navegador deve ser verdadeiro ou falso.',
        ];
    }
}
