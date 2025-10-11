# Search & Discovery

## Overview

Hunter implements a powerful search system using Laravel Scout with Meilisearch, allowing users to discover developers, projects (hunts), and content across the platform.

## Search Configuration

### Meilisearch Setup

Install and configure Meilisearch:

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=your_master_key
```

Start Meilisearch:

```bash
# Using Docker
docker run -d -p 7700:7700 getmeili/meilisearch:latest

# Or locally
meilisearch --master-key=your_master_key
```

### Import Searchable Models

```bash
# Import users to search index
php artisan scout:import "App\Models\User"

# Import hunts to search index
php artisan scout:import "App\Models\Hunt"

# Flush and reimport (when changing searchable attributes)
php artisan scout:flush "App\Models\User"
php artisan scout:import "App\Models\User"
```

## Searchable Models

### User Search

Users are searchable by name, username, email, location, and skills:

```php
use App\Models\User;

// Search configuration in User model
public function toSearchableArray(): array
{
    return [
        'id' => (string) $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'location' => $this->location,
        'user_name' => $this->user_name,
        'skills' => $this->skills, // Array of skills
    ];
}
```

**Search Users:**

```php
// Basic search
$users = User::search('laravel developer')->get();

// Search with filters
$users = User::search('john')
    ->where('location', 'Cape Verde')
    ->get();

// Paginated results
$users = User::search('react')
    ->paginate(20);

// With query builder methods
$users = User::search('developer')
    ->query(fn ($builder) => $builder->where('email_verified_at', '!=', null))
    ->get();
```

### Hunt Search

Hunts are searchable by content and owner information:

```php
use App\Models\Hunt;

// Search configuration in Hunt model
public function toSearchableArray(): array
{
    return [
        'id' => $this->id,
        'content' => $this->content,
        'owner_id' => $this->owner_id,
        'owner_name' => $this->owner->name,
        'owner_username' => $this->owner->user_name,
        'created_at' => $this->created_at->timestamp,
    ];
}
```

**Search Hunts:**

```php
// Basic search
$hunts = Hunt::search('laravel tutorial')->get();

// Search with filters
$hunts = Hunt::search('react hooks')
    ->where('owner_id', $userId)
    ->get();

// Recent hunts matching query
$hunts = Hunt::search('deployment')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();
```

## Search Controller

### Main Search Route

**Route:** `GET /search`

```php
use App\Http\Controllers\SearchController;

Route::get('/search', [SearchController::class, 'index'])
    ->name('search');
```

**Implementation:**

```php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hunt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all'); // all, users, hunts
        
        $results = [
            'users' => [],
            'hunts' => [],
        ];
        
        if ($query) {
            if ($type === 'all' || $type === 'users') {
                $results['users'] = User::search($query)
                    ->take(10)
                    ->get();
            }
            
            if ($type === 'all' || $type === 'hunts') {
                $results['hunts'] = Hunt::search($query)
                    ->with('owner')
                    ->take(20)
                    ->get();
            }
        }
        
        return Inertia::render('search', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
        ]);
    }
}
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search query |
| `type` | string | Filter type: all, users, hunts |
| `skills` | array | Filter by skills (users only) |
| `location` | string | Filter by location (users only) |
| `page` | int | Page number for pagination |

**Example Requests:**

```bash
# Search everything
GET /search?q=laravel

# Search only users
GET /search?q=john&type=users

# Search only hunts
GET /search?q=deployment&type=hunts

# Search users by skills
GET /search?q=developer&type=users&skills[]=Laravel&skills[]=React

# Search with location filter
GET /search?q=developer&location=Cape Verde
```

**Response:**

```json
{
  "query": "laravel",
  "type": "all",
  "results": {
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "user_name": "johndoe",
        "bio": "Full-stack Laravel developer",
        "skills": ["Laravel", "PostgreSQL"],
        "avatar_url": "https://...",
        "location": "Cape Verde"
      }
    ],
    "hunts": [
      {
        "id": 1,
        "content": "Just deployed my Laravel app to production!",
        "created_at": "2024-01-15T10:30:00Z",
        "owner": {
          "id": 1,
          "name": "John Doe",
          "user_name": "johndoe",
          "avatar_url": "https://..."
        },
        "likes_count": 42,
        "comments_count": 8
      }
    ]
  }
}
```

## Advanced Search

### Search with Multiple Filters

