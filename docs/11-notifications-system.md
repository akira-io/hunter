# 🔔 Notifications System

## Overview

Hunter features a comprehensive real-time notification system that keeps users informed about important events and interactions. The system supports both in-app and email notifications with granular control over notification preferences.

## Features

### Notification Types

#### 1. **Social Notifications**
- **Follower Notifications**: When another Hunter follows you
- **Like Notifications**: When someone likes your Hunt or comment
- **Comment Notifications**: When someone comments on your Hunt

#### 2. **System Notifications**
- Important platform updates
- Account security alerts
- Administrative messages

### Notification Settings

Users can control their notification preferences independently:

```php
// Location: Settings → Notifications

[
    'email_notifications' => true,      // Master email toggle
    'follower_notifications' => true,   // In-app follower alerts
    'like_notifications' => true,       // In-app like alerts
    'comment_notifications' => true,    // In-app comment alerts
]
```

#### Important Behavior

- **In-app and Email are Independent**: Disabling in-app notifications does NOT block email notifications
- Users can receive email notifications even if in-app notifications are disabled
- Each notification type can be configured separately

## Implementation

### Backend

#### Notification Model

```php
// Using Laravel's default notification system
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    
    // Notification settings stored in user table
    protected $casts = [
        'email_notifications' => 'boolean',
        'follower_notifications' => 'boolean',
        'like_notifications' => 'boolean',
        'comment_notifications' => 'boolean',
    ];
}
```

#### Creating Notifications

```php
use App\Notifications\FollowerNotification;

// Trigger a follower notification
$follower->notify(new FollowerNotification($user));
```

#### Notification Channel Logic

```php
// Check if email should be sent
public function via($notifiable)
{
    $channels = ['database']; // Always add to in-app
    
    // Add email channel if enabled
    if ($notifiable->email_notifications) {
        $channels[] = 'mail';
    }
    
    return $channels;
}
```

### Frontend

#### Displaying Notifications

```tsx
import { usePage } from '@inertiajs/react';

function NotificationBell() {
    const { notifications, unreadCount } = usePage().props;
    
    return (
        <button className="relative">
            <BellIcon />
            {unreadCount > 0 && (
                <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5">
                    {unreadCount}
                </span>
            )}
        </button>
    );
}
```

#### Real-time Updates with Laravel Echo

```tsx
import Echo from 'laravel-echo';

useEffect(() => {
    window.Echo.private(`user.${userId}`)
        .notification((notification) => {
            // Update notification list
            setNotifications(prev => [notification, ...prev]);
            setUnreadCount(prev => prev + 1);
        });
}, [userId]);
```

## API Reference

### Get User Notifications

```http
GET /api/notifications
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": [
        {
            "id": "uuid",
            "type": "App\\Notifications\\FollowerNotification",
            "data": {
                "follower_id": 1,
                "follower_name": "John Doe",
                "message": "started following you"
            },
            "read_at": null,
            "created_at": "2025-01-15T10:30:00Z"
        }
    ],
    "unread_count": 5
}
```

### Mark as Read

```http
POST /api/notifications/{id}/read
Authorization: Bearer {token}
```

### Mark All as Read

```http
POST /api/notifications/read-all
Authorization: Bearer {token}
```

### Update Notification Settings

```http
PUT /api/settings/notifications
Authorization: Bearer {token}

{
    "email_notifications": true,
    "follower_notifications": false,
    "like_notifications": true,
    "comment_notifications": true
}
```

## Database Schema

### Notifications Table (Laravel Default)

```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT NOT NULL,
    data JSON NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_notifiable (notifiable_type, notifiable_id),
    INDEX idx_read_at (read_at)
);
```

### User Notification Settings

```sql
-- Stored in users table
ALTER TABLE users ADD COLUMN email_notifications BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN follower_notifications BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN like_notifications BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN comment_notifications BOOLEAN DEFAULT TRUE;
```

## Testing

### Example Test: Follower Notification

```php
use Tests\TestCase;
use App\Models\User;
use App\Notifications\FollowerNotification;

test('user receives notification when followed', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    
    $follower->follow($user);
    
    expect($user->notifications()->count())->toBe(1);
    expect($user->notifications->first())
        ->type->toBe(FollowerNotification::class);
});

test('email sent when email notifications enabled', function () {
    Notification::fake();
    
    $user = User::factory()->create(['email_notifications' => true]);
    $follower = User::factory()->create();
    
    $follower->follow($user);
    
    Notification::assertSentTo($user, FollowerNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels);
    });
});

test('no email sent when email notifications disabled', function () {
    Notification::fake();
    
    $user = User::factory()->create(['email_notifications' => false]);
    $follower = User::factory()->create();
    
    $follower->follow($user);
    
    Notification::assertSentTo($user, FollowerNotification::class, function ($notification, $channels) {
        return !in_array('mail', $channels);
    });
});
```

## Best Practices

### 1. **Respect User Preferences**
Always check notification settings before sending:
```php
if ($user->follower_notifications) {
    // Show in-app notification
}

if ($user->email_notifications) {
    // Send email
}
```

### 2. **Use Queued Notifications**
Send notifications asynchronously to improve performance:
```php
class FollowerNotification extends Notification implements ShouldQueue
{
    use Queueable;
}
```

### 3. **Batch Mark as Read**
Allow users to mark multiple notifications as read:
```php
$user->unreadNotifications->markAsRead();
```

### 4. **Clean Old Notifications**
Schedule cleanup of old read notifications:
```php
// In App\Console\Kernel
$schedule->command('notifications:clean')->daily();
```

```php
// Command
Notification::where('read_at', '!=', null)
    ->where('created_at', '<', now()->subMonths(3))
    ->delete();
```

## Future Enhancements

Based on the roadmap, these features are planned:

- **Granular Settings**: Per-notification-type email controls
- **Notification Audit**: Log of all sent notifications
- **Push Notifications**: Browser push for web app
- **Mobile Notifications**: iOS/Android app integration
- **Notification Grouping**: Group similar notifications
- **Time Windows**: Filter notifications by date range (with unread always loaded)
- **Read Receipts with Elapsed Time**: Show "Seen 2 hours ago"

## Troubleshooting

### Issue: Email Notifications Not Received

**Check:**
1. Email driver configuration in `.env`
2. User's `email_notifications` setting is `true`
3. Queue worker is running: `php artisan queue:work`
4. Email is not in spam folder

### Issue: Real-time Notifications Not Appearing

**Check:**
1. Laravel Reverb is running: `php artisan reverb:start`
2. Echo configuration in `resources/js/bootstrap.ts`
3. User is authenticated
4. Browser console for WebSocket errors

### Issue: Notification Count Not Updating

**Solution:**
```php
// Force refresh count
$unreadCount = auth()->user()->unreadNotifications()->count();
```

## Related Documentation

- [Real-time Chat](./06-real-time-chat.md) - WebSocket setup
- [API Reference](./08-api-reference.md) - Complete API docs
- [Testing](./10-testing.md) - Testing guidelines
