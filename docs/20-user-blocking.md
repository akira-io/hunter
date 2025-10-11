# User Blocking System

## Overview

The User Blocking System allows users to block other users, preventing them from interacting with blocked users' content, sending messages, or seeing their activity. This feature enhances user control and safety on the platform.

**New in v0.6.0** (October 16, 2025)

## Architecture

```
┌─────────────────────────────────────────────────┐
│            User Blocking System                  │
│         (Profile → Block/Unblock User)           │
└─────────────────────────────────────────────────┘
        │
        ├── Block Management
        │   ├── Block User Action
        │   ├── Unblock User Action
        │   └── Check Blocked Status
        │
        ├── Restrictions
        │   ├── Messaging Blocked
        │   ├── Profile Visibility Hidden
        │   ├── Content Interactions Blocked
        │   └── Follow Prevented
        │
        └── UI Components
            ├── Block Button
            ├── Blocked Users List
            └── Warning Messages
```

## Database Schema

```sql
-- blocked_users table
CREATE TABLE blocked_users (
    id BIGSERIAL PRIMARY KEY,
    blocker_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    blocked_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE(blocker_id, blocked_id)
);

-- Indexes for performance
CREATE INDEX idx_blocked_users_blocker ON blocked_users(blocker_id);
CREATE INDEX idx_blocked_users_blocked ON blocked_users(blocked_id);
CREATE INDEX idx_blocked_users_pair ON blocked_users(blocker_id, blocked_id);
```

### Model Definition

```php
// app/Models/BlockedUser.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedUser extends Model
{
    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    /**
     * The user who blocked
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    /**
     * The user who was blocked
     */
    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }
}
```

## Backend Implementation

### User Model Methods

```php
// app/Models/User.php

use App\Models\BlockedUser;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    /**
     * Users this user has blocked
     */
    public function blockedUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class, 'blocker_id');
    }

    /**
     * Users who have blocked this user
     */
    public function blockedByUsers(): HasMany
    {
        return $this->hasMany(BlockedUser::class, 'blocked_id');
    }

    /**
     * Block a user
     */
    public function block(self $user): void
    {
        if ($this->hasBlocked($user)) {
            return; // Already blocked (idempotent)
        }

        $this->blockedUsers()->create([
            'blocked_id' => $user->id,
        ]);
    }

    /**
     * Unblock a user
     */
    public function unblock(self $user): void
    {
        $this->blockedUsers()
            ->where('blocked_id', $user->id)
            ->delete();
    }

    /**
     * Check if user has blocked another user
     */
    public function hasBlocked(self $user): bool
    {
        return $this->blockedUsers()
            ->where('blocked_id', $user->id)
            ->exists();
    }

    /**
     * Check if user is blocked by another user
     */
    public function isBlockedBy(self $user): bool
    {
        return $this->blockedByUsers()
            ->where('blocker_id', $user->id)
            ->exists();
    }

    /**
     * Check if user can receive messages from sender
     */
    public function canReceiveMessagesFrom(?self $sender): bool
    {
        if (!$sender instanceof self || $sender->id === $this->id) {
            return false;
        }

        // Check if either user has blocked the other
        if ($this->hasBlocked($sender) || $this->isBlockedBy($sender)) {
            return false;
        }

        // Check privacy settings
        return match($this->privacy_settings['who_can_message'] ?? 'everyone') {
            'everyone' => true,
            'followers' => $this->isFollowedBy($sender),
            'none' => false,
            default => true,
        };
    }
}
```

### Block User Action

```php
// app/Actions/Settings/BlockUserAction.php
<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;
use InvalidArgumentException;

final readonly class BlockUserAction
{
    /**
     * Block a user
     *
     * @throws InvalidArgumentException if trying to block self
     */
    public function handle(User $blocker, int $userId): void
    {
        $userToBlock = User::query()->findOrFail($userId);

        if ($blocker->id === $userToBlock->id) {
            throw new InvalidArgumentException('Não pode bloquear-se a si mesmo.');
        }

        $blocker->block($userToBlock);

        // Log activity
        activity()
            ->causedBy($blocker)
            ->performedOn($userToBlock)
            ->log('User blocked');

        // Unfollow if following
        if ($blocker->isFollowing($userToBlock)) {
            $blocker->unfollow($userToBlock);
        }
        
        if ($userToBlock->isFollowing($blocker)) {
            $userToBlock->unfollow($blocker);
        }
    }
}
```

### Controller

```php
// app/Http/Controllers/Settings/BlockUserController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\BlockUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockUserController extends Controller
{
    public function __construct(
        private readonly BlockUserAction $blockUserAction
    ) {}

    /**
     * Block a user
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $this->blockUserAction->handle(
                $request->user(),
                $validated['user_id']
            );

            return back()->with('success', 'User blocked successfully');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Unblock a user
     */
    public function destroy(Request $request, int $userId): RedirectResponse
    {
        $userToUnblock = User::findOrFail($userId);
        
        $request->user()->unblock($userToUnblock);

        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($userToUnblock)
            ->log('User unblocked');

        return back()->with('success', 'User unblocked successfully');
    }

    /**
     * List blocked users
     */
    public function index(Request $request)
    {
        $blockedUsers = $request->user()
            ->blockedUsers()
            ->with('blocked')
            ->latest()
            ->paginate(20);

        return inertia('Settings/BlockedUsers', [
            'blockedUsers' => $blockedUsers,
        ]);
    }
}
```

