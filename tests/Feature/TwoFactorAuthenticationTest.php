<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure 2FA is enabled in config
    config()->set('fortify.features', [
        Features::registration(),
        Features::resetPasswords(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false, // Disable password confirmation for testing
        ]),
    ]);

    // Helper to confirm password in session (bypasses RequirePassword middleware)
    $this->confirmPassword = function () {
        $this->session(['auth.password_confirmed_at' => time()]);

        return $this;
    };
});

it('user can enable two factor authentication', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    ($this->confirmPassword)();

    actingAs($user)
        ->post('/user/two-factor-authentication')
        ->assertRedirect();

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull();
    expect($user->two_factor_recovery_codes)->not->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

it('user can confirm two factor authentication with valid code', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    ($this->confirmPassword)();

    // Enable 2FA
    actingAs($user)->post('/user/two-factor-authentication');

    $user->refresh();

    // Get a valid TOTP code
    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);
    $secret = decrypt($user->two_factor_secret);
    $validCode = $google2fa->getCurrentOtp($secret);

    // Confirm 2FA with valid code
    actingAs($user)
        ->post('/user/confirmed-two-factor-authentication', [
            'code' => $validCode,
        ])
        ->assertRedirect();

    $user->refresh();

    expect($user->two_factor_confirmed_at)->not->toBeNull();
});

it('user cannot confirm two factor authentication with invalid code', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    ($this->confirmPassword)();

    // Enable 2FA
    actingAs($user)->post('/user/two-factor-authentication');

    // Try to confirm with invalid code
    actingAs($user)
        ->post('/user/confirmed-two-factor-authentication', [
            'code' => '000000',
        ])
        ->assertSessionHasErrors();

    $user->refresh();

    expect($user->two_factor_confirmed_at)->toBeNull();
});

it('user can disable two factor authentication', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    ($this->confirmPassword)();

    // Enable and confirm 2FA
    actingAs($user)->post('/user/two-factor-authentication');
    $user->refresh();

    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);
    $secret = decrypt($user->two_factor_secret);
    $validCode = $google2fa->getCurrentOtp($secret);

    actingAs($user)->post('/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    $user->refresh();
    expect($user->two_factor_confirmed_at)->not->toBeNull();

    // Disable 2FA
    actingAs($user)
        ->delete('/user/two-factor-authentication')
        ->assertRedirect();

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_recovery_codes)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

it('user can regenerate recovery codes', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    ($this->confirmPassword)();

    // Enable and confirm 2FA
    actingAs($user)->post('/user/two-factor-authentication');
    $user->refresh();

    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);
    $secret = decrypt($user->two_factor_secret);
    $validCode = $google2fa->getCurrentOtp($secret);

    actingAs($user)->post('/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    $user->refresh();
    $oldRecoveryCodes = $user->two_factor_recovery_codes;

    // Regenerate recovery codes
    actingAs($user)
        ->post('/user/two-factor-recovery-codes')
        ->assertRedirect();

    $user->refresh();

    expect($user->two_factor_recovery_codes)->not->toEqual($oldRecoveryCodes);
    expect($user->two_factor_recovery_codes)->not->toBeNull();
});

it('user with two factor enabled must provide code during login', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    ($this->confirmPassword)();

    // Enable and confirm 2FA
    actingAs($user)->post('/user/two-factor-authentication');
    $user->refresh();

    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);
    $secret = decrypt($user->two_factor_secret);
    $validCode = $google2fa->getCurrentOtp($secret);

    actingAs($user)->post('/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    // Logout
    post('/logout');

    // Try to login - should be redirected to 2FA challenge
    post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect('/two-factor-challenge');

    assertGuest();
});

it('user can login with two factor code', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    ($this->confirmPassword)();

    // Enable and confirm 2FA
    actingAs($user)->post('/user/two-factor-authentication');
    $user->refresh();

    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);
    $secret = decrypt($user->two_factor_secret);
    $validCode = $google2fa->getCurrentOtp($secret);

    actingAs($user)->post('/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    // Logout
    post('/logout');

    // Login
    post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect('/two-factor-challenge');

    // Wait for a new TOTP window to ensure we get a different code
    // TOTP codes change every 30 seconds, so we need to wait for the next window
    $currentCode = $google2fa->getCurrentOtp($secret);
    $newCode = $currentCode;
    $maxAttempts = 35; // Max 35 seconds wait
    $attempts = 0;
    
    while ($newCode === $currentCode && $attempts < $maxAttempts) {
        sleep(1);
        $attempts++;
        $newCode = $google2fa->getCurrentOtp($secret);
    }

    // Provide 2FA code
    post('/two-factor-challenge', [
        'code' => $newCode,
    ])->assertRedirect(route('hunts.index', absolute: false));
});

it('user can login with recovery code', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    ($this->confirmPassword)();

    // Enable and confirm 2FA
    actingAs($user)->post('/user/two-factor-authentication');
    $user->refresh();

    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);
    $secret = decrypt($user->two_factor_secret);
    $validCode = $google2fa->getCurrentOtp($secret);

    actingAs($user)->post('/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    $user->refresh();
    $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
    $recoveryCode = $recoveryCodes[0];

    // Logout
    post('/logout');

    // Login
    post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect('/two-factor-challenge');

    // Provide recovery code
    post('/two-factor-challenge', [
        'recovery_code' => $recoveryCode,
    ])->assertRedirect(route('hunts.index', absolute: false));
});

it('security page shows two factor authentication status', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    actingAs($user)
        ->get('/settings/security')
        ->assertInertia(fn ($page) => $page
            ->component('settings/security')
            ->has('twoFactorEnabled')
            ->where('twoFactorEnabled', false)
        );

    ($this->confirmPassword)();

    // Enable 2FA
    actingAs($user)->post('/user/two-factor-authentication');

    actingAs($user)
        ->get('/settings/security')
        ->assertInertia(fn ($page) => $page
            ->component('settings/security')
            ->where('twoFactorEnabled', true)
            ->has('qrCodeSvg')
            ->where('confirmed', false)
        );
});
