<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

final class SendMessageRequest extends FormRequest
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
            'conversation_id' => ['required', 'exists:conversations,id'],
            'content' => ['required', 'string', 'max:10000'],
            'type' => ['in:text,image,file'],
            'metadata' => ['array', 'nullable'],
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
            'conversation_id.required' => 'O ID da conversa é obrigatório.',
            'conversation_id.exists' => 'A conversa especificada não existe.',
            'content.required' => 'O conteúdo da mensagem é obrigatório.',
            'content.max' => 'A mensagem não pode ter mais de 10.000 caracteres.',
            'type.in' => 'O tipo de mensagem deve ser "text", "image" ou "file".',
            'metadata.array' => 'Os metadados devem ser um array.',
        ];
    }

    /**
     * Get the validated conversation ID.
     */
    public function getConversationId(): int
    {
        /** @var mixed $conversationId */
        $conversationId = $this->validated('conversation_id');
        if (! is_int($conversationId) && ! is_string($conversationId) && ! is_numeric($conversationId)) {
            throw new InvalidArgumentException('Conversation ID must be numeric');
        }

        return (int) $conversationId;
    }

    /**
     * Get the validated message content.
     */
    public function getMessageContent(): string
    {
        /** @var string $content */
        $content = $this->validated('content');

        return $content;
    }

    /**
     * Get the validated type.
     */
    public function getType(): string
    {
        /** @var string $type */
        $type = $this->validated('type', 'text');

        return $type;
    }

    /**
     * Get the validated metadata.
     *
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        /** @var array<string, mixed>|null $metadata */
        $metadata = $this->validated('metadata');

        return $metadata;
    }
}