### Routes

```php
// routes/web.php

use App\Http\Controllers\Settings\BlockUserController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Block management
    Route::post('/settings/block-user', [BlockUserController::class, 'store'])
        ->name('settings.block-user.store');
    
    Route::delete('/settings/block-user/{userId}', [BlockUserController::class, 'destroy'])
        ->name('settings.block-user.destroy');
    
    Route::get('/settings/blocked-users', [BlockUserController::class, 'index'])
        ->name('settings.blocked-users');
});
```

## Frontend Implementation

### Block Button Component

```typescript
// resources/js/components/profile/block-button.tsx

import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Ban, ShieldOff } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface BlockButtonProps {
    userId: number;
    userName: string;
    isBlocked: boolean;
}

export function BlockButton({ userId, userName, isBlocked }: BlockButtonProps) {
    const [showConfirm, setShowConfirm] = useState(false);
    const [isProcessing, setIsProcessing] = useState(false);

    const handleBlock = () => {
        setIsProcessing(true);

        router.post(
            '/settings/block-user',
            { user_id: userId },
            {
                preserveScroll: true,
                onFinish: () => {
                    setIsProcessing(false);
                    setShowConfirm(false);
                },
            }
        );
    };

    const handleUnblock = () => {
        setIsProcessing(true);

        router.delete(`/settings/block-user/${userId}`, {
            preserveScroll: true,
            onFinish: () => {
                setIsProcessing(false);
                setShowConfirm(false);
            },
        });
    };

    if (isBlocked) {
        return (
            <>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setShowConfirm(true)}
                    disabled={isProcessing}
                >
                    <ShieldOff className="mr-2 h-4 w-4" />
                    Unblock
                </Button>

                <AlertDialog open={showConfirm} onOpenChange={setShowConfirm}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Unblock {userName}?</AlertDialogTitle>
                            <AlertDialogDescription>
                                This user will be able to follow you, message you, and see your content again.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction onClick={handleUnblock} disabled={isProcessing}>
                                Unblock User
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </>
        );
    }

    return (
        <>
            <Button
                variant="ghost"
                size="sm"
                onClick={() => setShowConfirm(true)}
                disabled={isProcessing}
                className="text-destructive hover:text-destructive"
            >
                <Ban className="mr-2 h-4 w-4" />
                Block User
            </Button>

            <AlertDialog open={showConfirm} onOpenChange={setShowConfirm}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Block {userName}?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Blocked users cannot:
                            <ul className="mt-2 list-inside list-disc space-y-1">
                                <li>Send you messages</li>
                                <li>See your profile or content</li>
                                <li>Follow you</li>
                                <li>Interact with your hunts</li>
                            </ul>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleBlock}
                            disabled={isProcessing}
                            className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        >
                            Block User
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
```

### Blocked Users List

```typescript
// resources/js/pages/settings/blocked-users.tsx

import { Head, Link } from '@inertiajs/react';
import { SettingsLayout } from '@/layouts/settings-layout';
import { BlockButton } from '@/components/profile/block-button';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { formatDistanceToNow } from 'date-fns';

interface BlockedUser {
    id: number;
    blocked: {
        id: number;
        name: string;
        user_name: string;
        avatar_url: string;
    };
    created_at: string;
}

interface Props {
    blockedUsers: {
        data: BlockedUser[];
        links: any;
        meta: any;
    };
}

export default function BlockedUsers({ blockedUsers }: Props) {
    return (
        <>
            <Head title="Blocked Users" />

            <SettingsLayout>
                <div className="space-y-6">
                    {/* Header */}
                    <div>
                        <h2 className="text-2xl font-bold">Blocked Users</h2>
                        <p className="text-muted-foreground">
                            Users you have blocked cannot interact with you
                        </p>
                    </div>

                    {/* Blocked Users List */}
                    {blockedUsers.data.length === 0 ? (
                        <div className="rounded-lg border p-8 text-center">
                            <p className="text-muted-foreground">
                                You haven't blocked anyone yet
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {blockedUsers.data.map((block) => (
                                <div
                                    key={block.id}
                                    className="flex items-center justify-between rounded-lg border p-4"
                                >
                                    {/* User Info */}
                                    <div className="flex items-center gap-4">
                                        <Avatar>
                                            <AvatarImage src={block.blocked.avatar_url} />
                                            <AvatarFallback>
                                                {block.blocked.name.charAt(0)}
                                            </AvatarFallback>
                                        </Avatar>

                                        <div>
                                            <Link
                                                href={`/hunters/${block.blocked.user_name}`}
                                                className="font-medium hover:underline"
                                            >
                                                {block.blocked.name}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">
                                                @{block.blocked.user_name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                Blocked{' '}
                                                {formatDistanceToNow(new Date(block.created_at), {
                                                    addSuffix: true,
                                                })}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Unblock Button */}
                                    <BlockButton
                                        userId={block.blocked.id}
                                        userName={block.blocked.name}
                                        isBlocked={true}
                                    />
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Pagination */}
                    {blockedUsers.data.length > 0 && (
                        <div className="flex items-center justify-center gap-2">
                            {blockedUsers.links.map((link: any, index: number) => (
                                <Link
                                    key={index}
                                    href={link.url || '#'}
                                    className={`rounded px-3 py-1 ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'hover:bg-muted'
                                    }`}
                                    preserveScroll
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </>
    );
}
```

