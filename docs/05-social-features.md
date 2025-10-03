# Social Features

## Overview

Hunter includes comprehensive social features that allow developers to connect, follow each other, interact with content, and build a professional network.

## Following System

Hunter uses the `akira/laravel-followable` package for following functionality.

### Follow a User

```php
use App\Models\User;

$user = auth()->user();
$targetUser = User::find(2);

// Follow a user
$user->follow($targetUser);

// Check if following
if ($user->isFollowing($targetUser)) {
    // User is following target
}

// Get followers count
$followersCount = $targetUser->followersCount();

// Get following count
$followingCount = $user->followingsCount();
```

**API Route:** `POST /follow/{user}`

```php
use App\Http\Controllers\Followable\FollowController;

Route::post('/follow/{user}', [FollowController::class, 'store'])
    ->middleware('auth');
```

**Request:**

```bash
POST /follow/123
Authorization: Bearer {token}
```

**Response:**

```json
{
  "message": "Successfully followed user",
  "is_following": true,
  "followers_count": 151
}
```

### Unfollow a User

```php
// Unfollow a user
$user->unfollow($targetUser);
```

**API Route:** `POST /unfollow/{user}`

```php
use App\Http\Controllers\Followable\UnFollowController;

Route::post('/unfollow/{user}', [UnFollowController::class, 'store'])
    ->middleware('auth');
```

### Get Followers

Get users who follow a specific user:

```php
// Get all followers
$followers = $user->followers()->get();

// Get followers with details
$followers = $user->followers()
    ->with(['follower' => function ($query) {
        $query->select('id', 'name', 'user_name', 'avatar_url', 'bio');
    }])
    ->latest()
    ->paginate(20);
```

**API Route:** `GET /api/followed-hunters`

```php
use App\Http\Controllers\Followable\GetHuntersController;

Route::get('/hunters', [GetHuntersController::class, 'index']);
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "user_name": "johndoe",
      "avatar_url": "https://...",
      "bio": "Full-stack developer",
      "is_following": false,
      "followers_count": 150,
      "following_count": 89
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150
  }
}
```

### Get Following

Get users that a specific user follows:

```php
use App\Actions\Followable\GetHuntingsAction;

// Get who the user is following
$following = app(GetHuntingsAction::class)->handle($userId);
```

**API Route:** `GET /huntings`

```php
use App\Http\Controllers\Followable\GetHuntingsController;

Route::get('/huntings', [GetHuntingsController::class, 'index']);
```

## Likes System

Hunter uses the `akira/laravel-likeable` package for likes functionality.

### Like Content

#### Like a Hunt

```php
use App\Models\Hunt;

$hunt = Hunt::find(1);
$user = auth()->user();

// Like
$user->like($hunt);

// Unlike
$user->unlike($hunt);

// Toggle like
$user->toggleLike($hunt);

// Check if liked
if ($user->hasLiked($hunt)) {
    // User has liked this hunt
}
```

**API Route:** `POST /hunts/{hunt}/like`

```php
use App\Http\Controllers\Likeable\ToggleHuntLikeController;

Route::post('/hunts/{hunt}/like', [ToggleHuntLikeController::class, 'store']);
```

**Request:**

```bash
POST /hunts/123/like
Authorization: Bearer {token}
```

**Response:**

```json
{
  "message": "Hunt liked successfully",
  "has_liked": true,
  "likes_count": 43
}
```

#### Like a Comment

```php
use App\Models\Comment;

$comment = Comment::find(1);

// Like comment
$user->like($comment);

// Check if liked
if ($user->hasLiked($comment)) {
    // User has liked this comment
}
```

**API Route:** `POST /comments/{comment}/like`

```php
use App\Http\Controllers\Likeable\ToggleCommentLikeController;

Route::post('/comments/{comment}/like', [ToggleCommentLikeController::class, 'store']);
```

### Get Likes

```php
// Get all users who liked a hunt
$likers = $hunt->likers()->get();

// Get likes count
$likesCount = $hunt->likesCount();

// Get all hunts a user has liked
$likedHunts = $user->likes()
    ->where('likeable_type', Hunt::class)
    ->get();
```

## Comments System

Hunter uses the `akira/laravel-commentable` package for comments.

### Add Comment

