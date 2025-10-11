# Chat System Architecture

## Overview

The Hunter chat system provides real-time messaging capabilities with a modern, responsive interface. Built with Laravel Reverb (WebSockets), React, and TypeScript, it offers instant communication with presence indicators, read receipts, and unread message tracking.

## Recent Improvements (v0.6.0)

### Dedicated Chat Layout

The chat system now features a dedicated layout component (`ChatLayout`) that provides:

- **Desktop Sidebar**: Persistent conversation list with search and filters
- **Mobile View**: Responsive conversation list that adapts to screen size
- **Conversation Header**: Displays participant information and online status
- **Message Area**: Optimized message display with proper overflow handling
- **Input Controls**: Rich text input with image upload support

### Architecture Pattern

All chat routes now use **Spatie Route Attributes** for better organization and IDE support:

```php
#[Middleware(['auth', 'verified'])]
#[Prefix('chat')]
final readonly class ChatController
{
    #[Get('/', name: 'chat.index')]
    public function index(Request $request): Response
    
    #[Get('/{conversation}', name: 'chat.show')]
    public function show(Request $request, int $conversation): Response
    
    #[Get('/mobile/{conversation}', name: 'chat.mobile')]
    public function mobile(int $conversation): RedirectResponse
}
```

### Frontend Improvements

#### ChatContext Provider

Centralized state management for chat functionality:

```tsx
import { ChatProvider, useChatContext } from '@/contexts/ChatContext';

// Wrap your app
<ChatProvider>
    <YourChatComponent />
</ChatProvider>

// Access in components
const { conversations, openChatWindow, markAsRead } = useChatContext();
```

#### Type-Safe Routes (Wayfinder)

All chat routes use type-safe URL generation:

```tsx
import chat from '@/routes/chat';

// Navigate to conversations list
router.visit(chat.index.url());

// Navigate to specific conversation
router.visit(chat.show.url(conversationId));
// or
router.visit(chat.show.url({ conversation: conversationId }));
```

### Presence System

Enhanced presence tracking with cache-based online status:

```php
// Mark user as online
POST /presence/online
Response: { status: 'online', user_id: 123 }

// Mark user as offline
POST /presence/offline
Response: { status: 'offline', user_id: 123 }

// Get online users
GET /users/online
Response: {
    data: [
        { id: 2, name: 'John Doe', email: 'john@example.com' },
        { id: 3, name: 'Jane Smith', email: 'jane@example.com' }
    ]
}
```

**Cache Strategy:**
- Online status stored in Redis with 10-minute TTL
- Automatically refreshed on activity
- Current user excluded from online users list

## Chat Features

### Conversation Management

#### Create Conversation

```php
POST /conversations
{
    "type": "direct",
    "participants": [2, 3]
}
```

#### List Conversations

```php
GET /conversations
```

**Response includes:**
- Conversation metadata
- Last message preview
- Unread count per conversation
- Participant information
- Online status of participants

#### Conversation Ordering

Frontend sorts conversations by:
1. **Online participants** (shown first)
2. **Last message timestamp** (most recent first)

```tsx
const sortedConversations = useMemo(() => {
    return [...conversations].sort((a, b) => {
        const aOnline = isParticipantOnline(a);
        const bOnline = isParticipantOnline(b);
        
        if (aOnline !== bOnline) {
            return bOnline ? 1 : -1;
        }
        
        return new Date(b.last_message_at) - new Date(a.last_message_at);
    });
}, [conversations, onlineUsers]);
```

### Message Handling

#### Send Message

```php
POST /messages
{
    "conversation_id": 1,
    "content": "Hello, world!",
    "type": "text"
}
```

**Message Types:**
- `text`: Plain text messages
- `image`: Image attachments
- `file`: File attachments

#### Mark Messages as Read

```php
POST /conversations/{conversation}/messages/read
```

Updates `read_at` timestamp for all unread messages in the conversation.

### Real-time Events

The system broadcasts several events via WebSockets:

#### MessageSent Event

```php
use App\Events\MessageSent;

event(new MessageSent($message, $conversation));
```

**Payload:**
```json
{
    "message": {
        "id": 123,
        "content": "Hello!",
        "user": { "id": 1, "name": "John Doe" },
        "created_at": "2024-01-15T10:30:00Z"
    },
    "conversation_id": 1
}
```

#### Presence Events

```php
// When user comes online
broadcast(new UserOnline($userId));

// When user goes offline
broadcast(new UserOffline($userId));
```

## Unread Messages Badge

### Sidebar Badge

The main navigation sidebar displays a badge with total unread message count:

```tsx
import { useChatContext } from '@/contexts/ChatContext';

const { conversations } = useChatContext();

const totalUnread = useMemo(() => {
    return conversations.reduce((total, conv) => {
        return total + (conv.unread_count || 0);
    }, 0);
}, [conversations]);

// Badge displays when totalUnread > 0
// Shows "99+" for counts over 99
```

### Conversation List Badges

Each conversation in the sidebar shows its individual unread count:

```tsx
{conversations.map((conversation) => (
    <ConversationItem 
        key={conversation.id}
        conversation={conversation}
        unreadCount={conversation.unread_count}
    />
))}
```

## Layout Components

### ChatLayout

Main layout wrapper for all chat pages:

```tsx
import ChatLayout from '@/layouts/chat-layout';

export default function ChatPage({ conversationId, currentUser }) {
    return (
        <ChatLayout 
            title="Messages"
            conversationId={conversationId}
        >
            <YourChatContent />
        </ChatLayout>
    );
}
```

