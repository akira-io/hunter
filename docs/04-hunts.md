# Hunts (Posts)

## Overview

**Hunts** are the core content type in Hunter - posts where developers share their thoughts, projects, code snippets, devlogs, and ideas. Think of them as tweets/posts specifically designed for the developer community.

## Hunt Model

```php
use App\Models\Hunt;

$hunt = Hunt::find(1);

// Properties
$hunt->id;              // Unique identifier
$hunt->owner_id;        // User who created it
$hunt->content;         // Post content (text)
$hunt->is_pinned;       // Whether it's pinned
$hunt->is_reported;     // Whether it's reported
$hunt->is_ignored;      // Whether it's ignored
$hunt->created_at;      // Creation timestamp
$hunt->updated_at;      // Last update

// Relationships
$hunt->owner;           // User who created it
$hunt->comments;        // Comments on the hunt
$hunt->likes;           // Likes on the hunt
$hunt->media;           // Attached images/files
```

## Creating Hunts

### Create Hunt Action

```php
use App\Actions\Hunt\CreateHuntAction;

$hunt = app(CreateHuntAction::class)->handle([
    'owner_id' => auth()->id(),
    'content' => 'Just deployed my first Laravel + React app! 🚀',
    'images' => $request->file('images'), // Optional
]);
```

### Create Hunt API

**Route:** `POST /hunts`

**Request:**

```json
{
  "content": "Just shipped a new feature using Laravel Reverb for real-time updates! The WebSocket integration was smoother than expected.",
  "images": ["file1.jpg", "file2.png"]
}
```

**With Images (multipart/form-data):**

```bash
curl -X POST https://devhunter.test/hunts \
  -H "Authorization: Bearer {token}" \
  -F "content=Check out my new project!" \
  -F "images[]=@screenshot1.png" \
  -F "images[]=@screenshot2.png"
```

**Validation Rules:**
- `content`: required, string, max 1000 characters
- `images`: optional, array, max 4 images
- `images.*`: image, max 5MB, formats: jpg, png, gif, webp

### Example Controller Implementation

```php
use App\Http\Controllers\HuntController;
use App\Actions\Hunt\CreateHuntAction;

class HuntController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'images' => 'nullable|array|max:4',
            'images.*' => 'image|max:5120',
        ]);

        $hunt = app(CreateHuntAction::class)->handle([
            'owner_id' => auth()->id(),
            'content' => $validated['content'],
            'images' => $request->file('images'),
        ]);

        return redirect()->back()
            ->with('success', 'Hunt created successfully!');
    }
}
```

## Displaying Hunts

### Get All Hunts

```php
use App\Actions\Hunt\GetHuntsAction;

// Get paginated hunts
$hunts = app(GetHuntsAction::class)->handle([
    'page' => 1,
    'per_page' => 20,
    'include_media' => true,
    'include_owner' => true,
]);
```

**Route:** `GET /hunts`

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 50)
- `user_id`: Filter by user
- `pinned`: Filter pinned hunts

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "content": "Just deployed my app!",
      "created_at": "2024-01-15T10:30:00Z",
      "owner": {
        "id": 1,
        "name": "John Doe",
        "user_name": "johndoe",
        "avatar_url": "https://..."
      },
      "likes_count": 42,
      "comments_count": 8,
      "has_liked": true,
      "is_pinned": false,
      "media": [
        {
          "id": 1,
          "url": "https://...",
          "type": "image"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200
  }
}
```

### Get Single Hunt

```php
$hunt = Hunt::with(['owner', 'comments.user', 'media'])
    ->withCount(['likes', 'comments'])
    ->findOrFail($id);
```

### Get User's Hunts

```php
$userHunts = Hunt::where('owner_id', $userId)
    ->latest()
    ->paginate(20);
```

**Route:** `GET /{username}` (includes hunts in profile)

## Updating Hunts

### Update Hunt Action

```php
use App\Actions\Hunt\UpdateHuntAction;

$hunt = app(UpdateHuntAction::class)->handle($huntId, [
    'content' => 'Updated content',
]);
```

**Route:** `PUT /hunts/{hunt}`

**Authorization:** User must own the hunt

```php
use App\Policies\HuntPolicy;

