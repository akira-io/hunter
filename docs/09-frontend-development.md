# Frontend Development

## Overview

Hunter's frontend is built with:
- **React 18** - UI library
- **TypeScript** - Type-safe JavaScript
- **Inertia.js v2** - Modern monolith approach
- **Tailwind CSS v4** - Utility-first CSS framework
- **Vite** - Fast build tool

## Project Structure

```
resources/js/
├── Components/          # Reusable React components
│   ├── markdown/       # Markdown editor components
│   │   ├── MarkdownEditor.tsx
│   │   ├── MarkdownRenderer.tsx
│   │   ├── EmojiPicker.tsx
│   │   └── MarkdownHelp.tsx
│   └── ...
├── Layouts/            # Page layouts
├── Pages/              # Inertia page components
│   ├── auth/          # Authentication pages
│   ├── settings/      # Settings pages
│   ├── hunts/         # Hunt-related pages
│   ├── Chat/          # Chat interface
│   └── ...
├── Hooks/             # Custom React hooks
├── Types/             # TypeScript type definitions
└── app.tsx            # Application entry point
```

> **Note**: For detailed information about the Markdown editing system, see the [Markdown Editor documentation](./16-markdown-editor.md).

## Setup

### Install Dependencies

```bash
npm install
```

### Development Server

```bash
# Start Vite dev server with HMR
npm run dev
```

### Build for Production

```bash
# Build optimized assets
npm run build
```

### Type Checking

```bash
# Run TypeScript compiler
npm run type-check
```

## Inertia.js Integration

### Page Components

Inertia pages receive props from the server:

```tsx
import { PageProps } from '@/Types';

interface Props extends PageProps {
  user: {
    id: number;
    name: string;
    email: string;
  };
}

export default function Dashboard({ user }: Props) {
  return (
    <div>
      <h1>Welcome, {user.name}</h1>
    </div>
  );
}
```

### Navigation

Use Inertia's router for client-side navigation:

```tsx
import { router } from '@inertiajs/react';

function NavigationExample() {
  // Visit a page
  const handleClick = () => {
    router.visit('/profile');
  };

  // Visit with options
  const handleUpdate = () => {
    router.visit('/profile', {
      method: 'put',
      data: { name: 'John Doe' },
      preserveScroll: true,
      onSuccess: () => {
        console.log('Profile updated!');
      },
    });
  };

  // Use Link component
  return (
    <>
      <button onClick={handleClick}>Go to Profile</button>
      <Link href="/profile">Profile</Link>
    </>
  );
}
```

### Forms

Use Inertia's Form component or useForm hook:

```tsx
import { Form } from '@inertiajs/react';

export default function CreateHunt() {
  return (
    <Form
      action="/hunts"
      method="post"
      resetOnSuccess
    >
      <textarea name="content" required />
      <button type="submit">Post Hunt</button>
    </Form>
  );
}
```

**Using useForm hook:**

```tsx
import { useForm } from '@inertiajs/react';

export default function CreateHunt() {
  const { data, setData, post, processing, errors } = useForm({
    content: '',
  });

  const submit = (e) => {
    e.preventDefault();
    post('/hunts', {
      preserveScroll: true,
      onSuccess: () => {
        setData('content', '');
      },
    });
  };

  return (
    <form onSubmit={submit}>
      <textarea
        value={data.content}
        onChange={(e) => setData('content', e.target.value)}
        placeholder="What's on your mind?"
      />
      {errors.content && (
        <div className="error">{errors.content}</div>
      )}
      <button type="submit" disabled={processing}>
        {processing ? 'Posting...' : 'Post'}
      </button>
    </form>
  );
}
```

### Shared Data

Access shared data from any component:

```tsx
import { usePage } from '@inertiajs/react';

function UserInfo() {
  const { auth, flash } = usePage().props;

  return (
    <div>
      {auth.user ? (
        <span>Welcome, {auth.user.name}!</span>
      ) : (
        <Link href="/login">Login</Link>
      )}
      
      {flash.success && (
        <div className="alert">{flash.success}</div>
      )}
    </div>
  );
}
```

## Custom Hooks

### useAuth

Access authenticated user:

```tsx
// hooks/useAuth.ts
import { usePage } from '@inertiajs/react';

export function useAuth() {
  const { auth } = usePage().props;
  
  return {
    user: auth.user,
    isAuthenticated: !!auth.user,
  };
}

// Usage
function MyComponent() {
  const { user, isAuthenticated } = useAuth();
  
  if (!isAuthenticated) {
    return <div>Please log in</div>;
  }
  
  return <div>Hello, {user.name}!</div>;
}
```