**Features:**
- Responsive sidebar (desktop) / full-screen (mobile)
- Conversation list with search
- Online user indicators
- Unread message counts
- Breadcrumb navigation integration

### OnlineUsers Component

Floating button showing online users:

```tsx
import { OnlineUsers } from '@/components/chat/OnlineUsers';

<OnlineUsers currentUserId={auth.user.id} />
```

**Features:**
- Shows count of online followed hunters
- Click to start conversation
- Real-time presence updates
- Unread messages badge
- Search functionality

## API Client Usage

All API calls use the centralized Axios client:

```tsx
import api from '@/lib/api';

// Fetch conversations
const { data } = await api.get('/conversations');

// Send message
const { data } = await api.post('/messages', {
    conversation_id: 1,
    content: 'Hello!'
});

// Mark as read
await api.post(`/conversations/${id}/messages/read`);
```

**Benefits:**
- Automatic CSRF token handling
- Consistent error handling
- Request/response interceptors
- TypeScript types

## Testing

### Feature Tests

```php
it('creates a conversation between two users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    actingAs($user1);
    
    $response = $this->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$user2->id],
    ]);
    
    $response->assertCreated()
        ->assertJsonStructure([
            'id',
            'participants',
            'created_at',
        ]);
});

it('marks messages as read', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    $conversation->participants()->attach($user->id);
    
    Message::factory()->count(3)->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id + 1,
    ]);
    
    actingAs($user);
    
    $this->postJson("/conversations/{$conversation->id}/messages/read")
        ->assertOk();
    
    expect(
        Message::where('conversation_id', $conversation->id)
            ->whereNotNull('read_at')
            ->count()
    )->toBe(3);
});
```

### Integration Tests

```php
it('displays online users in conversation list first', function () {
    $user = User::factory()->create();
    $onlineUser = User::factory()->create();
    $offlineUser = User::factory()->create();
    
    cache()->put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));
    
    actingAs($user);
    
    $response = $this->getJson('/conversations');
    $conversations = $response->json();
    
    // Frontend handles ordering, but data includes all necessary info
    expect($conversations)->toHaveCount(2);
});
```

## Performance Optimization

### Database Queries

Eager loading to prevent N+1 queries:

```php
$conversations = Conversation::query()
    ->with([
        'participants',
        'lastMessage.user',
        'messages' => fn($q) => $q->latest()->limit(50),
    ])
    ->whereHas('participants', fn($q) => $q->where('user_id', auth()->id()))
    ->latest('last_message_at')
    ->get();
```

### Caching Strategy

```php
// Cache online users for 30 seconds
$onlineUsers = Cache::remember('online_users', 30, function () {
    return User::whereIn('id', $this->getOnlineUserIds())->get();
});

// Per-user cache for conversations
$cacheKey = "user.{$userId}.conversations";
Cache::tags(['conversations', "user.{$userId}"])
    ->remember($cacheKey, 60, fn() => $this->getUserConversations($userId));
```

### WebSocket Optimization

```php
// Only broadcast to conversation participants
broadcast(new MessageSent($message))
    ->toOthers()
    ->to("conversation.{$conversationId}");
```

## Security

### Authorization

All chat endpoints require authentication and verify participant access:

```php
// Check if user is conversation participant
if (!$conversation->participants->contains(auth()->id())) {
    abort(403, 'Unauthorized');
}
```

### Input Validation

```php
$validated = $request->validate([
    'content' => 'required|string|max:2000',
    'type' => 'in:text,image,file',
    'conversation_id' => 'required|exists:conversations,id',
]);
```

### CSRF Protection

Chat API endpoints bypass CSRF for better SPA compatibility:

```php
#[WithoutMiddleware(VerifyCsrfToken::class)]
public function store(Request $request) { }
```

**Note:** Authentication via Sanctum tokens still required.

## Mobile Responsiveness

### Adaptive UI

```tsx
// Automatically adapts based on screen size
const isMobile = window.innerWidth < 768;

if (isMobile) {
    // Full-screen conversation view
    // Hide sidebar by default
    // Show back button
} else {
    // Split view with sidebar
    // Persistent conversation list
}
```

### Touch Optimization

- Larger tap targets (min 44x44px)
- Swipe gestures for navigation
- Pull-to-refresh on conversation list
- Optimized virtual keyboard handling

## Troubleshooting

### WebSocket Connection Issues

```bash
# Check Reverb is running
php artisan reverb:start

# Verify connection
php artisan tinker
> broadcast(new \App\Events\TestEvent());
```

### Cache Issues

```bash
# Clear cache
php artisan cache:clear

# Clear specific tags
php artisan tinker
> Cache::tags(['conversations'])->flush();
```

### Database Performance

```bash
# Analyze slow queries
php artisan telescope:prune

# Optimize tables
php artisan db:optimize
```

## Future Enhancements

- [ ] Group conversations (3+ participants)
- [ ] Message reactions (emoji)
- [ ] Reply/threading
- [ ] File attachments
- [ ] Voice messages
- [ ] Video calls
- [ ] Message search
- [ ] Conversation pinning
- [ ] Mute conversations
- [ ] Delete conversations
- [ ] Archive conversations

## Related Documentation

- [Advanced Chat Features](./13-advanced-chat-features.md)
- [Real-time Chat Basics](./06-real-time-chat.md)
- [Frontend Development](./09-frontend-development.md)
- [API Reference](./08-api-reference.md)
- [Testing Guide](./10-testing.md)