```php
use App\Models\Hunt;

$hunt = Hunt::find(1);
$user = auth()->user();

// Comment on a hunt
$comment = $user->comment($hunt, 'Great post!');

// Or using the model directly
$comment = $hunt->comment('Great post!');

// Comment as specific user
$comment = $hunt->commentAsUser($user, 'Awesome work!');
```

**API Route:** `POST /hunts/{hunt}/comments`

**Request:**

```json
{
  "content": "Great post! Thanks for sharing your experience."
}
```

**Response:**

```json
{
  "data": {
    "id": 1,
    "content": "Great post! Thanks for sharing.",
    "created_at": "2024-01-15T10:30:00Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "user_name": "johndoe",
      "avatar_url": "https://..."
    },
    "likes_count": 0,
    "has_liked": false
  }
}
```

### Get Comments

```php
// Get all comments on a hunt
$comments = $hunt->comments()
    ->with('user')
    ->latest()
    ->get();

// Get comments count
$commentsCount = $hunt->commentsCount();

// Get paginated comments
$comments = $hunt->comments()
    ->with(['user', 'likes'])
    ->withCount('likes')
    ->latest()
    ->paginate(20);
```

**API Route:** `GET /hunts/{hunt}/comments`

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "content": "Great post!",
      "created_at": "2024-01-15T10:30:00Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "user_name": "johndoe",
        "avatar_url": "https://..."
      },
      "likes_count": 5,
      "has_liked": true
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 42
  }
}
```

### Delete Comment

```php
$comment->delete();
```

**API Route:** `DELETE /comments/{comment}`

**Authorization:** User must own the comment or be the hunt owner

```php
use App\Policies\CommentPolicy;

class CommentPolicy
{
    public function delete(User $user, Comment $comment): bool
    {
        // Owner of comment or owner of the commentable
        return $user->id === $comment->user_id 
            || $user->id === $comment->commentable->owner_id;
    }
}
```

## Notifications

Hunter includes a comprehensive notification system for social interactions.

### Notification Types

#### User Followed Notification

Sent when someone follows a user:

```php
use App\Notifications\UserFollowedNotification;

// Send notification
$targetUser->notify(new UserFollowedNotification($follower));
```

**Notification data:**

```json
{
  "type": "user_followed",
  "follower": {
    "id": 1,
    "name": "John Doe",
    "user_name": "johndoe",
    "avatar_url": "https://..."
  },
  "message": "John Doe started following you"
}
```

### Get Notifications

**API Route:** `GET /api/notifications`

```php
use App\Http\Controllers\Api\NotificationController;

