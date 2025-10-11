<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

final readonly class PasswordController
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): RedirectResponse
    {
        return redirect()->route('security.index');
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $user->update([
            'password' => Hash::make(type($request->validated('password'))->asString()),
        ]);

        return back();
    }
}
