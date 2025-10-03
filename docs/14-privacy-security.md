# 🔒 Privacy & Security Settings

## Overview

Hunter provides comprehensive privacy and security controls that give users full control over their visibility, interactions, and account security. These settings help create a safe and personalized experience.

## Architecture

```
┌─────────────────────────────────────────────────┐
│          Privacy & Security Settings             │
│       (Settings → Privacy / Security)            │
└─────────────────────────────────────────────────┘
        │
        ├── Privacy Controls
        │   ├── Profile Visibility
        │   ├── Messaging Privacy
        │   ├── Hunt Comments Privacy
        │   ├── Search/Explore Visibility
        │   ├── Online Status Visibility
        │   └── Blocked Users Management
        │
        └── Security Features
            ├── Password Management
            ├── Two-Factor Authentication
            ├── Active Sessions
            ├── Connected Accounts
            └── Account Deletion
```

## 1. Privacy Section

### Profile Visibility Settings

Control who can view your complete profile, Hunts, and projects.

#### Database Schema

```sql
-- Add to users table
ALTER TABLE users ADD COLUMN profile_visibility VARCHAR(20) DEFAULT 'public';
-- Options: 'public', 'followers', 'private'
```

#### Implementation

```php
// User Model
class User extends Model
{
    protected $casts = [
        'profile_visibility' => 'string',
    ];
    
    public function canBeViewedBy(User $viewer): bool
    {
        return match($this->profile_visibility) {
            'public' => true,
            'followers' => $this->isFollowedBy($viewer),
            'private' => $this->id === $viewer->id,
        };
    }
}

// ProfileController
public function show(User $user)
{
    if (!$user->canBeViewedBy(auth()->user())) {
        abort(403, 'This profile is private');
    }
    
    return inertia('Profile/Show', [
        'user' => $user->load('hunts', 'followers', 'following'),
    ]);
}
```

#### Frontend

```tsx
export function PrivacySettings() {
    const { user } = usePage().props;
    const [visibility, setVisibility] = useState(user.profile_visibility);
    
    const updateVisibility = (value: string) => {
        router.put('/settings/privacy', {
            profile_visibility: value,
        });
        setVisibility(value);
    };
    
    return (
        <section>
            <h3>Profile Visibility</h3>
            <select value={visibility} onChange={(e) => updateVisibility(e.target.value)}>
                <option value="public">Public - Anyone can view</option>
                <option value="followers">Followers Only - Only followers can view</option>
                <option value="private">Private - Only you can view</option>
            </select>
        </section>
    );
}
```

### Messaging Privacy Controls

Control who can send you direct messages.

#### Database Schema

```sql
ALTER TABLE users ADD COLUMN messaging_privacy VARCHAR(20) DEFAULT 'everyone';
-- Options: 'everyone', 'followers', 'none'
```

#### Implementation

```php
public function canMessageUser(User $sender, User $recipient): bool
{
    return match($recipient->messaging_privacy) {
        'everyone' => true,
        'followers' => $recipient->isFollowedBy($sender),
        'none' => false,
    };
}

// ConversationController
public function store(Request $request)
{
    $validated = $request->validate([
        'recipient_id' => 'required|exists:users,id',
        'message' => 'required|string|max:10000',
    ]);
    
    $recipient = User::findOrFail($validated['recipient_id']);
    
    if (!$this->canMessageUser(auth()->user(), $recipient)) {
        abort(403, 'This user does not accept messages from you');
    }
    
    // Create conversation and send message
    // ...
}
```

### Hunt Comments Privacy

Control who can comment on your Hunts.

#### Database Schema

```sql
ALTER TABLE users ADD COLUMN hunt_comment_privacy VARCHAR(20) DEFAULT 'everyone';
-- Options: 'everyone', 'followers', 'disabled'
```

#### Implementation

```php
public function canCommentOnHunt(User $commenter, Hunt $hunt): bool
{
    $author = $hunt->user;
    
    return match($author->hunt_comment_privacy) {
        'everyone' => true,
        'followers' => $author->isFollowedBy($commenter),
        'disabled' => false,
    };
}

// CommentController
public function store(Request $request, Hunt $hunt)
{
    if (!$this->canCommentOnHunt(auth()->user(), $hunt)) {
        abort(403, 'Comments are disabled on this Hunt');
    }
    
    // Create comment
    // ...
}
```

### Search & Explore Visibility

