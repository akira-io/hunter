<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use App\Http\Requests\Auth\NewPasswordRequest;

final readonly class PasswordResetData
{
    /**
     * Create password reset data transfer object.
     */
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
        public string $passwordConfirmation,
    ) {}

    /**
     * Create from array data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token: (string) $data['token'],
            email: (string) $data['email'],
            password: (string) $data['password'],
            passwordConfirmation: (string) $data['password_confirmation'],
        );
    }

    /**
     * Create from new password request.
     */
    public static function fromRequest(NewPasswordRequest $request): self
    {
        return new self(
            token: (string) $request->input('token'),
            email: (string) $request->input('email'),
            password: (string) $request->input('password'),
            passwordConfirmation: (string) $request->input('password_confirmation'),
        );
    }

    /**
     * Get data as array for Password::reset.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->passwordConfirmation,
        ];
    }
}
