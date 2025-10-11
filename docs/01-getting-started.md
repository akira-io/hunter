# Getting Started

## Introduction

**Hunter** is a Cape Verdean network designed for creators, thinkers, and builders. It's a platform where developers can showcase their work, share their journey, and connect with like-minded professionals.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.4** or higher
- **Composer** 2.x
- **Node.js** 18.x or higher
- **npm** or **pnpm**
- **PostgreSQL** 14+ (production database)
- **Redis** (for queues and cache)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/akira-io/devhunter.git
cd devhunter
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit your `.env` file with PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=devhunter
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Configure Search (Meilisearch)

Hunter uses Meilisearch for full-text search:

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=your_master_key
```

Install and run Meilisearch:

```bash
# Using Docker
docker run -d -p 7700:7700 getmeili/meilisearch:latest

# Or install locally via homebrew
brew install meilisearch
meilisearch
```

Import searchable models:

```bash
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Hunt"
```

### 7. Configure Broadcasting (Laravel Reverb)

For real-time features like chat and notifications:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

Start Reverb server:

```bash
php artisan reverb:start
```

### 8. Configure Queue Worker

```bash
# In a separate terminal
php artisan queue:work
```

### 9. Start Development Server

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite dev server
npm run dev
```

Visit `http://localhost:8000` in your browser.

## Using Laravel Herd

If you're using [Laravel Herd](https://herd.laravel.com/):

1. Link the project directory
2. Access at `https://devhunter.test`
3. No need to run `php artisan serve`

## Using Docker

Use Laravel Sail for a containerized environment:

```bash
# Install Sail
composer require laravel/sail --dev

# Start containers
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate
```

## OAuth Configuration

### GitHub OAuth

1. Create a GitHub OAuth App at https://github.com/settings/developers
2. Set callback URL: `http://localhost:8000/auth/github/callback`
3. Add credentials to `.env`:

```env
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"
```

### Google OAuth

1. Create OAuth credentials in Google Cloud Console
2. Set callback URL: `http://localhost:8000/auth/google/callback`
3. Add credentials to `.env`:

```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

## Media Storage

Configure media storage for avatars and hunt images:

```env
# Local storage (development)
FILESYSTEM_DISK=public

# AWS S3 (production)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
```

Run the storage link command:

```bash
php artisan storage:link
```

## Seeding Data

Seed the database with test data:

```bash
php artisan db:seed
```

## Verification

Verify your installation:

```bash
# Check application health
php artisan about

# Run tests
php artisan test
```

## Common Issues

### Port Already in Use

If port 8000 is occupied:

```bash
php artisan serve --port=8001
```

### Permission Issues

```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### Node Modules Issues

```bash
rm -rf node_modules package-lock.json
npm install
```

## Next Steps

- Read the [Authentication Guide](./02-authentication.md)
- Learn about [User Profiles](./03-user-profiles.md)
- Understand [Hunts (Posts)](./04-hunts.md)
- Explore [Social Features](./05-social-features.md)
