<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('Markdown Content Support', function () {
    it('should create a hunt with basic markdown', function () {
        $markdownContent = '# Hello World

This is a **bold** text and this is *italic*.';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $this->assertDatabaseHas('hunts', [
            'content' => $markdownContent,
            'owner_id' => $this->user->id,
        ]);
    });

    it('should create a hunt with markdown links', function () {
        $markdownContent = 'Check out my [GitHub profile](https://github.com/username)!';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toBe($markdownContent);
    });

    it('should create a hunt with inline code', function () {
        $markdownContent = 'Use `console.log()` to debug your code.';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('`console.log()`');
    });

    it('should create a hunt with code blocks', function () {
        $markdownContent = '```javascript
function hello() {
  console.log("Hello World");
}
```';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('```javascript');
        expect($hunt->content)->toContain('function hello()');
    });

    it('should create a hunt with lists', function () {
        $markdownContent = '## My favorite languages:

- JavaScript
- TypeScript
- PHP

1. Laravel
2. React
3. Vue';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('- JavaScript');
        expect($hunt->content)->toContain('1. Laravel');
    });

    it('should create a hunt with checkboxes', function () {
        $markdownContent = 'Todo list:

- [ ] Learn React
- [x] Learn Laravel
- [ ] Build an app';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('- [ ]');
        expect($hunt->content)->toContain('- [x]');
    });

    it('should create a hunt with blockquotes', function () {
        $markdownContent = '> Programming is not about what you know; it is about what you can figure out.';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toStartWith('>');
    });

    it('should create a hunt with emojis', function () {
        $markdownContent = 'Just deployed my app! 🚀🎉

Features:
- Real-time chat 💬
- User profiles 👤
- Dark mode 🌙';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('🚀');
        expect($hunt->content)->toContain('💬');
    });

    it('should create a hunt with horizontal rules', function () {
        $markdownContent = 'Section 1

---

Section 2';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('---');
    });
});

describe('Markdown Content Validation', function () {
    it('should reject content exceeding max length', function () {
        $markdownContent = str_repeat('a', 501);

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertSessionHasErrors(['content']);
    });

    it('should accept content at exact max length', function () {
        $markdownContent = str_repeat('a', 500);

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $this->assertDatabaseHas('hunts', [
            'content' => $markdownContent,
        ]);
    });

    it('should reject empty markdown content', function () {
        $response = $this->post(route('hunts.store'), [
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['content']);
    });

    it('should reject content with only whitespace', function () {
        $response = $this->post(route('hunts.store'), [
            'content' => '   ',
        ]);

        $response->assertSessionHasErrors(['content']);
    });
});

describe('Complex Markdown Structures', function () {
    it('should create a hunt with mixed markdown elements', function () {
        $markdownContent = '# Project Update 🚀

## What I built

I created a **real-time chat** application using:

- Laravel for backend
- React for frontend
- WebSockets for real-time

### Code Sample

```php
Route::post("/messages", function (Request $request) {
    broadcast(new MessageSent($request->message));
});
```

> "Make it work, make it right, make it fast."

Check the [live demo](https://example.com)!';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('# Project Update');
        expect($hunt->content)->toContain('```php');
        expect($hunt->content)->toContain('- Laravel');
        expect($hunt->content)->toContain('[live demo]');
    });

    it('should handle nested markdown structures', function () {
        $markdownContent = '## Project Checklist

1. **Backend**
   - [x] API routes
   - [x] Authentication
   - [ ] Testing

2. **Frontend**
   - [x] Components
   - [ ] State management
   - [ ] E2E tests';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('1. **Backend**');
        expect($hunt->content)->toContain('- [x]');
    });
});

describe('Markdown with Special Characters', function () {
    it('should handle markdown with special characters', function () {
        $markdownContent = 'Testing special chars: < > & " \' @ # $ % ^ * ( )';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toBe($markdownContent);
    });

    it('should handle markdown with unicode characters', function () {
        $markdownContent = 'Unicode test: café, naïve, 日本語, 中文';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('café');
        expect($hunt->content)->toContain('日本語');
    });

    it('should handle markdown with line breaks', function () {
        $markdownContent = "Line 1\n\nLine 2\n\nLine 3";

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toBe($markdownContent);
    });
});

describe('Markdown Table Support', function () {
    it('should create a hunt with markdown tables', function () {
        $markdownContent = '| Framework | Language | Type |
|-----------|----------|------|
| Laravel   | PHP      | Backend |
| React     | JavaScript | Frontend |
| Vue       | JavaScript | Frontend |';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        expect($hunt->content)->toContain('| Framework');
        expect($hunt->content)->toContain('| Laravel');
    });
});

describe('Markdown Sanitization', function () {
    it('should preserve markdown but escape dangerous content on display', function () {
        // The content is stored as-is, but should be sanitized when rendered
        $markdownContent = 'Check this out: <script>alert("xss")</script>';

        $response = $this->post(route('hunts.store'), [
            'content' => $markdownContent,
        ]);

        $response->assertRedirect(route('hunts.index'));

        $hunt = Hunt::where('owner_id', $this->user->id)->first();
        // Content is stored as-is
        expect($hunt->content)->toContain('<script>');

        // But when rendered, it should be sanitized by rehype-sanitize
        // This is handled by the frontend MarkdownRenderer component
    });
});
