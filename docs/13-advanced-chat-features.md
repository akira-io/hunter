# 💬 Advanced Chat Features

## Overview

Building upon the core real-time chat functionality, Hunter includes advanced features for rich, contextual, and organized conversations. These features enhance collaboration and make communication more efficient.

## Feature Overview

```
┌─────────────────────────────────────────────────┐
│            Advanced Chat Features                │
└─────────────────────────────────────────────────┘
   │
   ├── Reply to Messages (Threading)
   ├── Forward Messages
   ├── Edit Sent Messages
   ├── Delete Messages
   ├── Message Reactions (Emoji)
   ├── Quote Messages
   ├── Search in Conversations
   ├── Save/Bookmark Messages
   └── Read Receipts with Timestamps
```

## 1. Reply to Messages (Threading)

### Overview

Reply to specific messages to maintain context in fast-moving conversations, similar to iMessage, WhatsApp, and Slack.

### Database Schema

```sql
ALTER TABLE messages ADD COLUMN replied_to_message_id BIGINT NULL;
ALTER TABLE messages ADD CONSTRAINT fk_replied_to 
    FOREIGN KEY (replied_to_message_id) REFERENCES messages(id) ON DELETE SET NULL;

CREATE INDEX idx_replied_to ON messages(replied_to_message_id);
```

### Implementation

#### Backend

```php
// Message Model
class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'replied_to_message_id',
    ];
    
    public function repliedTo()
    {
        return $this->belongsTo(Message::class, 'replied_to_message_id');
    }
    
    public function replies()
    {
        return $this->hasMany(Message::class, 'replied_to_message_id');
    }
}

// Create reply
public function sendReply(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'content' => 'required|string|max:10000',
        'replied_to_message_id' => 'required|exists:messages,id',
    ]);
    
    // Validate that replied message is in same conversation
    $repliedTo = Message::findOrFail($validated['replied_to_message_id']);
    
    if ($repliedTo->conversation_id !== $conversation->id) {
        abort(422, 'Cannot reply to message from different conversation');
    }
    
    $message = $conversation->messages()->create([
        'sender_id' => auth()->id(),
        'content' => $validated['content'],
        'replied_to_message_id' => $validated['replied_to_message_id'],
    ]);
    
    // Load relationship for broadcast
    $message->load('repliedTo.sender');
    
    broadcast(new MessageSent($message))->toOthers();
    
    return response()->json($message);
}
```

#### Frontend

```tsx
interface MessageWithReply {
    id: number;
    content: string;
    replied_to?: {
        id: number;
        content: string;
        sender: {
            name: string;
        };
    };
}

export function MessageBubble({ message }: { message: MessageWithReply }) {
    const scrollToMessage = (id: number) => {
        const element = document.getElementById(`message-${id}`);
        element?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        element?.classList.add('highlight-pulse');
        setTimeout(() => element?.classList.remove('highlight-pulse'), 2000);
    };
    
    return (
        <div id={`message-${message.id}`} className="space-y-2">
            {message.replied_to && (
                <button
                    onClick={() => scrollToMessage(message.replied_to.id)}
                    className="p-2 bg-secondary rounded text-sm border-l-2 border-primary"
                >
                    <p className="font-semibold">{message.replied_to.sender.name}</p>
                    <p className="text-muted-foreground line-clamp-2">
                        {message.replied_to.content}
                    </p>
                </button>
            )}
            <div className="p-3 bg-card rounded-lg">
                {message.content}
            </div>
        </div>
    );
}

// Reply composer
export function MessageComposer() {
    const [replyingTo, setReplyingTo] = useState<Message | null>(null);
    
    return (
        <div className="space-y-2">
            {replyingTo && (
                <div className="flex items-center gap-2 p-2 bg-secondary rounded">
                    <div className="flex-1">
                        <p className="text-sm font-semibold">
                            Replying to {replyingTo.sender.name}
                        </p>
                        <p className="text-xs text-muted-foreground line-clamp-1">
                            {replyingTo.content}
                        </p>
                    </div>
                    <button onClick={() => setReplyingTo(null)}>
                        <XIcon className="w-4 h-4" />
                    </button>
                </div>
            )}
            <textarea placeholder="Type a message..." />
        </div>
    );
}
```

