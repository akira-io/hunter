# Markdown Editor

## Overview

Hunter's **Markdown Editor** is a robust and complete component for creating formatted content. It provides an intuitive editing experience with Markdown support, real-time preview, emoji insertion, and contextual help.

## Components

The editor consists of 4 main components:

### 1. MarkdownEditor
The main component that orchestrates all editing functionality.

### 2. MarkdownRenderer
Renders Markdown content with proper formatting.

### 3. EmojiPicker
Emoji selector organized by categories.

### 4. MarkdownHelp
Quick Markdown syntax guide with interactive examples.

## Installation

### Dependencies

The editor uses the following libraries:

```json
{
  "@uiw/react-textarea-code-editor": "^3.0.2",
  "react-markdown": "^9.0.1",
  "remark-gfm": "^4.0.0",
  "rehype-sanitize": "^6.0.0"
}
```

### Import

```tsx
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
```

## Basic Usage

### Simple Example

```tsx
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
import { useState } from 'react';

function CreatePost() {
  const [content, setContent] = useState('');

  return (
    <MarkdownEditor
      value={content}
      onChange={(e) => setContent(e.target.value)}
      maxLength={500}
      placeholder="Write something interesting…"
    />
  );
}
```

### With Inertia Form

```tsx
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
import { useForm } from '@inertiajs/react';

function CreateHunt() {
  const { data, setData, post } = useForm({
    content: '',
  });

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    post('/hunts');
  };

  return (
    <form onSubmit={handleSubmit}>
      <MarkdownEditor
        value={data.content}
        onChange={(e) => setData('content', e.target.value)}
        maxLength={500}
        name="content"
      />
      <button type="submit">Publish</button>
    </form>
  );
}
```

## Properties

### MarkdownEditor Props

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `value` | `string` | - | **Required**. Current editor content |
| `onChange` | `(e: ChangeEvent<HTMLTextAreaElement>) => void` | - | **Required**. Callback when content changes |
| `maxLength` | `number` | `500` | Maximum number of characters |
| `placeholder` | `string` | `'Write something interesting…'` | Placeholder text |
| `rows` | `number` | `3` | Initial number of rows |
| `name` | `string` | - | Field name for forms |

### MarkdownRenderer Props

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `content` | `string` | - | **Required**. Markdown content to render |
| `className` | `string` | `''` | Additional CSS classes |

## Features

### 1. Edit and Preview Tabs

The editor has two tabs:

- **Edit**: Edit mode with syntax highlighting
- **Preview**: Real-time preview of formatted content

```tsx
// Tabs are managed automatically
// State is maintained internally using the useMarkdownEditor hook
```

### 2. Character Limit Indicator

The editor shows a character counter that changes color based on the limit:

- **Gray** (0-90%): Normal usage
- **Purple** (90-100%): Approaching limit
- **Red** (>100%): Over limit

```tsx
<MarkdownEditor
  value={content}
  onChange={setContent}
  maxLength={500}  // Set the limit
/>
```

### 3. Emoji Picker

The **EmojiPicker** offers emojis organized into 6 categories:

#### Available Categories

1. **Happy Faces** (13 emojis)
   - 😀 😃 😄 😁 😆 😅 🤣 😂 🙂 🙃 😉 😊 😇

2. **Love** (15 emojis)
   - 🥰 😍 🤩 😘 😗 😚 😙 ❤️ 🧡 💛 💚 💙 💜 💔 💕

3. **Expressions** (13 emojis)
   - 🤔 🤨 😐 😑 😏 😒 🙄 😬 🤥 😌 😔 😪 😴

4. **Negative Faces** (17 emojis)
   - 😕 😟 🙁 ☹️ 😮 😯 😲 😳 🥺 😢 😭 😱 😖 😞 😤 😡 😠

5. **Hands** (22 emojis)
   - 👋 🤚 ✋ 👌 ✌️ 🤞 🤟 🤘 🤙 👈 👉 👆 👇 👍 👎 ✊ 👊 👏 🙌 🤝 🙏 💪

6. **Symbols** (12 emojis)
   - 🔥 ⭐ 🌟 ✨ 💫 ⚡ 💥 🌈 ☀️ 🌙 🌊 💧

#### Picker Features

- **Search**: Search field by emoji name
- **Categorization**: Emojis organized by category
- **Quick insertion**: Click to insert at cursor
- **Responsive**: Adapts to different screen sizes

```tsx
// EmojiPicker is automatically integrated into MarkdownEditor
// No additional configuration needed
```

### 4. Markdown Help Guide

The **MarkdownHelp** provides clickable syntax examples:

#### Headings

