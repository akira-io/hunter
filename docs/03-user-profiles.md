# User Profiles

## Overview

User profiles in Hunter are comprehensive, showcasing developers' skills, background, projects, and social connections. Each profile is fully customizable and searchable.

## User Model

The `User` model includes:

```php
use App\Models\User;

$user = User::find(1);

// Basic info
$user->name;           // Full name
$user->email;          // Email address
$user->user_name;      // Username (unique)
$user->bio;            // Biography
$user->location;       // Location

// Skills
$user->skills;         // Array of skills

// Social links
$user->github_url;     // GitHub profile
$user->twitter_url;    // Twitter/X profile
$user->linkedin_url;   // LinkedIn profile
$user->bluesky_url;    // Bluesky profile
$user->website_url;    // Personal website
$user->youtube_url;    // YouTube channel

// OAuth data
$user->github_id;      // GitHub OAuth ID
$user->github_token;   // GitHub access token

// Profile images
$user->avatar_url;     // Profile picture
$user->background_image_url; // Cover/banner image

// Timestamps
$user->created_at;     // Account creation date
$user->onboarding_completed_at; // Onboarding completion
```

## Profile Management

### View Profile

**Public Profile Route:** `GET /{username}`

```php
use App\Http\Controllers\PublicProfileController;

// View any user's public profile
Route::get('/{username}', [PublicProfileController::class, 'show']);
```

**Response includes:**
- User information
- Skills and background
- Recent hunts (posts)
- Followers/following count
- Academic background
- Social links

### Edit Profile

**Route:** `GET /settings/profile`

```php
use App\Http\Controllers\Settings\ProfileController;

Route::get('/settings/profile', [ProfileController::class, 'edit'])
    ->middleware('auth')
    ->name('profile.edit');
```

### Update Profile

**Route:** `POST /settings/profile`

```php
use App\Http\Controllers\Settings\ProfileController;

public function update(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'user_name' => 'required|string|max:255|unique:users,user_name,' . auth()->id(),
        'email' => 'required|email|unique:users,email,' . auth()->id(),
        'bio' => 'nullable|string|max:500',
        'location' => 'nullable|string|max:255',
        'avatar' => 'nullable|image|max:2048',
        'background_image' => 'nullable|image|max:2048',
        'skills' => 'nullable|array',
    ]);
    
    $user = auth()->user();
    $user->update($validated);
    
    return back()->with('success', 'Profile updated successfully');
}
```

**Example Request:**

```json
{
  "name": "John Doe",
  "user_name": "johndoe",
  "email": "john@example.com",
  "bio": "Full-stack developer passionate about Laravel and React",
  "location": "Cape Verde",
  "skills": ["Laravel", "React", "PostgreSQL", "TypeScript"]
}
```

## Avatar Management

### Upload Avatar

Avatars are managed using Spatie Media Library:

```php
use App\Actions\User\GetAvatarAction;

// Upload avatar
if ($request->hasFile('avatar')) {
    $user->clearMediaCollection('avatar');
    $user->addMediaFromRequest('avatar')
        ->toMediaCollection('avatar');
}

// Get avatar URL
$avatarUrl = app(GetAvatarAction::class)->handle($user);
```

**Supported formats:** JPEG, PNG, GIF, WebP  
**Max size:** 2MB  
**Recommended dimensions:** 400x400px

### Background Image

```php
use App\Actions\User\GetBackgroundImageAction;

// Upload background image
if ($request->hasFile('background_image')) {
    $user->clearMediaCollection('background');
    $user->addMediaFromRequest('background_image')
        ->toMediaCollection('background');
}

// Get background URL
$bgUrl = app(GetBackgroundImageAction::class)->handle($user);
```

**Recommended dimensions:** 1200x400px

## Skills Management

### Skills Enum

Skills are defined in `SkillsEnum`:

```php
use App\Enums\SkillsEnum;

// Available skills
SkillsEnum::cases(); // Returns all available skills

// Example skills
SkillsEnum::Laravel->value;      // "Laravel"
SkillsEnum::React->value;        // "React"
SkillsEnum::PostgreSQL->value;   // "PostgreSQL"
```

### Update Skills

```php
$user->update([
    'skills' => [
        'Laravel',
        'React',
        'TypeScript',
        'PostgreSQL',
        'Tailwind CSS',
    ],
]);
```

### Highlight Skills

**Route:** `POST /profile/highlight-skill`

```php
use App\Http\Controllers\Profile\HighlightSkillController;

// Highlight top skills (reorder for prominence)
Route::post('/profile/highlight-skill', [HighlightSkillController::class, 'store']);
```

## Academic Background

Users can add their educational history:

```php
use App\Models\AcademicBackground;

// Create academic background
$background = $user->academicBackgrounds()->create([
    'institution' => 'University of Cape Verde',
    'degree' => 'Bachelor of Science',
    'field_of_study' => 'Computer Science',
    'start_date' => '2018-09-01',
    'end_date' => '2022-06-30',
    'description' => 'Focus on web development and databases',
]);

// Get user's education
$education = $user->academicBackgrounds()
    ->orderBy('start_date', 'desc')
    ->get();
```