## 2. Forward Messages

### Database Schema

```sql
ALTER TABLE messages ADD COLUMN forwarded_from_message_id BIGINT NULL;
ALTER TABLE messages ADD CONSTRAINT fk_forwarded_from 
    FOREIGN KEY (forwarded_from_message_id) REFERENCES messages(id) ON DELETE SET NULL;
```

### Implementation

```php
public function forwardMessage(Request $request)
{
    $validated = $request->validate([
        'message_id' => 'required|exists:messages,id',
        'conversation_ids' => 'required|array',
        'conversation_ids.*' => 'exists:conversations,id',
    ]);
    
    $original = Message::findOrFail($validated['message_id']);
    
    foreach ($validated['conversation_ids'] as $conversationId) {
        // Check user is participant
        $conversation = Conversation::findOrFail($conversationId);
        
        if (!$conversation->participants->contains(auth()->id())) {
            continue;
        }
        
        $forwarded = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'content' => $original->content,
            'type' => $original->type,
            'forwarded_from_message_id' => $original->id,
        ]);
        
        broadcast(new MessageSent($forwarded))->toOthers();
    }
    
    return response()->json(['success' => true]);
}
```

## 3. Edit Sent Messages

### Database Schema

```sql
ALTER TABLE messages ADD COLUMN edited_at TIMESTAMP NULL;
ALTER TABLE messages ADD COLUMN edit_history JSONB NULL;
```

### Implementation

```php
public function editMessage(Request $request, Message $message)
{
    // Only owner can edit
    if ($message->sender_id !== auth()->id()) {
        abort(403);
    }
    
    // Check time limit (15 minutes)
    if ($message->created_at->diffInMinutes(now()) > 15) {
        abort(422, 'Edit time limit exceeded');
    }
    
    $validated = $request->validate([
        'content' => 'required|string|max:10000',
    ]);
    
    // Store edit history
    $history = $message->edit_history ?? [];
    $history[] = [
        'content' => $message->content,
        'edited_at' => now()->toISOString(),
    ];
    
    $message->update([
        'content' => $validated['content'],
        'edited_at' => now(),
        'edit_history' => $history,
    ]);
    
    broadcast(new MessageEdited($message))->toOthers();
    
    return response()->json($message);
}
```

### Frontend

```tsx
export function MessageActions({ message }: { message: Message }) {
    const [editing, setEditing] = useState(false);
    const canEdit = message.sender_id === auth.user.id && 
                   dayjs().diff(message.created_at, 'minute') < 15;
    
    if (editing) {
        return <EditMessageForm message={message} onCancel={() => setEditing(false)} />;
    }
    
    return (
        <div className="flex items-center gap-2">
            {message.edited_at && (
                <span className="text-xs text-muted-foreground">(Edited)</span>
            )}
            {canEdit && (
                <button onClick={() => setEditing(true)}>
                    <PencilIcon className="w-4 h-4" />
                </button>
            )}
        </div>
    );
}
```

## 4. Delete Messages

### Database Schema

```sql
ALTER TABLE messages ADD COLUMN deleted_at TIMESTAMP NULL;
```

### Implementation

```php
public function deleteMessage(Message $message)
{
    if ($message->sender_id !== auth()->id()) {
        abort(403);
    }
    
    $message->update([
        'deleted_at' => now(),
        'content' => '[Message deleted]',
    ]);
    
    broadcast(new MessageDeleted($message))->toOthers();
    
    return response()->json(['success' => true]);
}
```

### Frontend

```tsx
export function MessageBubble({ message }: { message: Message }) {
    if (message.deleted_at) {
        return (
            <div className="p-3 bg-secondary/50 rounded-lg italic text-muted-foreground">
                This message was deleted
            </div>
        );
    }
    
    return (
        <div className="p-3 bg-card rounded-lg">
            {message.content}
        </div>
    );
}
```

