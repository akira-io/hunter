<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

final readonly class LoginCredentials
{
    /**
     * Create login credentials data transfer object.
     */
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
        public ?string $ip = null,
    ) {}

    /**
     * Create from array data.
     *
     * @param  array<string, mixed>  $credentials
     */
    public static function from(array $credentials, bool $remember = false, ?string $ip = null): self
    {
        return new self(
            email: (string) $credentials['email'],
            password: (string) $credentials['password'],
            remember: $remember,
            ip: $ip,
        );
    }

    /**
     * Get credentials as array for Auth::attempt.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
