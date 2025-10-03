# Real-time Chat

## Overview

Hunter includes a real-time messaging system built with Laravel Reverb (WebSockets) for instant communication between developers. The chat system supports one-on-one conversations with real-time message delivery, read receipts, and online presence.

## Architecture

- **Broadcasting:** Laravel Reverb (WebSockets)
- **Backend:** Laravel with Eloquent models
- **Frontend:** React with Inertia.js
- **Real-time Events:** Laravel Broadcasting
- **Persistence:** PostgreSQL database

## Models

### Conversation Model

```php
use App\Models\Conversation;

$conversation = Conversation::find(1);

// Properties
$conversation->id;              // Conversation ID
$conversation->created_by;      // User who created it
$conversation->created_at;      // Creation timestamp

// Relationships
$conversation->creator;         // User who created it
$conversation->participants;    // Users in conversation
$conversation->messages;        // All messages
```

### Message Model

```php
use App\Models\Message;

$message = Message::find(1);

// Properties
$message->id;                   // Message ID
$message->conversation_id;      // Conversation it belongs to
$message->user_id;             // Sender
$message->content;             // Message text
$message->read_at;             // When it was read
$message->created_at;          // When sent

// Relationships
$message->conversation;        // The conversation
$message->user;               // The sender
```

### ConversationParticipant Model

```php
use App\Models\ConversationParticipant;

// Pivot model for conversation participants
$participant->conversation_id;
$participant->user_id;
$participant->joined_at;
$participant->last_read_at;
$participant->is_admin;
```

## Creating Conversations

### Create a Conversation

```php
use App\Actions\Chat\CreateConversationAction;

$conversation = app(CreateConversationAction::class)->handle(
    creatorId: auth()->id(),
    participantIds: [2, 3], // User IDs to add
    initialMessage: 'Hey, let\'s discuss the project!'
);
```

**API Route:** `POST /conversations`

**Request:**

```json
{
  "participant_ids": [2, 3],
  "message": "Hey, let's discuss the project!"
}
```

**Response:**

```json
{
  "data": {
    "id": 1,
    "created_at": "2024-01-15T10:30:00Z",
    "participants": [
      {
        "id": 1,
        "name": "John Doe",
        "user_name": "johndoe",
        "avatar_url": "https://..."
      },
      {
        "id": 2,
        "name": "Jane Smith",
        "user_name": "janesmith",
        "avatar_url": "https://..."
      }
    ],
    "last_message": {
      "id": 1,
      "content": "Hey, let's discuss the project!",
      "user_id": 1,
      "created_at": "2024-01-15T10:30:00Z"
    },
    "unread_count": 0
  }
}
```

### Get Conversations

```php
use App\Actions\Chat\GetConversationsAction;

$conversations = app(GetConversationsAction::class)->handle(
    userId: auth()->id()
);
```

**API Route:** `GET /conversations`

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "created_at": "2024-01-15T10:30:00Z",
      "participants": [
        {
          "id": 2,
          "name": "Jane Smith",
          "user_name": "janesmith",
          "avatar_url": "https://...",
          "is_online": true
        }
      ],
      "last_message": {
        "id": 42,
        "content": "Sounds good!",
        "user_id": 2,
        "created_at": "2024-01-15T15:30:00Z",
        "read_at": null
      },
      "unread_count": 3
    }
  ]
}
```

### Delete Conversation

```php
use App\Actions\Chat\DeleteConversationAction;

app(DeleteConversationAction::class)->handle(
    conversationId: $conversationId,
    userId: auth()->id()
);
```

**API Route:** `DELETE /conversations/{conversation}`

**Authorization:** User must be a participant

## Sending Messages

### Send a Message

```php
use App\Actions\Chat\SendMessageAction;

$message = app(SendMessageAction::class)->handle(
    conversationId: $conversationId,
    userId: auth()->id(),
    content: 'Hello! How are you?'
);
```

**API Route:** `POST /messages`

**Request:**

```json
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
    "user_id": 1,
    "content": "Hello! How are you?",
    "read_at": null,
    "created_at": "2024-01-15T10:30:00Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "user_name": "johndoe",
      "avatar_url": "https://..."
    }
  }
}
```

### Get Messages

```php
use App\Actions\Chat\GetConversationMessagesAction;

