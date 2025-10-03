# API Reference

## Overview

Hunter provides a RESTful API for programmatic access to the platform. The API supports both web session authentication and token-based authentication via Laravel Sanctum.

## Base URL

```
Development: http://localhost:8000/api
Production: https://devhunter.test/api
```

## Authentication

### Web Authentication

For web-based requests using session cookies:

```bash
# Login first to establish session
POST /login
```

### Token Authentication (Sanctum)

For API clients and mobile applications:

**1. Create a token:**

```php
$token = $user->createToken('token-name')->plainTextToken;
```

**2. Use the token in requests:**

```bash
curl -H "Authorization: Bearer {token}" \
  https://devhunter.test/api/user
```

**3. Example token request:**

```bash
POST /api/tokens
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password",
  "device_name": "iPhone 14"
}
```

## Response Format

All API responses follow a consistent format:

**Success Response:**

```json
{
  "data": {
    // Response data
  },
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

**Error Response:**

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Rate Limiting

API endpoints are rate limited:

- **Authenticated requests:** 60 requests per minute
- **Unauthenticated requests:** 20 requests per minute

Rate limit headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 60
```

## Endpoints

### Authentication

#### Get Authenticated User

```bash
GET /api/user
Authorization: Bearer {token}
```

**Response:**

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "user_name": "johndoe",
  "avatar_url": "https://...",
  "bio": "Full-stack developer",
  "location": "Cape Verde",
  "skills": ["Laravel", "React"]
}
```

### Users

#### Search Users

```bash
GET /api/users/search?q=laravel
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
      "bio": "Laravel developer",
      "skills": ["Laravel", "PHP"],
      "followers_count": 150
    }
  ]
}
```

#### Get User Profile

```bash
GET /api/users/{username}
```

**Response:**

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "user_name": "johndoe",
    "email": "john@example.com",
    "bio": "Full-stack developer",
    "location": "Cape Verde",
    "skills": ["Laravel", "React", "PostgreSQL"],
    "avatar_url": "https://...",
    "background_image_url": "https://...",
    "github_url": "https://github.com/johndoe",
    "twitter_url": "https://twitter.com/johndoe",
    "linkedin_url": "https://linkedin.com/in/johndoe",
    "website_url": "https://johndoe.dev",
    "followers_count": 150,
    "following_count": 89,
    "hunts_count": 42,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Hunts

#### List Hunts

```bash
GET /api/hunts?page=1&per_page=20
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 50)
- `user_id`: Filter by user

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
      "media": []
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

#### Create Hunt

```bash
POST /api/hunts
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "Just deployed my first Laravel app!"
}
```

**With Images:**

```bash
POST /api/hunts
Authorization: Bearer {token}
Content-Type: multipart/form-data

content=Just deployed my app!
images[]=@screenshot1.png
images[]=@screenshot2.png
```

**Response:**

```json
{
  "data": {
    "id": 1,
    "content": "Just deployed my app!",
    "created_at": "2024-01-15T10:30:00Z",
    "owner": {...},
    "likes_count": 0,
    "comments_count": 0
  }
}
```

#### Get Single Hunt

```bash
GET /api/hunts/{id}
```

#### Update Hunt

```bash
PUT /api/hunts/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "Updated content"
}
```

#### Delete Hunt

```bash
DELETE /api/hunts/{id}
Authorization: Bearer {token}
```

#### Like/Unlike Hunt

```bash
POST /api/hunts/{id}/like
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

### Comments

#### Get Hunt Comments

```bash
GET /api/hunts/{id}/comments
```

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
  ]
}
```

#### Create Comment

```bash
POST /api/hunts/{id}/comments
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "Great post! Thanks for sharing."
}
```

#### Delete Comment

```bash
DELETE /api/comments/{id}
Authorization: Bearer {token}
```

#### Like/Unlike Comment

```bash
POST /api/comments/{id}/like
Authorization: Bearer {token}
```

### Social

#### Follow User

```bash
POST /api/follow/{user_id}
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

#### Unfollow User

```bash
POST /api/unfollow/{user_id}
Authorization: Bearer {token}
```

#### Get Followers

```bash
GET /api/users/{user_id}/followers?page=1
```

#### Get Following

```bash
GET /api/users/{user_id}/following?page=1
```

#### Get Followed Hunters

```bash
GET /api/followed-hunters
Authorization: Bearer {token}
```

**Response:**

```json
{
  "data": [
    {
      "id": 2,
      "name": "Jane Smith",
      "user_name": "janesmith",
      "avatar_url": "https://...",
      "is_following": true,
      "followers_count": 200
    }
  ]
}
```

### Conversations

#### List Conversations

```bash
GET /api/conversations
Authorization: Bearer {token}
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "participants": [
        {
          "id": 2,
          "name": "Jane Smith",
          "avatar_url": "https://...",
          "is_online": true
        }
      ],
      "last_message": {
        "content": "Hello!",
        "created_at": "2024-01-15T10:30:00Z"
      },
      "unread_count": 3
    }
  ]
}
```

#### Create Conversation

```bash
POST /api/conversations
Authorization: Bearer {token}
Content-Type: application/json

{
  "participant_ids": [2, 3],
  "message": "Hey, let's chat!"
}
```

#### Get Conversation Messages

```bash
GET /api/conversations/{id}/messages?page=1
Authorization: Bearer {token}
```

#### Delete Conversation

```bash
DELETE /api/conversations/{id}
Authorization: Bearer {token}
```

### Messages

#### Send Message

```bash
POST /api/messages
Authorization: Bearer {token}
Content-Type: application/json