## Testing

### Unit Tests

```php
// tests/Unit/Actions/Settings/BlockUserActionTest.php

use App\Actions\Settings\BlockUserAction;
use App\Models\User;

test('successfully blocks another user', function () {
    $blocker = User::factory()->create();
    $userToBlock = User::factory()->create();
    $action = new BlockUserAction();

    $action->handle($blocker, $userToBlock->id);

    expect($blocker->hasBlocked($userToBlock))->toBeTrue();
});

test('cannot block yourself', function () {
    $user = User::factory()->create();
    $action = new BlockUserAction();

    expect(fn () => $action->handle($user, $user->id))
        ->toThrow(InvalidArgumentException::class, 'Não pode bloquear-se a si mesmo.');
});

test('throws exception when user not found', function () {
    $blocker = User::factory()->create();
    $action = new BlockUserAction();

    expect(fn () => $action->handle($blocker, 99999))
        ->toThrow(ModelNotFoundException::class);
});

test('can block multiple users', function () {
    $blocker = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $action = new BlockUserAction();

    $action->handle($blocker, $user1->id);
    $action->handle($blocker, $user2->id);

    expect($blocker->hasBlocked($user1))->toBeTrue()
        ->and($blocker->hasBlocked($user2))->toBeTrue();
});

test('blocking is idempotent', function () {
    $blocker = User::factory()->create();
    $userToBlock = User::factory()->create();
    $action = new BlockUserAction();

    $action->handle($blocker, $userToBlock->id);
    $action->handle($blocker, $userToBlock->id);

    expect($blocker->blockedUsers()->count())->toBe(1);
});

test('creates database record', function () {
    $blocker = User::factory()->create();
    $userToBlock = User::factory()->create();
    $action = new BlockUserAction();

    $action->handle($blocker, $userToBlock->id);

    $this->assertDatabaseHas('blocked_users', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $userToBlock->id,
    ]);
});
```

### Model Tests

```php
// tests/Unit/Models/UserTest.php

test('can receive messages from another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    expect($user1->canReceiveMessagesFrom($user2))->toBeTrue();
});

test('cannot receive messages from blocked user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->block($user2);

    expect($user1->canReceiveMessagesFrom($user2))->toBeFalse();
});

test('cannot receive messages when blocked by sender', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user2->block($user1);

    expect($user1->canReceiveMessagesFrom($user2))->toBeFalse();
});

test('cannot receive messages from null sender', function () {
    $user = User::factory()->create();

    expect($user->canReceiveMessagesFrom(null))->toBeFalse();
});

test('cannot receive messages from itself', function () {
    $user = User::factory()->create();

    expect($user->canReceiveMessagesFrom($user))->toBeFalse();
});
```

## Usage Examples

### Add Block Button to Profile

```typescript
// In profile page
import { BlockButton } from '@/components/profile/block-button';

export default function Profile({ user, isBlocked }) {
    return (
        <div>
            <h1>{user.name}</h1>
            
            {auth.user.id !== user.id && (
                <BlockButton
                    userId={user.id}
                    userName={user.name}
                    isBlocked={isBlocked}
                />
            )}
        </div>
    );
}
```

### Check Block Status in Backend

```php
// Before showing content
if ($viewer->hasBlocked($user) || $viewer->isBlockedBy($user)) {
    return response()->json(['error' => 'User is blocked'], 403);
}

// Allow content access
return response()->json($content);
```

### Middleware for Blocked Users

```php
// app/Http/Middleware/CheckBlockedUsers.php

class CheckBlockedUsers
{
    public function handle(Request $request, Closure $next)
    {
        $targetUserId = $request->route('user');
        
        if ($targetUserId && auth()->check()) {
            $targetUser = User::find($targetUserId);
            
            if ($targetUser && (
                auth()->user()->hasBlocked($targetUser) ||
                auth()->user()->isBlockedBy($targetUser)
            )) {
                abort(403, 'Cannot access this content');
            }
        }

        return $next($request);
    }
}
```

## Related Documentation

- [14. Privacy & Security](./14-privacy-security.md)
- [05. Social Features](./05-social-features.md)
- [06. Real-time Chat](./06-real-time-chat.md)
- [10. Testing](./10-testing.md)

---

**Version**: 0.6.0  
**Last Updated**: October 16, 2025
