<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use RuntimeException;

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
     * @return array<string, array<int, string>>
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
     * Get the validated data from the request.
     *
     * @param  array<array-key, mixed>|int|string|null  $key
     * @return array<string, bool>
     */
    public function validated(mixed $key = null, mixed $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (! is_array($validated)) {
            throw new RuntimeException('Expected array from validated data');
        }

        return [
            'follow_notifications' => (bool) ($validated['follow_notifications'] ?? false),
            'email_notifications' => (bool) ($validated['email_notifications'] ?? false),
            'browser_notifications' => (bool) ($validated['browser_notifications'] ?? false),
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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['follow_notifications', 'email_notifications', 'browser_notifications'] as $field) {
            if ($this->has($field)) {
                $input = $this->input($field);

                // Only convert values that are actually boolean-like (true/false/1/0/"1"/"0")
                // Leave invalid values as-is so validation can catch them
                if (is_bool($input) || $input === 1 || $input === 0 || $input === '1' || $input === '0' || $input === 'true' || $input === 'false') {
                    $data[$field] = filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
            }
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
