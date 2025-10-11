# Active Sessions Management

## Overview

Active Sessions Management allows users to view and control all active login sessions across different devices. This feature enhances security by providing visibility and control over where accounts are accessed.

**New in v0.6.0** (October 16, 2025)

## Architecture

```
┌─────────────────────────────────────────────────┐
│          Active Sessions Management              │
│       (Settings → Security → Active Sessions)    │
└─────────────────────────────────────────────────┘
        │
        ├── Session Detection
        │   ├── IP Address Tracking
        │   ├── Device Information
        │   ├── Browser Detection
        │   └── Geolocation
        │
        ├── Session Filtering
        │   ├── Unique by IP Address
        │   ├── Most Recent Per IP
        │   └── Current Session Marking
        │
        └── Session Management
            ├── View All Sessions
            ├── Revoke Individual Session
            ├── Logout All Devices
            └── Auto-expiration (5 min)
```

## Database Schema

```sql
-- authentication_logs table (from rappasoft/laravel-authentication-log)
CREATE TABLE authentication_logs (
    id BIGSERIAL PRIMARY KEY,
    authenticatable_type VARCHAR(255) NOT NULL,
    authenticatable_id BIGINT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_at TIMESTAMP,
    login_successful BOOLEAN DEFAULT true,
    logout_at TIMESTAMP,
    cleared_by_user BOOLEAN DEFAULT false,
    location JSONB,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_auth_logs_user ON authentication_logs(authenticatable_id, authenticatable_type);
CREATE INDEX idx_auth_logs_ip ON authentication_logs(ip_address);
CREATE INDEX idx_auth_logs_login_at ON authentication_logs(login_at);
```

## Backend Implementation

### Action Class

```php
// app/Actions/Settings/ActiveSessionsAction.php
<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Support\Collection;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;

final readonly class ActiveSessionsAction
{
    /**
     * Get active sessions for user, filtered by IP
     */
    public function handle(User $user): Collection
    {
        // Get all active sessions (not logged out)
        $sessions = AuthenticationLog::query()
            ->where('authenticatable_id', $user->id)
            ->where('authenticatable_type', User::class)
            ->whereNull('logout_at')
            ->orderByDesc('login_at')
            ->get();

        // Group by IP address and keep only most recent
        $uniqueSessions = $sessions
            ->groupBy('ip_address')
            ->map(fn ($group) => $group->first())
            ->values();

        // Map to response format
        return $uniqueSessions->map(function ($session) {
            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'login_at' => $session->login_at,
                'location' => $session->location,
                'device_type' => $this->getDeviceType($session->user_agent),
                'browser' => $this->getBrowser($session->user_agent),
                'platform' => $this->getPlatform($session->user_agent),
                'is_current' => $session->ip_address === request()->ip(),
            ];
        });
    }

    private function getDeviceType(string $userAgent): string
    {
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            return 'mobile';
        }
        return 'desktop';
    }

    private function getBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        return 'Unknown';
    }

    private function getPlatform(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'macOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iOS')) return 'iOS';
        return 'Unknown';
    }
}
```

### Controller

```php
// app/Http/Controllers/Settings/SecurityController.php

use App\Actions\Settings\ActiveSessionsAction;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;

class SecurityController extends Controller
{
    /**
     * Show security settings with active sessions
     */
    public function index(ActiveSessionsAction $activeSessionsAction)
    {
        return inertia('Settings/Security', [
            'activeSessions' => $activeSessionsAction->handle(auth()->user()),
            'has2FA' => auth()->user()->two_factor_enabled,
            'connectedAccounts' => [
                'github' => !empty(auth()->user()->github_id),
                'google' => !empty(auth()->user()->google_id),
            ],
        ]);
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(int $id)
    {
        $session = AuthenticationLog::query()
            ->where('id', $id)
            ->where('authenticatable_id', auth()->id())
            ->firstOrFail();

        // Don't allow revoking current session via this method
        if ($session->ip_address === request()->ip()) {
            return back()->withErrors(['error' => 'Cannot revoke current session']);
        }

        $session->update(['logout_at' => now()]);

        return back()->with('success', 'Session revoked successfully');
    }

    /**
     * Logout all other devices
     */
    public function revokeAllSessions()
    {
        $currentIp = request()->ip();

        $revoked = AuthenticationLog::query()
            ->where('authenticatable_id', auth()->id())
            ->where('ip_address', '!=', $currentIp)
            ->whereNull('logout_at')
            ->update(['logout_at' => now()]);

        return back()->with('success', "Logged out from {$revoked} other device(s)");
    }
}
```