Route::get('/notifications', [NotificationController::class, 'index']);
```

**Response:**

```json
{
  "data": [
    {
      "id": "uuid-here",
      "type": "user_followed",
      "data": {
        "follower": {
          "id": 1,
          "name": "John Doe",
          "user_name": "johndoe"
        },
        "message": "John Doe started following you"
      },
      "read_at": null,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "unread_count": 5
}
```

### Mark Notification as Read

**API Route:** `PUT /api/notifications/{id}`

```php
Route::put('/notifications/{id}', [NotificationController::class, 'update']);
```

**Request:**

```json
{
  "read": true
}
```

### Mark All Notifications as Read

**API Route:** `POST /api/notifications/mark-all-read`

```php
Route::post('/notifications/mark-all-read', [NotificationController::class, 'store']);
```

### Get Unread Count

**API Route:** `GET /api/notifications/unread-count`

```php
Route::get('/notifications/unread-count', [NotificationController::class, 'show']);
```

**Response:**

```json
{
  "unread_count": 5
}
```

### Notification Settings

Users can control which notifications they receive:

```php
$user->notification_settings = [
    'email_on_follow' => true,
    'email_on_like' => false,
    'email_on_comment' => true,
    'push_on_follow' => true,
    'push_on_like' => true,
    'push_on_comment' => true,
];

$user->save();
```

**Update Settings Route:** `POST /settings/notifications`

```php
use App\Actions\Settings\UpdateNotificationSettingsAction;

app(UpdateNotificationSettingsAction::class)->handle($user, [
    'email_on_follow' => true,
    'email_on_like' => false,
    'email_on_comment' => true,
]);
```

## User Discovery

### Find Hunters (Users)

**Route:** `GET /finder`

```php
use App\Http\Controllers\FinderController;

Route::get('/finder', [FinderController::class, 'index']);
```

Displays a curated list of developers to follow, filtered by:
- Skills
- Location
- Activity level
- Not already following

**Response includes:**
- User profile information
- Skills
- Followers/following counts
- Recent activity

### Search Users

**Route:** `GET /search`

```php
use App\Http\Controllers\SearchController;

Route::get('/search', [SearchController::class, 'index']);
```

**Query Parameters:**
- `q`: Search query
- `type`: Filter type (users, hunts)
- `skills`: Filter by skills
- `location`: Filter by location

**Example:**

```bash
GET /search?q=laravel&type=users&skills[]=Laravel&skills[]=React
```

**Response:**

```json
{
  "users": [
    {
      "id": 1,
      "name": "John Doe",
      "user_name": "johndoe",
      "bio": "Full-stack Laravel developer",
      "skills": ["Laravel", "React", "PostgreSQL"],
      "avatar_url": "https://...",
      "is_following": false
    }
  ],
  "hunts": [
    {
      "id": 1,
      "content": "Just deployed my Laravel app!",
      "owner": {...}
    }
  ]
}
```

## Activity Feed

### Personal Feed

Shows hunts from followed users:

```php
use App\Models\Hunt;

$feed = Hunt::whereIn('owner_id', function ($query) {
        $query->select('followable_id')
            ->from('followables')
            ->where('follower_id', auth()->id())
            ->where('followable_type', User::class);
    })
    ->orWhere('owner_id', auth()->id())
    ->with(['owner', 'media'])
    ->withCount(['likes', 'comments'])
    ->latest()
    ->paginate(20);
```

### Global Feed (Explore)

Shows all public hunts:

```php
$exploreFeed = Hunt::with(['owner', 'media'])
    ->withCount(['likes', 'comments'])
    ->latest()
    ->paginate(20);
```

## Social Stats

### User Statistics

```php
$user = User::withCount([
    'followers',
    'followings',
    'hunts',
    'comments',
])->find($id);

$stats = [
    'followers_count' => $user->followers_count,
    'following_count' => $user->followings_count,
    'hunts_count' => $user->hunts_count,
    'comments_count' => $user->comments_count,
];
```

### Hunt Statistics

```php
$hunt = Hunt::withCount(['likes', 'comments'])
    ->find($id);

$stats = [
    'likes_count' => $hunt->likes_count,
    'comments_count' => $hunt->comments_count,
    'views_count' => $hunt->views_count ?? 0,
];
```

## Testing

### Feature Tests

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Hunt;

class SocialFeaturesTest extends TestCase
{
    public function test_user_can_follow_another_user(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->post("/follow/{$targetUser->id}");

        $this->assertTrue($user->isFollowing($targetUser));
    }

    public function test_user_can_like_hunt(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create();

        $response = $this->actingAs($user)
            ->post("/hunts/{$hunt->id}/like");

        $this->assertTrue($user->hasLiked($hunt));
    }

    public function test_user_can_comment_on_hunt(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create();

        $response = $this->actingAs($user)
            ->post("/hunts/{$hunt->id}/comments", [
                'content' => 'Great post!',
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_id' => $hunt->id,
            'content' => 'Great post!',
        ]);
    }

    public function test_user_receives_notification_when_followed(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();

        $follower->follow($user);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => 'App\Notifications\UserFollowedNotification',
        ]);
    }
}
```

## Frontend Integration

### React Example

```tsx
import { router } from '@inertiajs/react';

function FollowButton({ user, isFollowing }) {
  const handleFollow = () => {
    const endpoint = isFollowing ? `/unfollow/${user.id}` : `/follow/${user.id}`;
    
    router.post(endpoint, {}, {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Follow status updated');
      },
    });
  };

  return (
    <button onClick={handleFollow}>
      {isFollowing ? 'Unfollow' : 'Follow'}
    </button>
  );
}

function LikeButton({ hunt }) {
  const handleLike = () => {
    router.post(`/hunts/${hunt.id}/like`, {}, {
      preserveScroll: true,
    });
  };

  return (
    <button onClick={handleLike}>
      {hunt.has_liked ? '❤️' : '🤍'} {hunt.likes_count}
    </button>
  );
}
```

## Best Practices

1. **Rate limiting** - Prevent spam follows/likes
2. **Eager loading** - Always load relationships to prevent N+1
3. **Caching** - Cache follower/following counts
4. **Queue notifications** - Send notifications asynchronously
5. **Privacy settings** - Allow users to control their visibility
6. **Block/mute features** - Consider adding these for user safety
7. **Activity validation** - Verify users can't like/follow themselves