$messages = app(GetConversationMessagesAction::class)->handle(
    conversationId: $conversationId,
    userId: auth()->id(),
    page: 1,
    perPage: 50
);
```

**API Route:** `GET /conversations/{conversation}/messages`

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Messages per page (default: 50)

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "content": "Hello!",
      "user_id": 1,
      "read_at": "2024-01-15T10:35:00Z",
      "created_at": "2024-01-15T10:30:00Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "avatar_url": "https://..."
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 42
  }
}
```

### Delete Message

```php
use App\Actions\Chat\DeleteMessageAction;

app(DeleteMessageAction::class)->handle(
    messageId: $messageId,
    userId: auth()->id()
);
```

**API Route:** `DELETE /messages/{message}`

**Authorization:** User must be the sender

## Read Receipts

### Mark Messages as Read

```php
use App\Actions\Chat\MarkMessagesAsReadAction;

app(MarkMessagesAsReadAction::class)->handle(
    conversationId: $conversationId,
    userId: auth()->id()
);
```

**API Route:** `POST /conversations/{conversation}/messages/read`

**Response:**

```json
{
  "message": "Messages marked as read",
  "unread_count": 0
}
```

### Bulk Mark as Read (API)

**API Route:** `POST /api/messages/bulk-read`

**Request:**

```json
{
  "message_ids": [1, 2, 3, 4, 5]
}
```

## Real-time Events

### Message Sent Event

Broadcast when a message is sent:

```php
use App\Events\MessageSent;

// In SendMessageAction
broadcast(new MessageSent($message))->toOthers();
```

**Event Structure:**

```php
class MessageSent implements ShouldBroadcast
{
    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->message->conversation_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
```

**Frontend Listening:**

```tsx
import Echo from 'laravel-echo';

Echo.private(`conversation.${conversationId}`)
  .listen('.message.sent', (event) => {
    console.log('New message:', event.message);
    // Add message to UI
  });
```

### Message Received Event

```php
use App\Events\MessageReceived;

broadcast(new MessageReceived($message, $recipientId));
```

### Conversation Created Event

```php
use App\Events\ConversationCreated;

broadcast(new ConversationCreated($conversation, $userId));
```

### Conversations Snapshot Event

Broadcasts conversation list updates:

```php
use App\Events\ConversationsSnapshot;

broadcast(new ConversationsSnapshot($conversations, $userId));
```

## User Presence

Track online/offline status for chat users.

### Mark User Online

**API Route:** `POST /presence/online`

```php
Route::post('/presence/online', function (Request $request) {
    $user = $request->user();
    cache()->put("user_online_{$user->id}", now(), now()->addMinutes(10));
    
    broadcast(new UserOnline($user));
    
    return response()->json(['status' => 'online', 'user_id' => $user->id]);
});
```

### Mark User Offline

**API Route:** `POST /presence/offline`

```php
Route::post('/presence/offline', function (Request $request) {
    $user = $request->user();
    cache()->forget("user_online_{$user->id}");
    
    broadcast(new UserOffline($user));
    
    return response()->json(['status' => 'offline']);
});
```

### Get Online Users

**API Route:** `GET /users/online`

```php
Route::get('/users/online', function (Request $request) {
    $onlineUserIds = [];
    
    $users = User::all();
    foreach ($users as $user) {
        if (cache()->has("user_online_{$user->id}")) {
            $onlineUserIds[] = $user->id;
        }
    }
    
    $onlineUsers = User::whereIn('id', $onlineUserIds)
        ->where('id', '!=', $request->user()->id)
        ->get();
    
    return UserResource::collection($onlineUsers);
});
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

### Check User Online Status

```php
function isUserOnline(int $userId): bool
{
    return cache()->has("user_online_{$userId}");
}
```

## Broadcasting Configuration

### Configure Reverb

In `.env`:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Start Reverb Server

```bash
php artisan reverb:start
```

For production:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### Channel Authorization

Define private channels in `routes/channels.php`:

```php
use Illuminate\Support\Facades\Broadcast;