### Routes

```php
// routes/web.php

use App\Http\Controllers\Settings\SecurityController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Security settings
    Route::get('/settings/security', [SecurityController::class, 'index'])
        ->name('settings.security');
    
    // Session management
    Route::post('/settings/security/sessions/{id}/revoke', [SecurityController::class, 'revokeSession'])
        ->name('settings.security.sessions.revoke');
    
    Route::post('/settings/security/sessions/revoke-all', [SecurityController::class, 'revokeAllSessions'])
        ->name('settings.security.sessions.revoke-all');
});
```

## Frontend Implementation

### TypeScript Types

```typescript
// resources/js/types/session.ts

export interface Session {
    id: number;
    ip_address: string;
    user_agent: string;
    login_at: string;
    location: {
        city?: string;
        country?: string;
        country_code?: string;
    } | null;
    device_type: 'mobile' | 'desktop';
    browser: string;
    platform: string;
    is_current: boolean;
}
```

### Custom Hook

```typescript
// resources/js/hooks/use-unique-sessions.ts

import { useMemo } from 'react';
import type { Session } from '@/types/session';

/**
 * Hook to filter and return unique sessions by IP address
 * Keeps only the most recent session per IP
 *
 * @param sessions - Array of active sessions
 * @returns Filtered array with unique sessions per IP
 */
export function useUniqueSessions(sessions: Session[]): Session[] {
    return useMemo(() => {
        const sessionMap = new Map<string, Session>();

        sessions.forEach((session) => {
            const existing = sessionMap.get(session.ip_address);

            if (!existing) {
                sessionMap.set(session.ip_address, session);
            } else {
                // Keep the most recent session
                const existingTime = new Date(existing.login_at).getTime();
                const currentTime = new Date(session.login_at).getTime();

                if (currentTime > existingTime) {
                    sessionMap.set(session.ip_address, session);
                }
            }
        });

        return Array.from(sessionMap.values());
    }, [sessions]);
}
```

### Session Card Component

```typescript
// resources/js/components/security/session-card.tsx

import { router } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { Monitor, Smartphone, MapPin, Clock, X } from 'lucide-react';
import type { Session } from '@/types/session';

interface SessionCardProps {
    session: Session;
    onRevoke?: (id: number) => void;
    isRevoking?: boolean;
}

export function SessionCard({ session, onRevoke, isRevoking }: SessionCardProps) {
    const DeviceIcon = session.device_type === 'mobile' ? Smartphone : Monitor;

    const handleRevoke = () => {
        if (session.is_current) {
            alert('Cannot revoke current session');
            return;
        }

        if (confirm('Are you sure you want to revoke this session?')) {
            router.post(`/settings/security/sessions/${session.id}/revoke`, {}, {
                preserveScroll: true,
                onSuccess: () => {
                    onRevoke?.(session.id);
                },
            });
        }
    };

    return (
        <div className="flex items-start gap-4 rounded-lg border p-4">
            {/* Device Icon */}
            <div className="rounded-full bg-primary/10 p-3">
                <DeviceIcon className="h-5 w-5 text-primary" />
            </div>

            {/* Session Details */}
            <div className="flex-1 space-y-1">
                <div className="flex items-center gap-2">
                    <h4 className="font-medium">
                        {session.browser} on {session.platform}
                    </h4>
                    {session.is_current && (
                        <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                            Current Session
                        </span>
                    )}
                </div>

                <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                    {/* IP Address & Location */}
                    <div className="flex items-center gap-1">
                        <MapPin className="h-3.5 w-3.5" />
                        <span>{session.ip_address}</span>
                        {session.location && (
                            <span>
                                • {session.location.city}, {session.location.country}
                            </span>
                        )}
                    </div>

                    {/* Last Active */}
                    <div className="flex items-center gap-1">
                        <Clock className="h-3.5 w-3.5" />
                        <span>
                            Active {formatDistanceToNow(new Date(session.login_at), { addSuffix: true })}
                        </span>
                    </div>
                </div>
            </div>

            {/* Revoke Button */}
            {!session.is_current && (
                <button
                    onClick={handleRevoke}
                    disabled={isRevoking}
                    className="rounded-full p-2 text-muted-foreground hover:bg-destructive/10 hover:text-destructive disabled:opacity-50"
                    title="Revoke session"
                >
                    <X className="h-4 w-4" />
                </button>
            )}
        </div>
    );
}
```

### Active Sessions Component