```markdown
# Heading 1
## Heading 2
### Heading 3
```

#### Text Formatting

```markdown
**bold**
*italic*
~~strikethrough~~
```

#### Lists

```markdown
- Unordered item
1. Numbered item
- [ ] Task (checkbox)
```

#### Links and Code

```markdown
[link text](url)
`inline code`
```
code block
```
```

#### Quotes and Separators

```markdown
> Quote
---
```

### 5. Automatic Code Detection

The editor automatically detects when you paste code and formats it appropriately:

#### Detected Languages

- PHP
- JavaScript/TypeScript
- JSX/TSX (React)
- HTML
- CSS/SCSS
- JSON
- SQL
- Bash/Shell
- Python

#### Detection Example

```tsx
// When pasting this code:
function hello() {
  console.log('Hello World');
}

// The editor automatically adds:
```javascript
function hello() {
  console.log('Hello World');
}
```
```

### 6. Smart Formatting

The editor offers smart formatting for selected text:

#### Wrap

Select text and click a format to wrap it:

- **Bold**: `**text**`
- **Italic**: `*text*`
- **Strikethrough**: `~~text~~`
- **Link**: `[text](url)`
- **Inline code**: `` `text` ``

#### Prefix

Adds prefix to each selected line:

- **Heading**: `# `
- **List**: `- `
- **Quote**: `> `
- **Numbered**: `1. `

## Rendering

### MarkdownRenderer

The `MarkdownRenderer` transforms Markdown into styled HTML:

#### Supported Elements

1. **Headings** (h1-h6)
2. **Paragraphs**
3. **Links** (open in new tab)
4. **Inline and block code**
5. **Ordered and unordered lists**
6. **Checkboxes** (`- [ ]` and `- [x]`)
7. **Quotes** (blockquote)
8. **Images**
9. **Tables**
10. **Horizontal separators**
11. **Formatting** (bold, italic, strikethrough)

#### Usage Example

```tsx
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';

function DisplayContent({ content }) {
  return (
    <div className="prose">
      <MarkdownRenderer content={content} />
    </div>
  );
}
```

#### Code with Syntax Highlighting

```tsx
// MarkdownRenderer uses @uiw/react-textarea-code-editor
// to highlight code in blocks

const markdown = `
\`\`\`javascript
function greet(name) {
  console.log(\`Hello, \${name}!\`);
}
\`\`\`
`;

<MarkdownRenderer content={markdown} />
```

### Tables

The renderer supports GFM (GitHub Flavored Markdown) tables:

```markdown
| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| Value 1  | Value 2  | Value 3  |
| Value 4  | Value 5  | Value 6  |
```

#### Table Styles

- Rounded borders
- Row hover effects
- Highlighted header
- Responsive with horizontal scroll

### Interactive Checkboxes

```markdown
- [ ] Incomplete task
- [x] Completed task
```

Renders visual checkboxes (readonly).

## Custom Hooks

### useMarkdownEditor

Hook that manages all editor state and logic:

```tsx
import { useMarkdownEditor } from '@/hooks/useMarkdownEditor';

const {
  editorRef,        // Ref to the textarea
  activeTab,        // 'edit' or 'preview'
  setActiveTab,     // Changes active tab
  handlePaste,      // Handles paste event
  insertText,       // Inserts formatted text
  insertEmoji,      // Inserts emoji
} = useMarkdownEditor({ value, onChange, name });
```

#### Hook Features

1. **Code detection** on paste
2. **Automatic formatting** of indentation
3. **Text insertion** at cursor
4. **Wrap and prefix** for selected text
5. **Ref management** for editor

## Responsiveness

The editor is fully responsive:

### Mobile (< 640px)

- Tabs take full width
- Compact help buttons (icons only)
- Emoji grid: 8 columns
- Help popover: 280px

### Tablet (640px - 1024px)

- Hybrid layout
- Buttons with visible text
- Emoji grid: 8 columns
- Help popover: 320px

### Desktop (> 1024px)

- Full horizontal layout
- All labels visible
- Emoji grid: 8 columns
- Help popover: 380px

### Responsive Classes Example

```tsx
<TabsList className="w-full sm:w-auto">
  <TabsTrigger value="edit" className="flex-1 sm:flex-initial">
    <Pencil className="h-4 w-4" />
    <span className="ml-1.5">Edit</span>
  </TabsTrigger>
</TabsList>

<Button variant="ghost" size="sm">
  <Smile className="h-4 w-4" />
  <span className="ml-1 hidden sm:inline">Emoji</span>