// Policy check
if (auth()->user()->can('update', $hunt)) {
    // User can update
}
```

**Request:**

```json
{
  "content": "Updated: Just deployed my first Laravel + React app! 🚀 #Laravel #React"
}
```

## Deleting Hunts

### Delete Hunt Action

```php
use App\Actions\Hunt\DeleteHuntAction;

app(DeleteHuntAction::class)->handle($huntId);
```

**Route:** `DELETE /hunts/{hunt}`

**Authorization:** User must own the hunt

```php
use App\Policies\HuntPolicy;

if (auth()->user()->can('delete', $hunt)) {
    $hunt->delete();
}
```

## Media Attachments

Hunts can have multiple images attached using Spatie Media Library:

### Upload Images

```php
// Add multiple images to a hunt
if ($request->hasFile('images')) {
    foreach ($request->file('images') as $image) {
        $hunt->addMedia($image)
            ->toMediaCollection('images');
    }
}

// Get hunt images
$images = $hunt->getMedia('images');

foreach ($images as $image) {
    $url = $image->getUrl();
    $thumbnail = $image->getUrl('thumb');
}
```

### Image Specifications

- **Formats:** JPEG, PNG, GIF, WebP
- **Max size:** 5MB per image
- **Max images:** 4 per hunt
- **Recommended dimensions:** 1200x800px
- **Thumbnails:** Auto-generated

## Hunt Interactions

### Likes

Hunts support likes using the `akira/laravel-likeable` package:

```php
use App\Http\Controllers\Likeable\ToggleHuntLikeController;

// Toggle like on a hunt
Route::post('/hunts/{hunt}/like', [ToggleHuntLikeController::class, 'store']);
```

**Like a hunt:**

```php
$hunt->like(); // Authenticated user likes

// Check if user liked
if ($hunt->hasLiked()) {
    // Current user has liked
}

// Get likes count
$likesCount = $hunt->likesCount();

// Get users who liked
$likers = $hunt->likers()->get();
```

**Unlike a hunt:**

```php
$hunt->unlike();
```

**API Route:** `POST /hunts/{hunt}/like`

### Comments

Hunts support comments using the `akira/laravel-commentable` package:

```php
// Add comment
$comment = $hunt->comment('Great post!');

// Add comment as specific user
$comment = $hunt->commentAsUser($user, 'Awesome work!');

// Get comments
$comments = $hunt->comments()->with('user')->latest()->get();

// Comments count
$commentsCount = $hunt->comments()->count();
```

**Comment Model:**

```php
use App\Models\Comment;

$comment->id;           // Comment ID
$comment->content;      // Comment text
$comment->user_id;      // Who commented
$comment->commentable;  // The hunt
$comment->created_at;   // When
```

**API Route:** `POST /hunts/{hunt}/comments`

**Request:**

```json
{
  "content": "Great post! Thanks for sharing."
}
```

**Delete Comment Route:** `DELETE /comments/{comment}`

**Authorization:** User must own the comment

### Comment Likes

Comments can also be liked:

```php
use App\Http\Controllers\Likeable\ToggleCommentLikeController;

Route::post('/comments/{comment}/like', [ToggleCommentLikeController::class, 'store']);
```

## Hunt Features

### Pinning Hunts

Users can pin their important hunts to the top of their profile:

```php
// Pin a hunt
$hunt->update(['is_pinned' => true]);

// Get pinned hunts
$pinnedHunts = Hunt::where('owner_id', $userId)
    ->where('is_pinned', true)
    ->latest()
    ->get();
```

**Limit:** Usually 1-3 pinned hunts per user

### Reporting Hunts

Users can report inappropriate content:

```php
// Report a hunt
$hunt->update(['is_reported' => true]);

// Administrators can review reported hunts
$reportedHunts = Hunt::where('is_reported', true)
    ->with('owner')
    ->latest()
    ->get();
```

### Ignoring Hunts

Users can hide hunts they don't want to see:

```php
// Ignore a hunt (hide from feed)
$hunt->update(['is_ignored' => true]);
```

## Hunt Search

Hunts are searchable via Laravel Scout:

```php
use App\Models\Hunt;

