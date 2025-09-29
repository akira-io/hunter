<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use App\Http\Requests\Auth\RegisterRequest;

final readonly class RegisterUserData
{
    /**
     * Create user registration data transfer object.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * Create from array data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );
    }

    /**
     * Create from register request.
     */
    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
        );
    }
}