</Button>
```

## Theming and Styling

### CSS Variables

The editor uses the project's CSS variables:

```css
--color-background
--color-foreground
--color-muted
--color-muted-foreground
--color-primary
--color-primary-foreground
--color-border
--color-accent
--color-accent-foreground
```

### Dark Mode

The editor automatically adapts to the theme:

```tsx
// Light mode
<div className="bg-muted text-foreground">
  
// Dark mode (automatic)
<div className="dark:bg-muted dark:text-foreground">
```

### Visual States

#### Focus

```css
focus-within:border-primary
focus-within:ring-2
focus-within:ring-primary/20
```

#### Hover

```css
hover:bg-accent
hover:text-accent-foreground
```

#### Transitions

```css
transition-colors
transition-all
```

## Security

### Sanitization

Content is sanitized using `rehype-sanitize`:

```tsx
import rehypeSanitize from 'rehype-sanitize';

<ReactMarkdown
  rehypePlugins={[rehypeSanitize]}
>
  {content}
</ReactMarkdown>
```

### Scripts and Dangerous HTML

- `<script>` tags are removed
- `on*` attributes are removed
- Inline HTML is escaped
- `javascript:` links are blocked

### Content Validation

```php
// Backend validation
$request->validate([
    'content' => 'required|string|max:500',
]);
```

## Performance

### Optimizations

1. **Lazy Loading**: Components loaded on demand
2. **Debouncing**: Preview updates with delay
3. **Memoization**: Memoized components
4. **Code Splitting**: Optimized bundle

### Bundle Size

- MarkdownEditor: ~15KB
- EmojiPicker: ~8KB
- MarkdownHelp: ~3KB
- MarkdownRenderer: ~25KB
- **Total**: ~51KB (gzipped)

## Accessibility

### ARIA

```tsx
<button
  type="button"
  aria-label="Insert emoji"
  title="Choose emoji"
>
  <Smile />
</button>
```

### Keyboard Navigation

- **Tab**: Navigate between elements
- **Shift + Tab**: Navigate backwards
- **Enter**: Activate buttons
- **Escape**: Close popovers

### Screen Readers

All buttons have descriptive labels:

```tsx
<TabsTrigger value="edit">
  <Pencil className="h-4 w-4" />
  <span className="ml-1.5">Edit</span>
</TabsTrigger>
```

## Advanced Examples

### Editor with Validation

```tsx
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
import { useState } from 'react';

function ValidatedEditor() {
  const [content, setContent] = useState('');
  const [error, setError] = useState('');

  const handleChange = (e: ChangeEvent<HTMLTextAreaElement>) => {
    const value = e.target.value;
    setContent(value);

    if (value.length > 500) {
      setError('500 character limit exceeded');
    } else if (value.length < 10) {
      setError('Minimum 10 characters required');
    } else {
      setError('');
    }
  };

  return (
    <div>
      <MarkdownEditor
        value={content}
        onChange={handleChange}
        maxLength={500}
      />
      {error && (
        <p className="text-destructive text-sm mt-2">{error}</p>
      )}
    </div>
  );
}
```

### Editor with Auto-save

```tsx
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
import { useState, useEffect } from 'react';
import { useDebounce } from '@/hooks/useDebounce';

function AutoSaveEditor() {
  const [content, setContent] = useState('');
  const debouncedContent = useDebounce(content, 1000);

  useEffect(() => {
    if (debouncedContent) {
      // Save to localStorage or API
      localStorage.setItem('draft', debouncedContent);
    }
  }, [debouncedContent]);

  return (
    <MarkdownEditor
      value={content}
      onChange={(e) => setContent(e.target.value)}
      placeholder="Your content is saved automatically…"
    />
  );
}
```

### Editor with Custom Preview

```tsx
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { useState } from 'react';

function CustomPreviewEditor() {
  const [content, setContent] = useState('');
  const [showPreview, setShowPreview] = useState(false);

  return (
    <div>
      <MarkdownEditor
        value={content}
        onChange={(e) => setContent(e.target.value)}
      />
      
      <button onClick={() => setShowPreview(!showPreview)}>
        {showPreview ? 'Hide' : 'Show'} External Preview
      </button>

      {showPreview && (
        <div className="mt-4 border rounded-lg p-4">
          <h3 className="font-semibold mb-2">Custom Preview</h3>
          <MarkdownRenderer content={content} />
        </div>
      )}
    </div>
  );
}
```

## Troubleshooting

### Common Issues

#### 1. Preview not updating

**Problem**: Preview doesn't show changes

**Solution**: Make sure `value` is being updated correctly

```tsx
// ❌ Wrong
<MarkdownEditor value={content} onChange={setContent} />

// ✅ Correct
<MarkdownEditor 
  value={content} 
  onChange={(e) => setContent(e.target.value)} 