// Search hunts
$hunts = Hunt::search('laravel deployment')
    ->get();

// Search with user filter
$hunts = Hunt::search('react')
    ->where('owner_id', $userId)
    ->get();
```

**Searchable attributes:**
- `content`
- `owner_name`
- `owner_username`

**Search Route:** `GET /search?q=laravel&type=hunts`

## Hunt Feed

The main feed shows hunts from followed users:

```php
// Get feed for authenticated user
$feed = Hunt::whereIn('owner_id', auth()->user()->followings->pluck('id'))
    ->orWhere('owner_id', auth()->id())
    ->latest()
    ->with(['owner', 'media'])
    ->withCount(['likes', 'comments'])
    ->paginate(20);
```

**Feed Route:** `GET /`

**Feed Algorithm:**
1. Hunts from followed users
2. User's own hunts
3. Ordered by recency (latest first)
4. Pagination support

## Hunt Resources

API resource for transforming hunt data:

```php
use App\Http\Resources\Hunt\HuntResource;

// Single hunt
return new HuntResource($hunt);

// Collection
return HuntResource::collection($hunts);
```

**Resource output:**

```json
{
  "id": 1,
  "content": "Just deployed my app!",
  "created_at": "2024-01-15T10:30:00Z",
  "created_at_human": "2 hours ago",
  "owner": {
    "id": 1,
    "name": "John Doe",
    "user_name": "johndoe",
    "avatar_url": "https://..."
  },
  "likes_count": 42,
  "comments_count": 8,
  "has_liked": true,
  "is_pinned": false,
  "is_reported": false,
  "media": [
    {
      "id": 1,
      "url": "https://cdn.example.com/hunts/image.jpg",
      "thumbnail_url": "https://cdn.example.com/hunts/image-thumb.jpg",
      "type": "image",
      "size": 1024000,
      "mime_type": "image/jpeg"
    }
  ]
}
```

## Hunt Policy

Authorization logic in `HuntPolicy`:

```php
namespace App\Policies;

use App\Models\Hunt;
use App\Models\User;

class HuntPolicy
{
    public function update(User $user, Hunt $hunt): bool
    {
        return $user->id === $hunt->owner_id;
    }

    public function delete(User $user, Hunt $hunt): bool
    {
        return $user->id === $hunt->owner_id;
    }

    public function pin(User $user, Hunt $hunt): bool
    {
        return $user->id === $hunt->owner_id;
    }
}
```

## Testing

### Feature Tests

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Hunt;

class HuntTest extends TestCase
{
    public function test_user_can_create_hunt(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/hunts', [
                'content' => 'My first hunt!',
            ]);

        $this->assertDatabaseHas('hunts', [
            'owner_id' => $user->id,
            'content' => 'My first hunt!',
        ]);
    }

    public function test_user_can_like_hunt(): void
    {
        $hunt = Hunt::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post("/hunts/{$hunt->id}/like");

        $this->assertTrue($hunt->hasLiked($user));
    }

    public function test_user_can_delete_own_hunt(): void
    {
        $user = User::factory()->create();
        $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete("/hunts/{$hunt->id}");

        $this->assertDatabaseMissing('hunts', ['id' => $hunt->id]);
    }
}
```

## Best Practices

1. **Content moderation** - Monitor reported hunts
2. **Rate limiting** - Prevent spam (e.g., max 10 hunts/hour)
3. **Image optimization** - Compress images before upload
4. **Caching** - Cache popular hunts to reduce DB load
5. **Pagination** - Always paginate hunt lists
6. **Eager loading** - Load relationships to prevent N+1 queries
7. **Sanitization** - Clean user content to prevent XSS

## Frontend Integration

### React Component Example

```tsx
import { router } from '@inertiajs/react';

function HuntCard({ hunt }) {
  const handleLike = () => {
    router.post(`/hunts/${hunt.id}/like`);
  };

  return (
    <div className="hunt-card">
      <div className="hunt-content">{hunt.content}</div>
      <button onClick={handleLike}>
        {hunt.has_liked ? 'Unlike' : 'Like'} ({hunt.likes_count})
      </button>
    </div>
  );
}
```