```typescript
// resources/js/components/security/active-sessions.tsx

import { useState } from 'react';
import { router } from '@inertiajs/react';
import { useUniqueSessions } from '@/hooks/use-unique-sessions';
import { SessionCard } from './session-card';
import { Button } from '@/components/ui/button';
import type { Session } from '@/types/session';

interface ActiveSessionsProps {
    sessions: Session[];
}

export function ActiveSessions({ sessions }: ActiveSessionsProps) {
    const [revoking, setRevoking] = useState<number | null>(null);
    const uniqueSessions = useUniqueSessions(sessions);

    const handleLogoutAll = () => {
        if (!confirm('Are you sure you want to logout from all other devices?')) {
            return;
        }

        router.post('/settings/security/sessions/revoke-all', {}, {
            preserveScroll: true,
        });
    };

    return (
        <section className="space-y-4">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h3 className="text-lg font-semibold">Active Sessions</h3>
                    <p className="text-sm text-muted-foreground">
                        Manage your login sessions across devices
                    </p>
                </div>
                <span className="text-sm text-muted-foreground">
                    {uniqueSessions.length} {uniqueSessions.length === 1 ? 'session' : 'sessions'}
                </span>
            </div>

            {/* Session List */}
            <div className="space-y-3">
                {uniqueSessions.map((session) => (
                    <SessionCard
                        key={session.id}
                        session={session}
                        onRevoke={setRevoking}
                        isRevoking={revoking === session.id}
                    />
                ))}
            </div>

            {/* Logout All Button */}
            {uniqueSessions.length > 1 && (
                <Button
                    variant="outline"
                    onClick={handleLogoutAll}
                    className="w-full"
                >
                    Logout All Other Devices
                </Button>
            )}
        </section>
    );
}
```

### Settings Page

```typescript
// resources/js/pages/settings/security.tsx

import { Head } from '@inertiajs/react';
import { SettingsLayout } from '@/layouts/settings-layout';
import { ActiveSessions } from '@/components/security/active-sessions';
import type { Session } from '@/types/session';

interface Props {
    activeSessions: Session[];
    has2FA: boolean;
    connectedAccounts: {
        github: boolean;
        google: boolean;
    };
}

export default function Security({ activeSessions, has2FA, connectedAccounts }: Props) {
    return (
        <>
            <Head title="Security Settings" />

            <SettingsLayout>
                <div className="space-y-8">
                    {/* Page Header */}
                    <div>
                        <h2 className="text-2xl font-bold">Security Settings</h2>
                        <p className="text-muted-foreground">
                            Manage your account security and active sessions
                        </p>
                    </div>

                    {/* Active Sessions */}
                    <ActiveSessions sessions={activeSessions} />

                    {/* Other security features... */}
                </div>
            </SettingsLayout>
        </>
    );
}
```

## Testing

### Unit Tests

```php
// tests/Unit/Actions/Settings/ActiveSessionsActionTest.php

use App\Actions\Settings\ActiveSessionsAction;
use App\Models\User;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;

test('returns only most recent session per ip', function () {
    $user = User::factory()->create();
    $action = new ActiveSessionsAction();

    // Create 3 sessions with same IP
    AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'login_at' => now()->subHours(3),
    ]);

    AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'login_at' => now()->subHour(),
    ]);

    $recent = AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'login_at' => now(),
    ]);

    $sessions = $action->handle($user);

    expect($sessions)->toHaveCount(1)
        ->and($sessions->first()['id'])->toBe($recent->id);
});

test('returns multiple sessions with different ips', function () {
    $user = User::factory()->create();
    $action = new ActiveSessionsAction();

    AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.1',
    ]);

    AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.2',
    ]);

    $sessions = $action->handle($user);

    expect($sessions)->toHaveCount(2);
});

test('excludes logged out sessions', function () {
    $user = User::factory()->create();
    $action = new ActiveSessionsAction();

    AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'logout_at' => now(),
    ]);

    $sessions = $action->handle($user);

    expect($sessions)->toBeEmpty();
});

test('marks current session correctly', function () {
    $user = User::factory()->create();
    $action = new ActiveSessionsAction();

    AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => request()->ip(),
    ]);

    $sessions = $action->handle($user);

    expect($sessions->first()['is_current'])->toBeTrue();
});
```

### Feature Tests