// Conversation channel - only participants can join
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return $user->conversations()
        ->where('conversations.id', $conversationId)
        ->exists();
});

// User presence channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

## Mobile Chat

Hunter includes a mobile-optimized chat view:

**Route:** `GET /chat/mobile/{conversation}`

```php
Route::get('/chat/mobile/{conversation}', function ($conversationId) {
    $user = auth()->user();
    
    return Inertia::render('Chat/Mobile', [
        'conversationId' => (int) $conversationId,
        'currentUser' => [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => app(GetAvatarAction::class)->handle($user),
        ],
    ]);
})->name('chat.mobile');
```

## Frontend Integration

### React Chat Component Example

```tsx
import { useEffect, useState } from 'react';
import Echo from 'laravel-echo';
import { router } from '@inertiajs/react';

function ChatConversation({ conversationId, currentUser }) {
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');

  useEffect(() => {
    // Load initial messages
    loadMessages();

    // Listen for new messages
    Echo.private(`conversation.${conversationId}`)
      .listen('.message.sent', (event) => {
        setMessages((prev) => [...prev, event.message]);
      });

    return () => {
      Echo.leave(`conversation.${conversationId}`);
    };
  }, [conversationId]);

  const loadMessages = async () => {
    const response = await fetch(`/conversations/${conversationId}/messages`);
    const data = await response.json();
    setMessages(data.data);
  };

  const sendMessage = () => {
    router.post('/messages', {
      conversation_id: conversationId,
      content: newMessage,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        setNewMessage('');
      },
    });
  };

  return (
    <div className="chat-container">
      <div className="messages">
        {messages.map((message) => (
          <div key={message.id} className={
            message.user_id === currentUser.id ? 'message-sent' : 'message-received'
          }>
            <p>{message.content}</p>
            <span>{message.created_at}</span>
          </div>
        ))}
      </div>
      
      <div className="message-input">
        <input
          value={newMessage}
          onChange={(e) => setNewMessage(e.target.value)}
          onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
          placeholder="Type a message..."
        />
        <button onClick={sendMessage}>Send</button>
      </div>
    </div>
  );
}
```

### Presence Management

```tsx
import { useEffect } from 'react';
import Echo from 'laravel-echo';

function usePresence() {
  useEffect(() => {
    // Mark online when component mounts
    fetch('/presence/online', { method: 'POST' });

    // Keep-alive interval
    const interval = setInterval(() => {
      fetch('/presence/online', { method: 'POST' });
    }, 5 * 60 * 1000); // Every 5 minutes

    // Mark offline on unmount
    return () => {
      clearInterval(interval);
      fetch('/presence/offline', { method: 'POST' });
    };
  }, []);
}
```

## Testing

### Feature Tests

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;

class ChatTest extends TestCase
{
    public function test_user_can_create_conversation(): void
    {
        $creator = User::factory()->create();
        $participant = User::factory()->create();

        $response = $this->actingAs($creator)
            ->post('/conversations', [
                'participant_ids' => [$participant->id],
                'message' => 'Hello!',
            ]);

        $this->assertDatabaseHas('conversations', [
            'created_by' => $creator->id,
        ]);
    }

    public function test_user_can_send_message(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach($user->id);

        $response = $this->actingAs($user)
            ->post('/messages', [
                'conversation_id' => $conversation->id,
                'content' => 'Test message',
            ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => 'Test message',
        ]);
    }

    public function test_user_can_mark_messages_as_read(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach($user->id);
        
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/conversations/{$conversation->id}/messages/read");

        $this->assertNotNull($message->fresh()->read_at);
    }
}
```

## Best Practices

1. **Rate limiting** - Prevent message spam
2. **Message validation** - Sanitize content
3. **Pagination** - Load messages in chunks
4. **Presence heartbeat** - Update online status regularly
5. **Error handling** - Handle WebSocket disconnections
6. **Encryption** - Consider end-to-end encryption for sensitive data
7. **Notification integration** - Send push/email for missed messages
8. **Archive old conversations** - Improve performance
9. **Media attachments** - Consider adding image/file support
10. **Typing indicators** - Show when users are typing