**Routes:**

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/profile/academic-background` | GET | List education |
| `/profile/academic-background` | POST | Add education |
| `/profile/academic-background/{id}` | PUT | Update education |
| `/profile/academic-background/{id}` | DELETE | Remove education |

**Example Create Request:**

```json
{
  "institution": "University of Cape Verde",
  "degree": "Bachelor of Science",
  "field_of_study": "Computer Science",
  "start_date": "2018-09-01",
  "end_date": "2022-06-30",
  "description": "Specialized in web technologies and software engineering"
}
```

## Social Links

### Update Social Links

**Route:** `POST /profile/links`

```php
use App\Http\Controllers\Profile\LinksController;

Route::post('/profile/links', [LinksController::class, 'update']);
```

**Request:**

```json
{
  "github_url": "https://github.com/johndoe",
  "twitter_url": "https://twitter.com/johndoe",
  "linkedin_url": "https://linkedin.com/in/johndoe",
  "bluesky_url": "https://bsky.app/profile/johndoe.bsky.social",
  "website_url": "https://johndoe.dev",
  "youtube_url": "https://youtube.com/@johndoe"
}
```

**Validation:**
- URLs must be valid
- Domain must match the platform (e.g., github.com for GitHub)

## About Section

**Route:** `POST /profile/about`

```php
use App\Http\Controllers\Profile\AboutController;

Route::post('/profile/about', [AboutController::class, 'update']);
```

**Request:**

```json
{
  "bio": "Passionate full-stack developer with 5+ years of experience...",
  "location": "Praia, Cape Verde"
}
```

## Profile Search

Users are searchable via Laravel Scout (Meilisearch):

```php
use App\Models\User;

// Search users
$users = User::search('laravel developer')
    ->get();

// Search with filters
$users = User::search('john')
    ->where('location', 'Cape Verde')
    ->get();

// Get searchable attributes
$user->toSearchableArray();
// Returns: id, name, email, location, user_name, skills
```

## User Resources (API)

Transform user data for API responses:

```php
use App\Http\Resources\UserResource;

// Single user
return new UserResource($user);

// Collection
return UserResource::collection($users);
```

**Resource output:**

```json
{
  "id": 1,
  "name": "John Doe",
  "user_name": "johndoe",
  "email": "john@example.com",
  "avatar_url": "https://example.com/avatars/john.jpg",
  "bio": "Full-stack developer",
  "location": "Cape Verde",
  "skills": ["Laravel", "React", "PostgreSQL"],
  "github_url": "https://github.com/johndoe",
  "twitter_url": "https://twitter.com/johndoe",
  "followers_count": 150,
  "following_count": 89,
  "hunts_count": 42,
  "created_at": "2024-01-15T10:30:00Z"
}
```

## Profile Visibility

All profiles are public by default. Users can control content visibility through:

1. **Profile completion** - Incomplete profiles show placeholder text
2. **Hunt privacy** - Individual posts can be private (future feature)
3. **Activity settings** - Control what activities are shown

## Onboarding

New users go through an onboarding process:

```php
// Check if user completed onboarding
if ($user->onboarding_completed) {
    // User finished setup
}

// Mark onboarding as complete
$user->update([
    'onboarding_completed' => true,
    'onboarding_completed_at' => now(),
]);
```

**Onboarding steps:**
1. Basic profile information
2. Add skills
3. Upload avatar
4. Add social links (optional)
5. Add academic background (optional)

## User Actions

### Get User Profile Data

```php
use App\Actions\User\UserProfileAction;

$profileData = app(UserProfileAction::class)->handle($user);
```

Returns formatted profile data including:
- User information
- Statistics (followers, hunts, etc.)
- Recent activity
- Skills and background

### Get Avatar

```php
use App\Actions\User\GetAvatarAction;

$avatarUrl = app(GetAvatarAction::class)->handle($user);
// Returns: URL or null (falls back to default)
```

### Get Background Image

```php
use App\Actions\User\GetBackgroundImageAction;

$backgroundUrl = app(GetBackgroundImageAction::class)->handle($user);
```

## Testing

### Feature Tests

```php
use Tests\TestCase;
use App\Models\User;

class ProfileTest extends TestCase
{
    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/settings/profile', [
                'name' => 'Updated Name',
                'bio' => 'New bio',
                'location' => 'Cape Verde',
            ]);

        $response->assertRedirect();
        
        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_user_can_add_academic_background(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/profile/academic-background', [
                'institution' => 'Test University',
                'degree' => 'Bachelor',
                'field_of_study' => 'CS',
                'start_date' => '2020-01-01',
            ]);

        $this->assertDatabaseHas('academic_backgrounds', [
            'user_id' => $user->id,
            'institution' => 'Test University',
        ]);
    }
}
```

## Best Practices

1. **Keep profiles updated** - Encourage users to maintain current information
2. **Validate URLs** - Ensure social links are valid and safe
3. **Optimize images** - Compress avatars and backgrounds
4. **Use slug-based URLs** - `/username` instead of `/user/123`
5. **Cache profile data** - Reduce database queries for public profiles
6. **Verify external links** - Check that social profiles exist