```php
use App\Services\Search\UserSearchService;

class UserSearchService
{
    public function search(array $filters)
    {
        $query = User::search($filters['query'] ?? '');
        
        // Filter by skills
        if (isset($filters['skills']) && !empty($filters['skills'])) {
            $query->where('skills', 'IN', $filters['skills']);
        }
        
        // Filter by location
        if (isset($filters['location'])) {
            $query->where('location', $filters['location']);
        }
        
        // Exclude specific users
        if (isset($filters['exclude'])) {
            $query->whereNotIn('id', $filters['exclude']);
        }
        
        return $query->paginate($filters['per_page'] ?? 20);
    }
}
```

### Fuzzy Search

Meilisearch provides fuzzy search by default:

```php
// These all match "developer"
User::search('developr')->get();  // Typo
User::search('devloper')->get();  // Typo
User::search('develop')->get();   // Partial
```

### Search Ranking

Configure ranking rules in Meilisearch:

```php
use MeiliSearch\Client;

$client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

// Configure user index
$client->index('users')->updateRankingRules([
    'words',
    'typo',
    'proximity',
    'attribute',
    'sort',
    'exactness',
    'followers:desc', // Custom ranking by followers
]);

// Configure hunt index
$client->index('hunts')->updateRankingRules([
    'words',
    'typo',
    'proximity',
    'attribute',
    'sort',
    'exactness',
    'created_at:desc', // Prefer recent hunts
    'likes:desc', // Prefer popular hunts
]);
```

## User Discovery

### Finder Page

Discover new developers to follow:

**Route:** `GET /finder`

```php
use App\Http\Controllers\FinderController;

Route::get('/finder', [FinderController::class, 'index'])
    ->middleware('auth')
    ->name('finder');
```

**Implementation:**

```php
class FinderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get IDs of users already following
        $followingIds = $user->followings()->pluck('followable_id')->toArray();
        
        // Find users with similar skills
        $recommendations = User::query()
            ->whereNotIn('id', array_merge($followingIds, [$user->id]))
            ->where(function ($query) use ($user) {
                // Users with overlapping skills
                foreach ($user->skills ?? [] as $skill) {
                    $query->orWhereJsonContains('skills', $skill);
                }
            })
            ->withCount(['followers', 'hunts'])
            ->orderBy('followers_count', 'desc')
            ->take(20)
            ->get();
        
        return Inertia::render('finder', [
            'recommendations' => $recommendations,
        ]);
    }
}
```

### Search Suggestions

Provide autocomplete suggestions:

```php
public function suggestions(Request $request)
{
    $query = $request->input('q');
    
    if (strlen($query) < 2) {
        return response()->json([]);
    }
    
    // Get user suggestions
    $userSuggestions = User::search($query)
        ->take(5)
        ->get()
        ->map(fn($user) => [
            'type' => 'user',
            'id' => $user->id,
            'name' => $user->name,
            'user_name' => $user->user_name,
            'avatar_url' => $user->avatar_url,
        ]);
    
    // Get popular search terms
    $popularTerms = $this->getPopularSearchTerms($query);
    
    return response()->json([
        'users' => $userSuggestions,
        'terms' => $popularTerms,
    ]);
}
```

## Skill-based Search

### Search by Specific Skills

```php
use App\Enums\SkillsEnum;

// Get all developers with Laravel skill
$laravelDevelopers = User::search('')
    ->where('skills', 'CONTAINS', 'Laravel')
    ->get();

// Get developers with multiple skills
$fullstackDevelopers = User::search('')
    ->where('skills', 'CONTAINS', 'Laravel')
    ->where('skills', 'CONTAINS', 'React')
    ->get();
```

### Popular Skills

```php
public function popularSkills()
{
    // Get most common skills from all users
    $skills = User::whereNotNull('skills')
        ->get()
        ->pluck('skills')
        ->flatten()
        ->countBy()
        ->sortDesc()
        ->take(20);
    
    return response()->json($skills);
}
```

## Search Analytics

### Track Search Queries

```php
use Illuminate\Support\Facades\DB;

class SearchAnalytics
{
    public function logSearch(string $query, string $type, int $resultsCount)
    {
        DB::table('search_logs')->insert([
            'user_id' => auth()->id(),
            'query' => $query,
            'type' => $type,
            'results_count' => $resultsCount,
            'created_at' => now(),
        ]);
    }
    
    public function popularSearches(int $limit = 10)
    {
        return DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>', now()->subDays(30))
            ->groupBy('query')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

## Frontend Integration

### React Search Component

```tsx
import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import debounce from 'lodash/debounce';