{
  "conversation_id": 1,
  "content": "Hello! How are you?"
}
```

**Response:**

```json
{
  "data": {
    "id": 1,
    "conversation_id": 1,
    "content": "Hello! How are you?",
    "user_id": 1,
    "read_at": null,
    "created_at": "2024-01-15T10:30:00Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "avatar_url": "https://..."
    }
  }
}
```

#### Delete Message

```bash
DELETE /api/messages/{id}
Authorization: Bearer {token}
```

#### Mark Messages as Read

```bash
POST /api/conversations/{id}/messages/read
Authorization: Bearer {token}
```

#### Bulk Mark as Read

```bash
POST /api/messages/bulk-read
Authorization: Bearer {token}
Content-Type: application/json

{
  "message_ids": [1, 2, 3, 4, 5]
}
```

### Presence

#### Mark Online

```bash
POST /api/presence/online
Authorization: Bearer {token}
```

**Response:**

```json
{
  "status": "online",
  "user_id": 1
}
```

#### Mark Offline

```bash
POST /api/presence/offline
Authorization: Bearer {token}
```

#### Get Online Users

```bash
GET /api/users/online
Authorization: Bearer {token}
```

**Response:**

```json
{
  "data": [
    {
      "id": 2,
      "name": "Jane Smith",
      "user_name": "janesmith",
      "avatar_url": "https://...",
      "is_online": true
    }
  ]
}
```

### Notifications

#### List Notifications

```bash
GET /api/notifications
Authorization: Bearer {token}
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

#### Mark Notification as Read

```bash
PUT /api/notifications/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "read": true
}
```

#### Mark All as Read

```bash
POST /api/notifications/mark-all-read
Authorization: Bearer {token}
```

#### Get Unread Count

```bash
GET /api/notifications/unread-count
Authorization: Bearer {token}
```

**Response:**

```json
{
  "unread_count": 5
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Internal Server Error |

## Pagination

All list endpoints support pagination:

**Request:**
```bash
GET /api/hunts?page=2&per_page=25
```

**Response:**
```json
{
  "data": [...],
  "meta": {
    "current_page": 2,
    "last_page": 10,
    "per_page": 25,
    "total": 250,
    "from": 26,
    "to": 50
  },
  "links": {
    "first": "https://api.example.com/hunts?page=1",
    "last": "https://api.example.com/hunts?page=10",
    "prev": "https://api.example.com/hunts?page=1",
    "next": "https://api.example.com/hunts?page=3"
  }
}
```

## Filtering & Sorting

### Filtering

```bash
# Filter hunts by user
GET /api/hunts?user_id=1

# Filter by date
GET /api/hunts?from=2024-01-01&to=2024-01-31
```

### Sorting

```bash
# Sort by field
GET /api/hunts?sort=created_at&order=desc

# Multiple sorts
GET /api/hunts?sort[]=likes_count,desc&sort[]=created_at,desc
```

## WebSocket Events

Subscribe to real-time events via Laravel Echo:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Listen for messages
Echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (event) => {
        console.log('New message:', event.message);
    });

// Listen for presence
Echo.join(`user.${userId}`)
    .here((users) => {
        console.log('Online users:', users);
    })
    .joining((user) => {
        console.log('User joined:', user);
    })
    .leaving((user) => {
        console.log('User left:', user);
    });
```

## Best Practices

1. **Use HTTPS in production**
2. **Store tokens securely** - Never expose in client-side code
3. **Implement retry logic** - Handle rate limits gracefully
4. **Cache responses** - Reduce API calls when possible
5. **Use pagination** - Don't fetch all data at once
6. **Handle errors** - Check response status codes
7. **Set timeouts** - Don't let requests hang indefinitely
8. **Validate input** - Always validate on client side
9. **Monitor usage** - Track API consumption
10. **Version your API** - Plan for future changes

## SDK Examples

### JavaScript/TypeScript

```typescript
class DevHunterAPI {
  private token: string;
  private baseURL: string = 'https://devhunter.test/api';

  constructor(token: string) {
    this.token = token;
  }

  private async request(endpoint: string, options: RequestInit = {}) {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      ...options,
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers,
      },
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.statusText}`);
    }

    return response.json();
  }

  async getUser() {
    return this.request('/user');
  }

  async createHunt(content: string) {
    return this.request('/hunts', {
      method: 'POST',
      body: JSON.stringify({ content }),
    });
  }

  async likeHunt(huntId: number) {
    return this.request(`/hunts/${huntId}/like`, {
      method: 'POST',
    });
  }
}

// Usage
const api = new DevHunterAPI('your-token-here');
const user = await api.getUser();
```

### PHP

```php
use Illuminate\Support\Facades\Http;

class DevHunterClient
{
    private string $token;
    private string $baseUrl = 'https://devhunter.test/api';

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    private function request(string $method, string $endpoint, array $data = [])
    {
        $response = Http::withToken($this->token)
            ->$method("{$this->baseUrl}{$endpoint}", $data);

        return $response->json();
    }

    public function getUser()
    {
        return $this->request('get', '/user');
    }

    public function createHunt(string $content)
    {
        return $this->request('post', '/hunts', [
            'content' => $content,
        ]);
    }
}

// Usage
$client = new DevHunterClient('your-token-here');
$user = $client->getUser();
```
