# Quick Reference Guide

## Common Commands

### Development

```bash
# Start development server
php artisan serve
npm run dev

# Start Reverb (WebSockets)
php artisan reverb:start

# Start queue worker
php artisan queue:work

# Start Meilisearch
meilisearch --master-key=your_key
```

### Database

```bash
# Run migrations
php artisan migrate

# Fresh migration with seed
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name

# Create model with factory
php artisan make:model ModelName -mf
```

### Search

```bash
# Import models to search index
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Hunt"

# Flush search index
php artisan scout:flush "App\Models\User"
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/HuntTest.php

# Run with filter
php artisan test --filter=testUserCanCreateHunt

# Run with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Format code with Pint
vendor/bin/pint

# Run static analysis
vendor/bin/phpstan analyse

# Check code style (without fixing)
vendor/bin/pint --test
```

## Key Models

### User
```php
$user = User::find(1);
$user->name;                    // Full name
$user->user_name;               // Username
$user->skills;                  // Array of skills
$user->followers;               // Followers relationship
$user->followings;              // Following relationship
$user->hunts;                   // User's hunts
```

### Hunt
```php
$hunt = Hunt::find(1);
$hunt->content;                 // Hunt content
$hunt->owner;                   // Owner relationship
$hunt->likes;                   // Likes relationship
$hunt->comments;                // Comments relationship
$hunt->like();                  // Like hunt
$hunt->unlike();                // Unlike hunt
```

### Conversation
```php
$conversation = Conversation::find(1);
$conversation->participants;    // Users in conversation
$conversation->messages;        // All messages
```

## Common Routes

### Web Routes
| Route | Method | Description |
|-------|--------|-------------|
| `/` | GET | Home/Feed |
| `/login` | GET/POST | Login page |
| `/register` | GET/POST | Registration |
| `/{username}` | GET | Public profile |
| `/hunts` | GET | All hunts |
| `/hunts` | POST | Create hunt |
| `/hunts/{id}` | DELETE | Delete hunt |
| `/follow/{user}` | POST | Follow user |
| `/search` | GET | Search page |
| `/settings/profile` | GET/POST | Profile settings |

### API Routes
| Route | Method | Description |
|-------|--------|-------------|
| `/api/user` | GET | Get authenticated user |
| `/api/hunts` | GET | List hunts |
| `/api/hunts` | POST | Create hunt |
| `/api/conversations` | GET | List conversations |
| `/api/messages` | POST | Send message |
| `/api/notifications` | GET | List notifications |

## Configuration Files

### Environment Variables
```env
# Application
APP_URL=https://devhunter.test
APP_ENV=local

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=devhunter
DB_USERNAME=postgres
DB_PASSWORD=password

# Search (Meilisearch)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=masterKey

# Broadcasting (Reverb)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=app_id
REVERB_APP_KEY=app_key
REVERB_APP_SECRET=app_secret
REVERB_HOST=localhost
REVERB_PORT=8080

# OAuth
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_secret

GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_secret
```

## Useful Packages

### Custom Packages
- `akira/laravel-followable` - Following system
- `akira/laravel-likeable` - Like functionality
- `akira/laravel-commentable` - Comments
- `akira/laravel-auth-logs` - Authentication logs

### Laravel Packages
- `laravel/scout` - Full-text search
- `laravel/sanctum` - API authentication
- `laravel/socialite` - OAuth providers
- `laravel/reverb` - WebSocket server
- `spatie/laravel-medialibrary` - Media management

## Quick Actions

### Follow/Unfollow
```php
$user->follow($targetUser);
$user->unfollow($targetUser);
$user->isFollowing($targetUser);  // bool
```

### Like/Unlike
```php
$user->like($hunt);
$user->unlike($hunt);
$user->hasLiked($hunt);  // bool
```

### Comment
```php
$hunt->comment('Great post!');
$user->comment($hunt, 'Awesome!');
```

### Search
```php
User::search('laravel developer')->get();
Hunt::search('deployment')->get();
```

## Frontend Helpers

### Inertia Navigation
```tsx
import { router } from '@inertiajs/react';

// Simple visit
router.visit('/profile');

// With options
router.post('/hunts', { content: 'Hello' }, {
  preserveScroll: true,
  onSuccess: () => console.log('Posted!'),
});
```

### Using Auth
```tsx
import { usePage } from '@inertiajs/react';

const { auth } = usePage().props;
if (auth.user) {
  // User is authenticated
}
```

### Echo (WebSockets)
```tsx
window.Echo.private(`conversation.${id}`)
  .listen('.message.sent', (event) => {
    console.log('New message:', event.message);
  });
```

## Database Queries

### Efficient Queries
```php
// Eager load relationships
$hunts = Hunt::with(['owner', 'media'])
    ->withCount(['likes', 'comments'])
    ->latest()
    ->paginate(20);

// Avoid N+1 queries
$users = User::with('hunts')->get();
foreach ($users as $user) {
    echo $user->hunts->count(); // No extra query
}
```

### Scopes (if defined)
```php
Hunt::pinned()->get();              // Get pinned hunts
User::verified()->get();            // Get verified users
```

## Troubleshooting

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Rebuild Frontend
```bash
rm -rf node_modules
npm install
npm run build
```

### Reset Database
```bash
php artisan migrate:fresh --seed
```

### Search Not Working
```bash
# Reimport search indexes
php artisan scout:flush "App\Models\User"
php artisan scout:import "App\Models\User"
php artisan scout:flush "App\Models\Hunt"
php artisan scout:import "App\Models\Hunt"
```

### WebSocket Issues
```bash
# Restart Reverb
php artisan reverb:restart

# Check if Reverb is running
ps aux | grep reverb
```

## Performance Tips

1. **Use eager loading** - Prevent N+1 queries
2. **Cache frequently accessed data**
3. **Index database columns** used in WHERE clauses
4. **Paginate large datasets**
5. **Queue heavy operations**
6. **Optimize images** before upload
7. **Use CDN** for static assets
8. **Enable OPcache** in production
9. **Minimize database queries** in loops
10. **Profile slow queries** with Telescope

## Security Checklist

- ✅ Use HTTPS in production
- ✅ Keep dependencies updated
- ✅ Validate all user inputs
- ✅ Use CSRF protection
- ✅ Implement rate limiting
- ✅ Hash passwords with bcrypt
- ✅ Use prepared statements (Eloquent does this)
- ✅ Sanitize user content
- ✅ Check authorization with policies
- ✅ Log security events

## Resources

- **Laravel Docs**: https://laravel.com/docs
- **Inertia.js Docs**: https://inertiajs.com
- **React Docs**: https://react.dev
- **Tailwind CSS**: https://tailwindcss.com
- **Pest PHP**: https://pestphp.com
- **Meilisearch**: https://www.meilisearch.com

---

For detailed information, see the full documentation in the [docs folder](./README.md).