function SearchBar() {
  const [query, setQuery] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [isLoading, setIsLoading] = useState(false);

  const fetchSuggestions = debounce(async (searchQuery) => {
    if (searchQuery.length < 2) {
      setSuggestions([]);
      return;
    }

    setIsLoading(true);
    try {
      const response = await fetch(`/api/search/suggestions?q=${searchQuery}`);
      const data = await response.json();
      setSuggestions(data.users);
    } catch (error) {
      console.error('Search failed:', error);
    } finally {
      setIsLoading(false);
    }
  }, 300);

  useEffect(() => {
    fetchSuggestions(query);
  }, [query]);

  const handleSearch = (e) => {
    e.preventDefault();
    router.visit(`/search?q=${encodeURIComponent(query)}`);
  };

  return (
    <form onSubmit={handleSearch}>
      <input
        type="text"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Search developers, hunts..."
      />
      
      {suggestions.length > 0 && (
        <div className="suggestions">
          {suggestions.map((user) => (
            <a key={user.id} href={`/${user.user_name}`}>
              <img src={user.avatar_url} alt={user.name} />
              <span>{user.name}</span>
              <small>@{user.user_name}</small>
            </a>
          ))}
        </div>
      )}
    </form>
  );
}
```

### Search Results Page

```tsx
function SearchResults({ query, type, results }) {
  return (
    <div>
      <h1>Search Results for "{query}"</h1>
      
      <div className="filters">
        <a href={`/search?q=${query}&type=all`}>All</a>
        <a href={`/search?q=${query}&type=users`}>Users</a>
        <a href={`/search?q=${query}&type=hunts`}>Hunts</a>
      </div>
      
      {results.users.length > 0 && (
        <section>
          <h2>Developers</h2>
          {results.users.map((user) => (
            <UserCard key={user.id} user={user} />
          ))}
        </section>
      )}
      
      {results.hunts.length > 0 && (
        <section>
          <h2>Hunts</h2>
          {results.hunts.map((hunt) => (
            <HuntCard key={hunt.id} hunt={hunt} />
          ))}
        </section>
      )}
    </div>
  );
}
```

## Testing

### Feature Tests

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Hunt;

class SearchTest extends TestCase
{
    public function test_users_are_searchable(): void
    {
        User::factory()->create([
            'name' => 'John Laravel Developer',
            'skills' => ['Laravel', 'PHP'],
        ]);

        $results = User::search('laravel')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Laravel Developer', $results->first()->name);
    }

    public function test_hunts_are_searchable(): void
    {
        $hunt = Hunt::factory()->create([
            'content' => 'Just deployed my React application',
        ]);

        $results = Hunt::search('react')->get();

        $this->assertTrue($results->contains($hunt));
    }

    public function test_search_page_returns_results(): void
    {
        User::factory()->create(['name' => 'Test User']);

        $response = $this->get('/search?q=test&type=users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('results.users')
        );
    }
}
```

## Performance Optimization

### Caching Search Results

```php
use Illuminate\Support\Facades\Cache;

public function cachedSearch(string $query, string $type)
{
    $cacheKey = "search:{$type}:{$query}";
    
    return Cache::remember($cacheKey, 300, function () use ($query, $type) {
        if ($type === 'users') {
            return User::search($query)->take(20)->get();
        }
        
        return Hunt::search($query)->take(20)->get();
    });
}
```

### Indexing Strategy

```bash
# Schedule regular reindexing
# In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Reindex users nightly
    $schedule->command('scout:import "App\Models\User"')
        ->daily();
        
    // Reindex hunts every 6 hours
    $schedule->command('scout:import "App\Models\Hunt"')
        ->everySixHours();
}
```

## Best Practices

1. **Debounce search inputs** - Reduce API calls
2. **Cache popular searches** - Improve response times
3. **Limit results** - Don't return too many items at once
4. **Index selectively** - Only include searchable fields
5. **Monitor search performance** - Track slow queries
6. **Update indexes** - Keep search data fresh
7. **Handle typos** - Meilisearch does this automatically
8. **Provide filters** - Help users narrow results
9. **Log analytics** - Understand what users search for
10. **Optimize ranking** - Prioritize relevant results