## 5. Message Reactions

### Database Schema

```sql
CREATE TABLE message_reactions (
    id BIGSERIAL PRIMARY KEY,
    message_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (message_id, user_id, emoji),
    INDEX idx_message_reactions (message_id)
);
```

### Implementation

```php
public function toggleReaction(Request $request, Message $message)
{
    $validated = $request->validate([
        'emoji' => 'required|string|max:10',
    ]);
    
    $reaction = MessageReaction::where([
        'message_id' => $message->id,
        'user_id' => auth()->id(),
        'emoji' => $validated['emoji'],
    ])->first();
    
    if ($reaction) {
        $reaction->delete();
        $action = 'removed';
    } else {
        MessageReaction::create([
            'message_id' => $message->id,
            'user_id' => auth()->id(),
            'emoji' => $validated['emoji'],
        ]);
        $action = 'added';
    }
    
    broadcast(new MessageReactionToggled($message, $validated['emoji'], $action))->toOthers();
    
    return response()->json(['success' => true, 'action' => $action]);
}

// Get reactions grouped by emoji
public function getReactions(Message $message)
{
    return $message->reactions()
        ->selectRaw('emoji, count(*) as count, GROUP_CONCAT(user_id) as user_ids')
        ->groupBy('emoji')
        ->get();
}
```

### Frontend

```tsx
export function MessageReactions({ message }: { message: Message }) {
    const { reactions } = message;
    
    const handleReaction = (emoji: string) => {
        axios.post(`/api/messages/${message.id}/react`, { emoji });
    };
    
    return (
        <div className="flex gap-1 mt-1">
            {reactions.map(({ emoji, count, user_ids }) => {
                const hasReacted = user_ids.includes(auth.user.id);
                
                return (
                    <button
                        key={emoji}
                        onClick={() => handleReaction(emoji)}
                        className={cn(
                            'px-2 py-1 rounded-full text-sm',
                            hasReacted ? 'bg-primary/20' : 'bg-secondary'
                        )}
                    >
                        {emoji} {count}
                    </button>
                );
            })}
            <EmojiPicker onSelect={handleReaction} />
        </div>
    );
}
```

## 6. Quote Messages

Similar to Reply, but creates a visual quote block:

```tsx
export function QuotedMessage({ message }: { message: Message }) {
    return (
        <div className="border-l-4 border-primary pl-3 py-2 bg-secondary/30 rounded">
            <p className="text-sm font-semibold">{message.sender.name}</p>
            <p className="text-sm text-muted-foreground">{message.content}</p>
        </div>
    );
}
```

## 7. Search Messages in Conversations

### Implementation

```php
public function searchMessages(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'query' => 'required|string|min:2',
        'sender_id' => 'nullable|exists:users,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after:start_date',
    ]);
    
    $query = $conversation->messages()
        ->where('content', 'ILIKE', '%' . $validated['query'] . '%')
        ->when($validated['sender_id'] ?? null, fn($q, $senderId) => 
            $q->where('sender_id', $senderId)
        )
        ->when($validated['start_date'] ?? null, fn($q, $start) => 
            $q->where('created_at', '>=', $start)
        )
        ->when($validated['end_date'] ?? null, fn($q, $end) => 
            $q->where('created_at', '<=', $end)
        );
    
    $results = $query->with('sender')
        ->orderByDesc('created_at')
        ->paginate(20);
    
    return response()->json($results);
}
```

### Frontend

```tsx
export function MessageSearch({ conversationId }: { conversationId: number }) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    
    const search = useDebouncedCallback(async (q: string) => {
        if (q.length < 2) return;
        
        setLoading(true);
        const { data } = await axios.get(`/api/conversations/${conversationId}/search`, {
            params: { query: q },
        });
        setResults(data.data);
        setLoading(false);
    }, 300);
    
    return (
        <div className="space-y-4">
            <input
                type="text"
                placeholder="Search messages..."
                value={query}
                onChange={(e) => {
                    setQuery(e.target.value);
                    search(e.target.value);
                }}
                className="w-full"
            />
            {loading && <Spinner />}
            <div className="space-y-2">
                {results.map(message => (
                    <SearchResult key={message.id} message={message} />
                ))}
            </div>
        </div>
    );
}
```