### useFlash

Handle flash messages:

```tsx
// hooks/useFlash.ts
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export function useFlash() {
  const { flash } = usePage().props;
  
  useEffect(() => {
    if (flash.success) {
      // Show success toast
      console.log('Success:', flash.success);
    }
    
    if (flash.error) {
      // Show error toast
      console.error('Error:', flash.error);
    }
  }, [flash]);
  
  return flash;
}
```

### useDebounce

Debounce search inputs:

```tsx
// hooks/useDebounce.ts
import { useState, useEffect } from 'react';

export function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}

// Usage
function SearchBar() {
  const [query, setQuery] = useState('');
  const debouncedQuery = useDebounce(query, 300);

  useEffect(() => {
    if (debouncedQuery) {
      // Perform search
      router.visit(`/search?q=${debouncedQuery}`, {
        preserveState: true,
        preserveScroll: true,
      });
    }
  }, [debouncedQuery]);

  return (
    <input
      value={query}
      onChange={(e) => setQuery(e.target.value)}
      placeholder="Search..."
    />
  );
}
```

## Real-time Features

### Laravel Echo Setup

```tsx
// bootstrap.ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT,
  wssPort: import.meta.env.VITE_REVERB_PORT,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
});
```

### Listening to Events

```tsx
import { useEffect } from 'react';

function ChatComponent({ conversationId }) {
  useEffect(() => {
    const channel = window.Echo.private(`conversation.${conversationId}`);
    
    channel.listen('.message.sent', (event) => {
      console.log('New message:', event.message);
      // Update UI with new message
    });

    return () => {
      channel.stopListening('.message.sent');
      window.Echo.leave(`conversation.${conversationId}`);
    };
  }, [conversationId]);

  return <div>Chat interface</div>;
}
```

### Presence Channels

```tsx
function OnlineUsers() {
  const [onlineUsers, setOnlineUsers] = useState([]);

  useEffect(() => {
    window.Echo.join('online-users')
      .here((users) => {
        setOnlineUsers(users);
      })
      .joining((user) => {
        setOnlineUsers((prev) => [...prev, user]);
      })
      .leaving((user) => {
        setOnlineUsers((prev) => 
          prev.filter((u) => u.id !== user.id)
        );
      });

    return () => {
      window.Echo.leave('online-users');
    };
  }, []);

  return (
    <div>
      <h3>Online Users ({onlineUsers.length})</h3>
      {onlineUsers.map((user) => (
        <div key={user.id}>{user.name}</div>
      ))}
    </div>
  );
}
```

## Tailwind CSS

### Configuration

```javascript
// tailwind.config.js
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.tsx',
    './resources/**/*.ts',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#3B82F6',
        secondary: '#10B981',
      },
    },
  },
  plugins: [],
};
```

### Dark Mode

Hunter supports dark mode:

```tsx
function ThemeToggle() {
  const toggleTheme = () => {
    document.documentElement.classList.toggle('dark');
  };

  return (
    <button onClick={toggleTheme}>
      Toggle Dark Mode
    </button>
  );
}
```

**Using dark mode classes:**

```tsx
<div className="bg-white dark:bg-gray-900 text-black dark:text-white">
  Content that adapts to theme
</div>
```

## Component Examples

### Hunt Card

```tsx
interface Hunt {
  id: number;
  content: string;
  created_at: string;
  owner: {
    name: string;
    user_name: string;
    avatar_url: string;
  };
  likes_count: number;
  comments_count: number;
  has_liked: boolean;
}

function HuntCard({ hunt }: { hunt: Hunt }) {
  const handleLike = () => {
    router.post(`/hunts/${hunt.id}/like`, {}, {
      preserveScroll: true,
    });
  };

  return (
    <div className="border rounded-lg p-4">
      <div className="flex items-center gap-3 mb-3">
        <img
          src={hunt.owner.avatar_url}
          alt={hunt.owner.name}
          className="w-10 h-10 rounded-full"
        />
        <div>
          <div className="font-semibold">{hunt.owner.name}</div>
          <div className="text-sm text-gray-500">
            @{hunt.owner.user_name}
          </div>
        </div>
      </div>
      
      <p className="mb-4">{hunt.content}</p>
      
      <div className="flex gap-4">
        <button
          onClick={handleLike}
          className={hunt.has_liked ? 'text-red-500' : 'text-gray-500'}
        >
          {hunt.has_liked ? '❤️' : '🤍'} {hunt.likes_count}
        </button>
        
        <Link href={`/hunts/${hunt.id}`}>
          💬 {hunt.comments_count}
        </Link>
      </div>
    </div>
  );
}
```