Control whether your profile appears in search results and Explore section.

#### Database Schema

```sql
ALTER TABLE users ADD COLUMN search_visibility BOOLEAN DEFAULT TRUE;
```

#### Implementation

```php
// SearchController
public function search(Request $request)
{
    $query = $request->input('query');
    
    $users = User::search($query)
        ->where('search_visibility', true)
        ->get();
    
    return response()->json(['users' => $users]);
}

// ExploreController
public function index()
{
    $users = User::where('search_visibility', true)
        ->inRandomOrder()
        ->limit(20)
        ->get();
    
    return inertia('Explore', ['users' => $users]);
}
```

### Online Status Visibility

Control who can see your online/last seen status.

#### Database Schema

```sql
ALTER TABLE users ADD COLUMN show_online_status BOOLEAN DEFAULT TRUE;
```

#### Implementation

```php
// Presence Middleware
public function handle(Request $request, Closure $next)
{
    if (auth()->check() && auth()->user()->show_online_status) {
        Cache::put(
            'user-online-' . auth()->id(),
            true,
            now()->addMinutes(5)
        );
    }
    
    return $next($request);
}

// Get online status
public function isOnline(User $user): bool
{
    if (!$user->show_online_status) {
        return false; // Hide status
    }
    
    return Cache::has('user-online-' . $user->id);
}
```

### Blocked Users Management

Block users to prevent all interactions.

#### Database Schema

```sql
CREATE TABLE blocked_users (
    id BIGSERIAL PRIMARY KEY,
    blocker_id BIGINT NOT NULL,
    blocked_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (blocker_id, blocked_id),
    INDEX idx_blocker (blocker_id),
    INDEX idx_blocked (blocked_id)
);
```

#### Implementation

```php
// User Model
public function blockedUsers()
{
    return $this->belongsToMany(User::class, 'blocked_users', 'blocker_id', 'blocked_id')
        ->withTimestamps();
}

public function hasBlocked(User $user): bool
{
    return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
}

public function isBlockedBy(User $user): bool
{
    return $user->hasBlocked($this);
}

// BlockUserController
public function block(User $user)
{
    if (auth()->id() === $user->id) {
        abort(422, 'Cannot block yourself');
    }
    
    auth()->user()->blockedUsers()->syncWithoutDetaching([$user->id]);
    
    // Remove existing follow relationships
    auth()->user()->unfollow($user);
    $user->unfollow(auth()->user());
    
    return response()->json(['success' => true]);
}

public function unblock(User $user)
{
    auth()->user()->blockedUsers()->detach($user->id);
    
    return response()->json(['success' => true]);
}

public function index()
{
    $blockedUsers = auth()->user()
        ->blockedUsers()
        ->select('id', 'name', 'username', 'avatar')
        ->get();
    
    return inertia('Settings/BlockedUsers', [
        'blockedUsers' => $blockedUsers,
    ]);
}
```

#### Apply Block Logic Globally

```php
// Middleware: CheckBlockedUsers
public function handle(Request $request, Closure $next)
{
    if (!auth()->check()) {
        return $next($request);
    }
    
    $blockedIds = auth()->user()->blockedUsers()->pluck('id')->toArray();
    $blockedByIds = User::whereHas('blockedUsers', function ($q) {
        $q->where('blocked_id', auth()->id());
    })->pluck('id')->toArray();
    
    $request->merge([
        'blocked_user_ids' => array_merge($blockedIds, $blockedByIds),
    ]);
    
    return $next($request);
}

// Apply in queries
Hunt::whereNotIn('user_id', $request->input('blocked_user_ids', []))
    ->latest()
    ->get();
```

#### Frontend

```tsx
export function BlockedUsers() {
    const { blockedUsers } = usePage().props;
    const [users, setUsers] = useState(blockedUsers);
    
    const handleUnblock = (userId: number) => {
        if (!confirm('Are you sure you want to unblock this user?')) return;
        
        router.delete(`/settings/blocked-users/${userId}`, {
            onSuccess: () => {
                setUsers(users.filter(u => u.id !== userId));
            },
        });
    };
    
    return (
        <div className="space-y-4">
            <h2>Blocked Users</h2>
            {users.length === 0 ? (
                <p className="text-muted-foreground">No blocked users</p>
            ) : (
                users.map(user => (
                    <div key={user.id} className="flex items-center gap-4">
                        <Avatar src={user.avatar} />
                        <div className="flex-1">
                            <p className="font-semibold">{user.name}</p>
                            <p className="text-sm text-muted-foreground">@{user.username}</p>
                        </div>
                        <button onClick={() => handleUnblock(user.id)}>
                            Unblock
                        </button>
                    </div>
                ))
            )}
        </div>
    );
}
```

