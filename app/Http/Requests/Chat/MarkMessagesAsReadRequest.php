<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

final class MarkMessagesAsReadRequest extends FormRequest
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
            'message_ids' => ['array'],
            'message_ids.*' => ['integer', 'exists:messages,id'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message_ids.array' => 'Os IDs das mensagens devem ser um array.',
            'message_ids.*.integer' => 'Cada ID de mensagem deve ser um número inteiro.',
            'message_ids.*.exists' => 'Uma ou mais mensagens não existem.',
        ];
    }

    /**
     * Get the validated message IDs.
     *
     * @return array<int>
     */
    public function getMessageIds(): array
    {
        /** @var array<int> $messageIds */
        $messageIds = $this->validated('message_ids', []);

        return $messageIds;
    }
}