### User Card

```tsx
interface User {
  id: number;
  name: string;
  user_name: string;
  avatar_url: string;
  bio: string;
  skills: string[];
  is_following: boolean;
}

function UserCard({ user }: { user: User }) {
  const handleFollow = () => {
    const endpoint = user.is_following 
      ? `/unfollow/${user.id}` 
      : `/follow/${user.id}`;
      
    router.post(endpoint, {}, {
      preserveScroll: true,
    });
  };

  return (
    <div className="border rounded-lg p-4">
      <div className="flex items-start justify-between">
        <div className="flex gap-3">
          <img
            src={user.avatar_url}
            alt={user.name}
            className="w-16 h-16 rounded-full"
          />
          <div>
            <h3 className="font-semibold">{user.name}</h3>
            <p className="text-gray-500">@{user.user_name}</p>
            <p className="mt-2">{user.bio}</p>
          </div>
        </div>
        
        <button
          onClick={handleFollow}
          className={`px-4 py-2 rounded ${
            user.is_following 
              ? 'bg-gray-200' 
              : 'bg-blue-500 text-white'
          }`}
        >
          {user.is_following ? 'Following' : 'Follow'}
        </button>
      </div>
      
      <div className="mt-3 flex flex-wrap gap-2">
        {user.skills.map((skill) => (
          <span
            key={skill}
            className="px-2 py-1 bg-gray-100 rounded text-sm"
          >
            {skill}
          </span>
        ))}
      </div>
    </div>
  );
}
```

### Modal Component

```tsx
import { useEffect, useRef } from 'react';

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  children: React.ReactNode;
}

function Modal({ isOpen, onClose, children }: ModalProps) {
  const modalRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };

    if (isOpen) {
      document.addEventListener('keydown', handleEscape);
      document.body.style.overflow = 'hidden';
    }

    return () => {
      document.removeEventListener('keydown', handleEscape);
      document.body.style.overflow = 'unset';
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div
        className="absolute inset-0 bg-black/50"
        onClick={onClose}
      />
      <div
        ref={modalRef}
        className="relative bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4"
      >
        {children}
      </div>
    </div>
  );
}
```

## TypeScript Types

### Common Types

```typescript
// Types/index.d.ts
export interface User {
  id: number;
  name: string;
  email: string;
  user_name: string;
  avatar_url: string;
  bio: string;
  location: string;
  skills: string[];
}

export interface Hunt {
  id: number;
  content: string;
  owner_id: number;
  owner: User;
  likes_count: number;
  comments_count: number;
  has_liked: boolean;
  is_pinned: boolean;
  created_at: string;
  media: Media[];
}

export interface Comment {
  id: number;
  content: string;
  user_id: number;
  user: User;
  likes_count: number;
  has_liked: boolean;
  created_at: string;
}

export interface PageProps {
  auth: {
    user: User | null;
  };
  flash: {
    success?: string;
    error?: string;
  };
}
```

## Testing

### Component Testing

```tsx
import { render, screen, fireEvent } from '@testing-library/react';
import { HuntCard } from '@/Components/HuntCard';

describe('HuntCard', () => {
  const mockHunt = {
    id: 1,
    content: 'Test hunt',
    owner: {
      name: 'John Doe',
      user_name: 'johndoe',
      avatar_url: 'https://...',
    },
    likes_count: 5,
    comments_count: 2,
    has_liked: false,
  };

  it('renders hunt content', () => {
    render(<HuntCard hunt={mockHunt} />);
    expect(screen.getByText('Test hunt')).toBeInTheDocument();
  });

  it('handles like button click', () => {
    render(<HuntCard hunt={mockHunt} />);
    const likeButton = screen.getByRole('button', { name: /like/i });
    fireEvent.click(likeButton);
    // Assert API call was made
  });
});
```

## Best Practices

1. **Use TypeScript** - Type safety prevents bugs
2. **Component composition** - Break down complex UIs
3. **Memoization** - Use `useMemo` and `useCallback` for performance
4. **Lazy loading** - Code-split routes and heavy components
5. **Error boundaries** - Catch and handle errors gracefully
6. **Accessibility** - Use semantic HTML and ARIA labels
7. **Responsive design** - Mobile-first approach
8. **Code splitting** - Optimize bundle size
9. **State management** - Keep state close to where it's used
10. **Testing** - Write tests for critical functionality