## 2. Security Section

### Password Management

Allow users to update their password securely.

#### Implementation

```php
// PasswordController
public function update(Request $request)
{
    $validated = $request->validate([
        'current_password' => 'required|current_password',
        'password' => 'required|confirmed|min:8',
    ]);
    
    auth()->user()->update([
        'password' => Hash::make($validated['password']),
    ]);
    
    // Log the password change
    AuthLog::create([
        'user_id' => auth()->id(),
        'event' => 'password_changed',
        'ip_address' => $request->ip(),
    ]);
    
    // Send email notification
    auth()->user()->notify(new PasswordChangedNotification());
    
    return back()->with('success', 'Password updated successfully');
}
```

### Two-Factor Authentication (2FA)

Enable 2FA for additional account security.

#### Database Schema

```sql
ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULL;
ALTER TABLE users ADD COLUMN two_factor_recovery_codes TEXT NULL;
ALTER TABLE users ADD COLUMN two_factor_confirmed_at TIMESTAMP NULL;
```

#### Implementation (Using Laravel Fortify)

```php
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Model
{
    use TwoFactorAuthenticatable;
}

// Enable 2FA
public function enable2FA()
{
    auth()->user()->update([
        'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
        'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
            return RecoveryCode::generate();
        })->all())),
    ]);
    
    return response()->json([
        'qr_code' => auth()->user()->twoFactorQrCodeSvg(),
        'recovery_codes' => json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true),
    ]);
}

// Confirm 2FA
public function confirm2FA(Request $request)
{
    $validated = $request->validate([
        'code' => 'required|string',
    ]);
    
    if (!app(TwoFactorAuthenticationProvider::class)->verify(
        decrypt(auth()->user()->two_factor_secret),
        $validated['code']
    )) {
        throw ValidationException::withMessages(['code' => 'Invalid code']);
    }
    
    auth()->user()->update([
        'two_factor_confirmed_at' => now(),
    ]);
    
    return response()->json(['success' => true]);
}
```

### Active Sessions Management

View and manage active login sessions.

#### Database Schema

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,
    last_activity INTEGER,
    
    INDEX idx_user (user_id),
    INDEX idx_last_activity (last_activity)
);
```

#### Implementation

```php
public function getSessions()
{
    $sessions = DB::table('sessions')
        ->where('user_id', auth()->id())
        ->orderByDesc('last_activity')
        ->get()
        ->map(function ($session) {
            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => Carbon::createFromTimestamp($session->last_activity),
                'is_current' => $session->id === session()->getId(),
            ];
        });
    
    return inertia('Settings/Sessions', [
        'sessions' => $sessions,
    ]);
}

public function destroySession(string $sessionId)
{
    DB::table('sessions')
        ->where('id', $sessionId)
        ->where('user_id', auth()->id())
        ->delete();
    
    return response()->json(['success' => true]);
}

public function destroyAllSessions()
{
    DB::table('sessions')
        ->where('user_id', auth()->id())
        ->where('id', '!=', session()->getId())
        ->delete();
    
    return back()->with('success', 'All other sessions logged out');
}
```

### Connected Accounts (OAuth)

Manage connected OAuth providers (GitHub, Google).

#### Database Schema

```sql
CREATE TABLE social_accounts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_id VARCHAR(255) NOT NULL,
    connected_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (provider, provider_id),
    INDEX idx_user (user_id)
);
```

#### Implementation

```php
public function getConnectedAccounts()
{
    $accounts = auth()->user()
        ->socialAccounts()
        ->get()
        ->map(fn($account) => [
            'provider' => $account->provider,
            'connected_at' => $account->connected_at,
        ]);
    
    return inertia('Settings/ConnectedAccounts', [
        'accounts' => $accounts,
    ]);
}