```php
// tests/Feature/Settings/SecurityControllerTest.php

test('can view security settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.security'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Security')
            ->has('activeSessions')
        );
});

test('can revoke a session', function () {
    $user = User::factory()->create();
    
    $session = AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.100',
    ]);

    $this->actingAs($user)
        ->post(route('settings.security.sessions.revoke', $session->id))
        ->assertSessionHas('success');

    expect($session->fresh()->logout_at)->not->toBeNull();
});

test('cannot revoke current session', function () {
    $user = User::factory()->create();
    
    $session = AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => request()->ip(),
    ]);

    $this->actingAs($user)
        ->post(route('settings.security.sessions.revoke', $session->id))
        ->assertSessionHasErrors('error');
});

test('can logout all other devices', function () {
    $user = User::factory()->create();

    // Current session
    AuthenticationLog::factory()->create([
        'authenticatable_id' => $user->id,
        'ip_address' => request()->ip(),
    ]);

    // Other sessions
    AuthenticationLog::factory()->count(3)->create([
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.100',
    ]);

    $this->actingAs($user)
        ->post(route('settings.security.sessions.revoke-all'))
        ->assertSessionHas('success');

    // Other sessions should be logged out
    expect(
        AuthenticationLog::where('authenticatable_id', $user->id)
            ->where('ip_address', '!=', request()->ip())
            ->whereNull('logout_at')
            ->count()
    )->toBe(0);
});
```

## Security Considerations

### Session Expiration

Sessions automatically expire after 5 minutes of inactivity:

```php
// config/session.php
'lifetime' => 5,
```

### IP Validation

Sessions are tied to IP addresses for security:

```php
// Middleware to validate session IP
class ValidateSessionIp
{
    public function handle($request, $next)
    {
        $session = AuthenticationLog::where('authenticatable_id', auth()->id())
            ->whereNull('logout_at')
            ->where('ip_address', $request->ip())
            ->exists();

        if (!$session) {
            auth()->logout();
            return redirect('/login')->with('error', 'Session expired');
        }

        return $next($request);
    }
}
```

### Rate Limiting

Protect session management endpoints:

```php
// routes/web.php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/settings/security/sessions/{id}/revoke', ...);
    Route::post('/settings/security/sessions/revoke-all', ...);
});
```

## Performance Optimization

### Database Indexes

```sql
-- Optimize session queries
CREATE INDEX idx_auth_logs_active ON authentication_logs(authenticatable_id, logout_at, login_at);
CREATE INDEX idx_auth_logs_ip_active ON authentication_logs(ip_address, logout_at);
```

### Caching

```php
// Cache active sessions for 5 minutes
public function handle(User $user): Collection
{
    return Cache::remember(
        "user_sessions_{$user->id}",
        now()->addMinutes(5),
        fn () => $this->fetchSessions($user)
    );
}
```

### Query Optimization

```php
// Eager load location data
$sessions = AuthenticationLog::with('location')
    ->where('authenticatable_id', $user->id)
    ->whereNull('logout_at')
    ->latest('login_at')
    ->get();
```

## Usage Examples

### Display Active Sessions

```typescript
import { ActiveSessions } from '@/components/security/active-sessions';

export default function SecurityPage({ activeSessions }) {
    return (
        <div>
            <ActiveSessions sessions={activeSessions} />
        </div>
    );
}
```

### Monitor Session Count

```typescript
const uniqueSessions = useUniqueSessions(sessions);
const sessionCount = uniqueSessions.length;

return (
    <Badge variant="secondary">
        {sessionCount} Active {sessionCount === 1 ? 'Session' : 'Sessions'}
    </Badge>
);
```

### Conditional Rendering

```typescript
const hasMultipleSessions = uniqueSessions.length > 1;

return (
    <>
        <ActiveSessions sessions={uniqueSessions} />
        
        {hasMultipleSessions && (
            <Alert>
                <AlertDescription>
                    You have active sessions on multiple devices.
                </AlertDescription>
            </Alert>
        )}
    </>
);
```

## Troubleshooting

### Sessions Not Showing

**Cause**: Cache not cleared
**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
```

### Duplicate Sessions

**Cause**: Not using `useUniqueSessions` hook
**Solution**:
```typescript
// Always use the hook
const uniqueSessions = useUniqueSessions(sessions);
```

### Location Not Displaying

**Cause**: IP geolocation service unavailable
**Solution**: Location is optional, fallback to IP display

## Related Documentation

- [14. Privacy & Security](./14-privacy-security.md)
- [02. Authentication](./02-authentication.md)
- [10. Testing](./10-testing.md)

---

**Version**: 0.6.0  
**Last Updated**: October 16, 2025