/>
```

#### 2. Emojis not appearing

**Problem**: Emojis are not displayed

**Solution**: Check if the font supports emojis

```css
/* Add to CSS */
body {
  font-family: system-ui, -apple-system, 'Segoe UI', Emoji;
}
```

#### 3. Code has no highlighting

**Problem**: Code blocks have no syntax highlighting

**Solution**: Specify the language in markdown

```markdown
```javascript  ← Add the language
function hello() {
  console.log('Hello');
}
```
```

#### 4. Styles not applied

**Problem**: Rendered content has no styles

**Solution**: Wrap renderer with prose classes

```tsx
<div className="prose dark:prose-invert">
  <MarkdownRenderer content={content} />
</div>
```

## Best Practices

### 1. Validation

Always validate content on the backend:

```php
$request->validate([
    'content' => 'required|string|max:500|min:10',
]);
```

### 2. Sanitization

Use `rehype-sanitize` for security:

```tsx
import rehypeSanitize from 'rehype-sanitize';
```

### 3. Character Limits

Set reasonable limits:

- Short posts: 500 characters
- Comments: 200 characters
- Articles: 10,000 characters

### 4. Visual Feedback

Provide clear feedback to users:

```tsx
const isNearLimit = content.length > maxLength * 0.9;
```

### 5. Accessibility

Use labels and ARIA attributes:

```tsx
<button aria-label="Markdown Help">
  <HelpCircle />
</button>
```

### 6. Performance

Use debouncing for expensive operations:

```tsx
const debouncedContent = useDebounce(content, 300);
```

## Testing

### Feature Tests

See `tests/Feature/Hunt/MarkdownContentTest.php` for comprehensive examples:

```php
it('should create a hunt with markdown content', function () {
    $markdownContent = '# Hello World

This is **bold** text.';

    $response = $this->post(route('hunts.store'), [
        'content' => $markdownContent,
    ]);

    $response->assertRedirect(route('hunts.index'));
    
    $this->assertDatabaseHas('hunts', [
        'content' => $markdownContent,
    ]);
});
```

### Unit Tests

See `tests/Unit/Models/MarkdownContentTest.php` for model-level tests:

```php
it('can store markdown content', function () {
    $markdownContent = '# Test Title

This is **bold** and *italic*.';

    $hunt = Hunt::factory()->create([
        'content' => $markdownContent,
    ]);

    expect($hunt->content)->toBe($markdownContent);
});
```

## Changelog

### Version 2.0.0 (Current - Component Version)

> **Note**: This is the Markdown Editor component version. For the application version, see the main CHANGELOG.md

#### ✨ New Features

- ✅ Emojis organized by categories (6 categories, 92+ emojis)
- ✅ Emoji search by name
- ✅ Checkbox support in lists (`- [ ]` and `- [x]`)
- ✅ Visual character limit indicator (color-coded)
- ✅ Horizontal separators (`---`)
- ✅ Tables with hover states and responsive design

#### 🎨 Visual Improvements

- ✅ Fully responsive layout (mobile, tablet, desktop)
- ✅ Enhanced focus states with rings and borders
- ✅ Smooth transitions on all interactions
- ✅ Consistent dark mode support
- ✅ Popover with controlled state
- ✅ Better spacing and padding

#### 🔧 Technical Improvements

- ✅ TypeScript strict mode compatible
- ✅ Component memoization for performance
- ✅ Optimized bundle size (~51KB gzipped)
- ✅ Enhanced accessibility (ARIA labels, keyboard navigation)
- ✅ Comprehensive test coverage (40 tests, 107 assertions)
- ✅ Security hardening with rehype-sanitize

#### 📚 Documentation

- ✅ Complete English documentation (16,976 characters)
- ✅ Usage examples for all features
- ✅ Troubleshooting guide
- ✅ Best practices section
- ✅ Testing examples

## Related Documentation

- [Hunts (Posts)](./04-hunts.md) - How hunts use markdown
- [Frontend Development](./09-frontend-development.md) - React component architecture
- [Testing](./10-testing.md) - Testing strategies

## Useful Links

- [React Markdown](https://github.com/remarkjs/react-markdown)
- [GitHub Flavored Markdown](https://github.github.com/gfm/)
- [Tailwind CSS](https://tailwindcss.com)
- [TypeScript](https://www.typescriptlang.org)
- [Inertia.js](https://inertiajs.com)

## Support

For issues or questions:

1. Check the documentation
2. Review test examples
3. Open an issue on the repository
4. Contact the team

---

> **Note**: For project version information, see [CHANGELOG.md](../CHANGELOG.md) or [package.json](../package.json)