## 8. Save/Bookmark Messages

### Database Schema

```sql
CREATE TABLE saved_messages (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    message_id BIGINT NOT NULL,
    saved_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    UNIQUE (user_id, message_id),
    INDEX idx_user_saved (user_id)
);
```

### Implementation

```php
public function toggleSave(Message $message)
{
    $saved = SavedMessage::where([
        'user_id' => auth()->id(),
        'message_id' => $message->id,
    ])->first();
    
    if ($saved) {
        $saved->delete();
        return response()->json(['saved' => false]);
    }
    
    SavedMessage::create([
        'user_id' => auth()->id(),
        'message_id' => $message->id,
    ]);
    
    return response()->json(['saved' => true]);
}

public function getSavedMessages()
{
    return auth()->user()
        ->savedMessages()
        ->with('message.sender', 'message.conversation')
        ->latest()
        ->paginate(50);
}
```

## 9. Read Receipts with Elapsed Time

### Database Schema

```sql
CREATE TABLE message_reads (
    id BIGSERIAL PRIMARY KEY,
    message_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    read_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (message_id, user_id),
    INDEX idx_message_reads (message_id)
);
```

### Implementation

```php
// Mark message as read
public function markAsRead(Message $message)
{
    MessageRead::updateOrCreate(
        [
            'message_id' => $message->id,
            'user_id' => auth()->id(),
        ],
        [
            'read_at' => now(),
        ]
    );
}

// Get read receipts
public function getReadReceipts(Message $message)
{
    return $message->reads()
        ->with('user:id,name,avatar')
        ->get()
        ->map(fn($read) => [
            'user' => $read->user,
            'read_at' => $read->read_at,
            'elapsed' => $read->read_at->diffForHumans(),
        ]);
}
```

### Frontend

```tsx
export function ReadReceipts({ message }: { message: Message }) {
    const { reads } = message;
    
    return (
        <div className="text-xs text-muted-foreground">
            {reads.map(read => (
                <div key={read.user.id}>
                    Seen by {read.user.name} {read.elapsed}
                </div>
            ))}
        </div>
    );
}
```

## Configuration

```php
// config/chat.php

return [
    'features' => [
        'replies' => true,
        'forward' => true,
        'edit' => true,
        'delete' => true,
        'reactions' => true,
        'quote' => true,
        'search' => true,
        'save' => true,
        'read_receipts' => true,
    ],
    
    'limits' => [
        'edit_time_limit' => 15, // minutes
        'max_forward_targets' => 5,
        'search_min_length' => 2,
    ],
];
```

## Testing

```php
test('user can reply to message', function () {
    $conversation = Conversation::factory()->create();
    $original = Message::factory()->create(['conversation_id' => $conversation->id]);
    
    $response = $this->actingAs($user)
        ->post("/api/conversations/{$conversation->id}/messages", [
            'content' => 'This is a reply',
            'replied_to_message_id' => $original->id,
        ]);
    
    $response->assertSuccessful();
    expect(Message::latest()->first()->replied_to_message_id)->toBe($original->id);
});

test('user can only edit own message within time limit', function () {
    $message = Message::factory()->create(['sender_id' => $user->id]);
    
    $response = $this->actingAs($user)
        ->put("/api/messages/{$message->id}", [
            'content' => 'Edited content',
        ]);
    
    $response->assertSuccessful();
    expect($message->fresh()->content)->toBe('Edited content');
});
```

## Related Documentation

- [Real-time Chat](./06-real-time-chat.md) - Core chat functionality
- [API Reference](./08-api-reference.md) - Complete API docs
- [Testing](./10-testing.md) - Testing guidelines