public function disconnectAccount(string $provider)
{
    // Ensure user has another auth method
    $accountCount = auth()->user()->socialAccounts()->count();
    $hasPassword = !empty(auth()->user()->password);
    
    if ($accountCount === 1 && !$hasPassword) {
        abort(422, 'Cannot disconnect last authentication method');
    }
    
    auth()->user()->socialAccounts()->where('provider', $provider)->delete();
    
    return back()->with('success', ucfirst($provider) . ' disconnected');
}
```

### Account Deletion

Allow users to permanently delete their account.

#### Implementation

```php
public function destroy(Request $request)
{
    $validated = $request->validate([
        'password' => 'required|current_password',
        'confirmation' => 'required|in:DELETE',
    ]);
    
    $user = auth()->user();
    
    // Soft delete or hard delete
    $user->delete();
    
    // Log out
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/')->with('success', 'Account deleted successfully');
}
```

## Complete Settings UI

### Privacy Settings Page

```tsx
export default function PrivacySettings() {
    const { user } = usePage().props;
    
    return (
        <div className="space-y-8">
            <section>
                <h2>Profile Visibility</h2>
                <VisibilitySelector
                    value={user.profile_visibility}
                    onChange={(val) => updateSetting('profile_visibility', val)}
                />
            </section>
            
            <section>
                <h2>Messaging</h2>
                <VisibilitySelector
                    value={user.messaging_privacy}
                    onChange={(val) => updateSetting('messaging_privacy', val)}
                />
            </section>
            
            <section>
                <h2>Hunt Comments</h2>
                <VisibilitySelector
                    value={user.hunt_comment_privacy}
                    onChange={(val) => updateSetting('hunt_comment_privacy', val)}
                />
            </section>
            
            <section>
                <h2>Search & Explore</h2>
                <Toggle
                    checked={user.search_visibility}
                    onChange={(val) => updateSetting('search_visibility', val)}
                    label="Appear in search and explore"
                />
            </section>
            
            <section>
                <h2>Online Status</h2>
                <Toggle
                    checked={user.show_online_status}
                    onChange={(val) => updateSetting('show_online_status', val)}
                    label="Show when I'm online"
                />
            </section>
            
            <section>
                <h2>Blocked Users</h2>
                <Link href="/settings/blocked-users">
                    Manage blocked users
                </Link>
            </section>
        </div>
    );
}
```

### Security Settings Page

```tsx
export default function SecuritySettings() {
    const { user, sessions } = usePage().props;
    
    return (
        <div className="space-y-8">
            <section>
                <h2>Password</h2>
                <PasswordChangeForm />
            </section>
            
            <section>
                <h2>Two-Factor Authentication</h2>
                {user.two_factor_confirmed_at ? (
                    <Disable2FAForm />
                ) : (
                    <Enable2FAForm />
                )}
            </section>
            
            <section>
                <h2>Active Sessions</h2>
                <SessionsList sessions={sessions} />
            </section>
            
            <section>
                <h2>Connected Accounts</h2>
                <ConnectedAccountsList />
            </section>
            
            <section>
                <h2>Delete Account</h2>
                <DangerZone />
            </section>
        </div>
    );
}
```

## Testing

```php
test('user can block another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    
    $this->actingAs($user)
        ->post("/api/users/{$target->id}/block");
    
    expect($user->hasBlocked($target))->toBeTrue();
});

test('blocked user cannot send messages', function () {
    $user = User::factory()->create();
    $blocked = User::factory()->create();
    
    $user->blockedUsers()->attach($blocked);
    
    $this->actingAs($blocked)
        ->post('/api/conversations', [
            'recipient_id' => $user->id,
            'message' => 'Hello',
        ])
        ->assertForbidden();
});

test('private profile is not visible to non-followers', function () {
    $user = User::factory()->create(['profile_visibility' => 'private']);
    $other = User::factory()->create();
    
    $this->actingAs($other)
        ->get("/profile/{$user->username}")
        ->assertForbidden();
});
```

## Best Practices

1. **Respect Privacy Settings Everywhere**: Check privacy flags in all relevant queries
2. **Notify Users of Security Changes**: Send emails for password changes, 2FA, etc.
3. **Provide Clear Explanations**: Help text for each setting
4. **Default to Safe**: Private/restrictive defaults for new users
5. **Audit Security Events**: Log all security-related actions

## Related Documentation

- [Authentication](./02-authentication.md) - Login and OAuth
- [User Profiles](./03-user-profiles.md) - Profile management
- [Real-time Chat](./06-real-time-chat.md) - Messaging privacy
- [Testing](./10-testing.md) - Testing guidelines
