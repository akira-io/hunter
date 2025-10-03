<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->hunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
    ]);
});

describe('Hunt Markdown Content', function () {
    it('can store markdown content', function () {
        $markdownContent = '# Test Title

This is **bold** and this is *italic*.';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toBe($markdownContent);
        expect($hunt->content)->toContain('# Test Title');
        expect($hunt->content)->toContain('**bold**');
    });

    it('preserves line breaks in markdown', function () {
        $markdownContent = "Line 1\n\nLine 2\n\nLine 3";

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toBe($markdownContent);
        expect($hunt->content)->toMatch('/Line 1\n\nLine 2/');
    });

    it('can store code blocks', function () {
        $markdownContent = '```php
function test() {
    return true;
}
```';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('```php');
        expect($hunt->content)->toContain('function test()');
    });

    it('can store lists in markdown', function () {
        $markdownContent = '- Item 1
- Item 2
- Item 3';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('- Item 1');
        expect($hunt->content)->toContain('- Item 2');
    });

    it('can store links in markdown', function () {
        $markdownContent = '[GitHub](https://github.com)';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toBe($markdownContent);
        expect($hunt->content)->toMatch('/\[GitHub\]\(https:\/\/github\.com\)/');
    });

    it('can store emojis in content', function () {
        $markdownContent = 'Hello World! 🚀 🎉 💻';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('🚀');
        expect($hunt->content)->toContain('🎉');
        expect($hunt->content)->toContain('💻');
    });

    it('can store checkboxes in markdown', function () {
        $markdownContent = '- [ ] Todo item
- [x] Completed item';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('- [ ]');
        expect($hunt->content)->toContain('- [x]');
    });

    it('can store blockquotes in markdown', function () {
        $markdownContent = '> This is a quote';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toStartWith('>');
    });

    it('can store tables in markdown', function () {
        $markdownContent = '| Header 1 | Header 2 |
|----------|----------|
| Cell 1   | Cell 2   |';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('| Header 1');
        expect($hunt->content)->toContain('|----------|');
    });

    it('can store horizontal rules', function () {
        $markdownContent = 'Section 1

---

Section 2';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('---');
    });
});

describe('Markdown Content Length', function () {
    it('respects max length constraint', function () {
        // Assuming max length is 500 characters
        $longContent = str_repeat('a', 500);

        $hunt = Hunt::factory()->create([
            'content' => $longContent,
        ]);

        expect(mb_strlen($hunt->content))->toBe(500);
    });

    it('can store content with markdown that exceeds plain text length', function () {
        // Markdown formatting adds characters but displays less
        $markdownContent = str_repeat('**bold** ', 50); // 9 chars * 50 = 450 chars

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('**bold**');
        expect(mb_strlen($hunt->content))->toBe(mb_strlen($markdownContent));
    });
});

describe('Markdown Special Characters', function () {
    it('can store special HTML characters', function () {
        $markdownContent = 'Testing: < > & " \'';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toBe($markdownContent);
    });

    it('can store unicode characters', function () {
        $markdownContent = 'Testing unicode: café, naïve, 日本語';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('café');
        expect($hunt->content)->toContain('日本語');
    });

    it('can store multiple languages', function () {
        $markdownContent = 'English, Español, Français, 中文, العربية';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('Español');
        expect($hunt->content)->toContain('中文');
    });
});

describe('Complex Markdown Structures', function () {
    it('can store nested markdown structures', function () {
        $markdownContent = '## Title

1. First item
   - Nested item 1
   - Nested item 2
2. Second item

```js
console.log("code");
```';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('## Title');
        expect($hunt->content)->toContain('- Nested item 1');
        expect($hunt->content)->toContain('```js');
    });

    it('can store markdown with multiple code blocks', function () {
        $markdownContent = 'PHP code:

```php
function test() {}
```

JavaScript code:

```javascript
function test() {}
```';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('```php');
        expect($hunt->content)->toContain('```javascript');
    });

    it('can store markdown with mixed formatting', function () {
        $markdownContent = '**Bold with *italic* inside** and ~~strikethrough~~';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        expect($hunt->content)->toContain('**Bold with *italic* inside**');
        expect($hunt->content)->toContain('~~strikethrough~~');
    });
});

describe('Markdown Content Retrieval', function () {
    it('retrieves markdown content unchanged', function () {
        $markdownContent = '# Test

This is **bold**.

```php
$code = true;
```';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        $retrieved = Hunt::find($hunt->id);

        expect($retrieved->content)->toBe($markdownContent);
        expect($retrieved->content)->toContain('# Test');
        expect($retrieved->content)->toContain('```php');
    });

    it('preserves whitespace in code blocks', function () {
        $markdownContent = '```php
function test() {
    if (true) {
        return "test";
    }
}
```';

        $hunt = Hunt::factory()->create([
            'content' => $markdownContent,
        ]);

        $retrieved = Hunt::find($hunt->id);

        expect($retrieved->content)->toBe($markdownContent);
        expect($retrieved->content)->toContain('    if (true)');
    });
});
