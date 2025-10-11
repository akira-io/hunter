# Authentication

## Overview

Hunter supports multiple authentication methods:
- Email/Password authentication
- OAuth via GitHub
- OAuth via Google
- Email verification
- Password reset

## Registration

### Standard Registration

Users can register with email and password:

```php
use App\Actions\Auth\RegisterUserAction;

$user = app(RegisterUserAction::class)->handle([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secure-password',
    'password_confirmation' => 'secure-password',
]);
```

**Route:** `POST /register`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

**Response:**
Redirects to dashboard or onboarding flow.

### Email Verification

After registration, users must verify their email:

```php
// Send verification email
$user->sendEmailVerificationNotification();

// Check if user is verified
if ($user->hasVerifiedEmail()) {
    // User is verified
}
```

**Verification Route:** `GET /email/verify/{id}/{hash}`

## Login

### Standard Login

```php
use App\Actions\Auth\LoginUserAction;

$user = app(LoginUserAction::class)->handle([
    'email' => 'john@example.com',
    'password' => 'secure-password',
    'remember' => true,
]);
```

**Route:** `POST /login`

**Request:**
```json
{
  "email": "john@example.com",
  "password": "SecurePass123!",
  "remember": true
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "redirect": "/dashboard"
}
```

## OAuth Authentication

### GitHub Authentication

**Flow:**

1. User clicks "Login with GitHub"
2. Redirects to GitHub OAuth
3. GitHub redirects back with authorization code
4. Application exchanges code for access token
5. User info is retrieved and user is logged in/registered

**Routes:**
- Redirect: `GET /auth/github`
- Callback: `GET /auth/github/callback`

**Implementation:**

```php
use App\Actions\Auth\HandleGithubAuthAction;

// In GithubAuthController
public function callback()
{
    $githubUser = Socialite::driver('github')->user();
    
    $user = app(HandleGithubAuthAction::class)->handle($githubUser);
    
    auth()->login($user);
    
    return redirect()->intended('/');
}
```

**User Data Retrieved:**
- Name
- Email
- GitHub ID
- Avatar URL
- GitHub username
- GitHub access token

### Google Authentication

Similar to GitHub, but using Google OAuth:

**Routes:**
- Redirect: `GET /auth/google`
- Callback: `GET /auth/google/callback`

**Implementation:**

```php
use App\Actions\Auth\HandleGoogleAuthAction;

public function callback()
{
    $googleUser = Socialite::driver('google')->user();
    
    $user = app(HandleGoogleAuthAction::class)->handle($googleUser);
    
    auth()->login($user);
    
    return redirect()->intended('/');
}
```

## Password Reset

### Request Password Reset

**Route:** `POST /forgot-password`

**Request:**
```json
{
  "email": "john@example.com"
}
```

**Action:**

```php
use App\Actions\Auth\SendPasswordResetAction;

app(SendPasswordResetAction::class)->handle($email);
```

This sends a password reset email with a unique token.

### Reset Password

**Route:** `POST /reset-password`

**Request:**
```json
{
  "token": "reset-token-here",
  "email": "john@example.com",
  "password": "NewSecurePass123!",
  "password_confirmation": "NewSecurePass123!"
}
```

**Action:**

```php
use App\Actions\Auth\ResetPasswordAction;

app(ResetPasswordAction::class)->handle([
    'token' => $token,
    'email' => $email,
    'password' => $password,
]);
```

## Logout

**Route:** `POST /logout`

```php
auth()->logout();
request()->session()->invalidate();
request()->session()->regenerateToken();
```

## Authentication Guards

Hunter uses two authentication guards:

### Web Guard (Default)
For web-based authentication with sessions:

```php
auth()->guard('web')->check();
```

### Sanctum Guard (API)
For API authentication with tokens:

```php
auth()->guard('sanctum')->check();
```

**API Token Generation:**

```php
$token = $user->createToken('token-name')->plainTextToken;
```

**API Request:**
```bash
curl -H "Authorization: Bearer {token}" \
  https://devhunter.test/api/user
```

## Middleware

### Authentication Middleware

Protect routes that require authentication:

```php
Route::middleware('auth')->group(function () {
    // Protected routes
});
```

### Email Verification Middleware

Require verified email:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Verified users only
});
```

### Guest Middleware

For routes only accessible to guests:

```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show']);
});
```

## Session Management

### Auth Logs

Hunter tracks authentication attempts using `akira/laravel-auth-logs`:

```php
// Get user's authentication logs
$logs = $user->authLogs()->latest()->get();

// Check recent failed attempts
$failedAttempts = $user->authLogs()
    ->where('successful', false)
    ->where('created_at', '>', now()->subHour())
    ->count();
```

### Password Confirmation

For sensitive operations, require password confirmation:

```php
Route::middleware(['auth', 'password.confirm'])->group(function () {
    Route::delete('/settings/profile', [ProfileController::class, 'destroy']);
});
```

## Frontend Authentication

### React/Inertia Authentication

Check authentication status in React components:

```tsx
import { usePage } from '@inertiajs/react';

function MyComponent() {
  const { auth } = usePage().props;
  
  if (auth.user) {
    return <div>Welcome, {auth.user.name}!</div>;
  }
  
  return <div>Please log in</div>;
}
```

### Redirecting Authenticated Users

```tsx
import { router } from '@inertiajs/react';

// Redirect to login
router.visit('/login');

// Redirect after login
router.visit('/dashboard', {
  onSuccess: () => {
    console.log('Logged in successfully');
  }
});
```

## Security Best Practices

1. **Always hash passwords** - Laravel handles this automatically
2. **Use HTTPS in production**
3. **Implement rate limiting** on login routes
4. **Validate all user inputs**
5. **Use CSRF protection** - enabled by default
6. **Rotate tokens regularly**
7. **Log authentication events** for auditing

## Testing Authentication

### Feature Tests

```php
use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    public function test_users_can_login(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_users_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
```

## API Reference

| Endpoint | Method | Description | Auth Required |
|----------|--------|-------------|---------------|
| `/register` | POST | Register new user | No |
| `/login` | POST | Login user | No |
| `/logout` | POST | Logout user | Yes |
| `/forgot-password` | POST | Request password reset | No |
| `/reset-password` | POST | Reset password | No |
| `/email/verify/{id}/{hash}` | GET | Verify email | Yes |
| `/auth/github` | GET | GitHub OAuth redirect | No |
| `/auth/github/callback` | GET | GitHub OAuth callback | No |
| `/auth/google` | GET | Google OAuth redirect | No |
| `/auth/google/callback` | GET | Google OAuth callback | No |
| `/api/user` | GET | Get authenticated user | Yes (Sanctum) |
