<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

final class CreateConversationRequest extends FormRequest
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
            'type' => 'required|in:direct,group',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
            'title' => 'nullable|string|max:255',
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
            'type.required' => 'O tipo de conversa é obrigatório.',
            'type.in' => 'O tipo de conversa deve ser "direct" ou "group".',
            'participants.required' => 'Os participantes são obrigatórios.',
            'participants.min' => 'É necessário pelo menos um participante.',
            'participants.*.exists' => 'Um ou mais participantes não existem.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
        ];
    }

    /**
     * Get the validated type.
     */
    public function getType(): string
    {
        return $this->validated('type');
    }

    /**
     * Get the validated participants.
     *
     * @return array<int>
     */
    public function getParticipants(): array
    {
        return $this->validated('participants', []);
    }

    /**
     * Get the validated title.
     */
    public function getTitle(): ?string
    {
        return $this->validated('title');
    }
}
