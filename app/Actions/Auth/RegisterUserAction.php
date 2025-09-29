<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

final readonly class RegisterUserAction
{
    /**
     * Register a new user with the given data.
     *
     * @param  array<string, mixed>  $userData
     */
    public function handle(array $userData): User
    {
        $user = User::query()->create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        event(new Registered($user));

        return $user;
    }
}
