# Hunt Creation UX

## Overview

The Hunt creation interface provides a modern, polished experience for sharing content on Hunter. With real-time feedback, visual progress indicators, and smooth animations, creating a Hunt is intuitive and satisfying.

## Recent Improvements (v0.6.0)

### Modern UI/UX Redesign

The `CreateHunt` component has been completely redesigned with:

- **Visual Hierarchy**: Clear separation between editor, metrics, and actions
- **Real-time Feedback**: Character counter with color-coded progress bar
- **Success Animation**: Celebratory feedback when Hunt is published
- **Micro-interactions**: Smooth animations on all interactive elements
- **Gradient Buttons**: Eye-catching purple gradient on submit button
- **Focus States**: Purple ring effect when interacting with the form

### Component Location

```
resources/js/components/feed/CreateHunt.tsx
```

## Features

### 1. Character Counter & Progress Bar

Real-time character counting with visual feedback:

#### States

| Character Count | Color  | State   | Message |
|----------------|--------|---------|---------|
| 0 - 449        | Purple | Normal  | None    |
| 450 - 500      | Amber  | Warning | "Próximo do limite de caracteres" |
| 501+           | Red    | Error   | "O conteúdo excede o limite. Por favor, reduza o texto." |

#### Implementation

```tsx
const maxLength = 500;
const contentLength = data.content.trim().length;
const progressPercentage = (contentLength / maxLength) * 100;
const isNearLimit = contentLength > maxLength * 0.9;
const isOverLimit = contentLength > maxLength;

<Progress 
    value={progressPercentage} 
    className={cn(
        "h-2 transition-all duration-300",
        isOverLimit && "bg-red-200 dark:bg-red-950",
        isNearLimit && !isOverLimit && "bg-amber-200 dark:bg-amber-950"
    )}
    indicatorClassName={cn(
        "transition-colors duration-300",
        isOverLimit && "bg-red-500",
        isNearLimit && !isOverLimit && "bg-amber-500",
        !isNearLimit && "bg-purple-500"
    )}
/>
```

### 2. Layout Structure

Clear visual hierarchy with labeled sections:

```tsx
<Card className="overflow-hidden">
    {/* Header */}
    <div className="flex items-center gap-2">
        <Sparkles className="h-5 w-5 text-purple-500" />
        <h3>Partilhe a sua Hunt</h3>
    </div>

    {/* Editor */}
    <MarkdownEditor 
        placeholder="O que descobriu hoje? Partilhe insights, conquistas ou desafios interessantes..."
    />

    {/* Separator */}
    <div className="my-3 border-t border-border/50" />

    {/* Character Count Section */}
    <div className="space-y-2">
        <div className="flex items-center justify-between">
            <span>Caracteres utilizados</span>
            <span>150 / 500</span>
        </div>
        <Progress />
        {/* Conditional feedback messages */}
    </div>

    {/* Actions */}
    <div className="flex items-center justify-between">
        <label>Adicionar imagem</label>
        <Button>Partilhar Hunt</Button>
    </div>
</Card>
```

### 3. Image Upload

Enhanced image upload with preview and validation:

```tsx
// File validation
if (file && file.size > 400 * 1024) {
    toast({
        variant: 'destructive',
        title: 'Imagem muito grande',
        description: 'O ficheiro é demasiado grande. O tamanho máximo é de 400 KB.',
    });
    return;
}

// Image preview with hover effects
{sanitizedImageUrls.map((src, index) => (
    <div className="group relative">
        <img 
            src={src} 
            className="max-h-96 rounded-xl transition-transform group-hover:scale-[1.02]"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 opacity-0 group-hover:opacity-100" />
        <button 
            onClick={handleRemoveImage}
            className="absolute top-3 right-3 bg-red-500 rounded-full"
        >
            <X size={18} />
        </button>
    </div>
))}
```

**Image Button States:**
- Default: "Adicionar imagem"
- With image: "Alterar imagem" with purple background
- Shows count: "1 imagem selecionada"

### 4. Success State

Animated success feedback before clearing the form:

```tsx
{isSuccess ? (
    <div className="flex flex-col items-center py-8 animate-in fade-in zoom-in">
        <div className="rounded-full bg-emerald-500/10 p-4">
            <CheckCircle2 className="h-12 w-12 text-emerald-500 animate-in zoom-in" />
        </div>
        <h3 className="text-xl font-semibold text-emerald-600">
            Hunt Partilhada!
        </h3>
        <p className="text-sm text-muted-foreground">
            A sua hunt está agora visível para todos
        </p>
    </div>
) : (
    // Normal form
)}
```

**Flow:**
1. User clicks "Partilhar Hunt"
2. Button shows loading state with spinner
3. On success, card transitions to success state
4. After 1.5s, form resets and closes (if in dialog)

### 5. Focus State

Visual feedback when user interacts with the form:

```tsx
const [isFocused, setIsFocused] = useState(false);

<Card 
    className={cn(
        "transition-all duration-300",
        isFocused && "ring-2 ring-purple-500/50 shadow-lg shadow-purple-500/20",
        isSuccess && "ring-2 ring-emerald-500/50 shadow-lg shadow-emerald-500/20"
    )}
>
    <form 
        onFocus={() => setIsFocused(true)}
        onBlur={() => setIsFocused(false)}
    >
```

### 6. Submit Button

Gradient button with multiple states:

```tsx
<Button
    type="submit"
    disabled={processing || contentLength === 0 || isOverLimit}
    className={cn(
        "bg-gradient-to-r from-purple-500 to-purple-700",
        "hover:from-purple-600 hover:to-purple-800",
        "hover:shadow-lg hover:shadow-purple-500/30",
        "active:scale-95",
        processing && "animate-pulse"
    )}
>
    {processing ? (
        <>
            <Loader2 className="h-4 w-4 animate-spin" />
            Partilhando...
        </>
    ) : (
        <>
            <Sparkles className="h-4 w-4" />
            Partilhar Hunt
        </>
    )}
</Button>
```

## Animations

### Tailwind Animate-in

The component uses Tailwind CSS animate-in utilities:

```css
/* Success state */
animate-in fade-in zoom-in duration-300

/* Image preview */
animate-in fade-in slide-in-from-bottom-4 duration-300

/* Image count badge */
animate-in fade-in slide-in-from-left-2 duration-300

/* Feedback messages */
animate-in fade-in slide-in-from-top-1 duration-200
```

### Hover & Active States

```css
/* Buttons and labels */
hover:scale-105 active:scale-95

/* Image preview */
group-hover:scale-[1.02]

/* Button */
hover:shadow-lg hover:shadow-purple-500/30
```

### Processing State

```css
/* Button during submission */
animate-pulse

/* Loader icon */
animate-spin
```

## Validation

### Frontend Validation

```tsx
// Submit handler
const shareHunt = (e: FormEvent) => {
    e.preventDefault();
    
    // Check character limit
    if (isOverLimit) {
        toast({
            variant: 'destructive',
            title: 'Texto muito longo',
            description: `O conteúdo excede o limite de ${maxLength} caracteres.`,
        });
        return;
    }
    
    // Submit via Inertia
    post(hunts.store.url(), { ... });
};
```

### Backend Validation

```php
$request->validate([
    'content' => 'required|string|max:500',
    'image' => 'nullable|image|max:400', // 400 KB
]);
```

## Toast Notifications

Enhanced toast messages with titles and descriptions:

```tsx
// Success
toast({
    title: 'Hunt partilhada! 🎉',
    description: 'A sua hunt foi partilhada com sucesso.',
});

// Error - File too large
toast({
    variant: 'destructive',
    title: 'Imagem muito grande',
    description: 'O ficheiro é demasiado grande. O tamanho máximo é de 400 KB.',
});

// Error - Over character limit
toast({
    variant: 'destructive',
    title: 'Texto muito longo',
    description: `O conteúdo excede o limite de ${maxLength} caracteres.`,
});

// Error - General failure
toast({
    variant: 'destructive',
    title: 'Erro ao partilhar',
    description: 'Ocorreu um erro ao partilhar a hunt. Tente novamente.',
});
```

## Progress Component

Enhanced `Progress` component with indicator customization:

```tsx
// resources/js/components/ui/progress.tsx
interface ProgressProps extends ComponentPropsWithoutRef<typeof ProgressPrimitive.Root> {
    indicatorClassName?: string;
}

const Progress = React.forwardRef<
    React.ElementRef<typeof ProgressPrimitive.Root>,
    ProgressProps
>(({ className, value, indicatorClassName, ...props }, ref) => (
    <ProgressPrimitive.Root
        ref={ref}
        className={cn('h-4 w-full overflow-hidden rounded-full bg-secondary', className)}
        {...props}
    >
        <ProgressPrimitive.Indicator
            className={cn('h-full w-full flex-1 bg-primary transition-all', indicatorClassName)}
            style={{ transform: `translateX(-${100 - (value || 0)}%)` }}
        />
    </ProgressPrimitive.Root>
));
```

**Usage:**

```tsx
<Progress 
    value={75} 
    className="h-2 bg-amber-200"
    indicatorClassName="bg-amber-500"
/>
```

## Accessibility

### Labels & ARIA

```tsx
<label
    htmlFor="image-upload"
    aria-label="Carregar imagem"
>
    <ImageIcon />
</label>

<input
    type="file"
    id="image-upload"
    className="hidden"
    accept="image/*"
/>
```

### Keyboard Navigation

- Tab through all interactive elements
- Enter to submit form
- Escape to close (when in dialog)
- Space to open file picker

### Screen Readers

```tsx
<button
    title="Remover imagem"
    aria-label="Remover imagem"
>
    <X size={18} />
</button>
```

## Responsive Design

### Mobile (< 768px)

- Full-width form
- Stacked layout for buttons
- Larger touch targets (min 44x44px)
- Mobile-specific controls (file picker in toolbar)

```tsx
renderMobileControls={(controls) => (
    <div className="mb-3 flex items-center justify-between">
        <div className="flex items-center gap-1">
            <label htmlFor="image-upload-mobile">
                <ImageIcon />
            </label>
            {controls.emojiPicker}
        </div>
        <div className="flex items-center gap-1">
            {controls.editButton}
            {controls.previewButton}
        </div>
    </div>
)}
```

### Desktop (≥ 768px)

- Horizontal layout for actions
- Persistent toolbar
- Hover states
- Split view (form / preview)

```tsx
<div className="flex items-center justify-between">
    <label htmlFor="image-upload">
        <ImageIcon />
        {imagePreview.length > 0 ? 'Alterar imagem' : 'Adicionar imagem'}
    </label>
    
    <Button type="submit">
        Partilhar Hunt
    </Button>
</div>
```

## Integration

### In Feed Page

```tsx
// resources/js/pages/hunts/hunts.tsx
import { CreateHunt } from '@/components/feed/CreateHunt';

export default function HuntLine({ hunts }) {
    return (
        <AppLayout>
            <div className="flex flex-col gap-4">
                <CreateHunt />
                
                <InfiniteScroll data="hunts">
                    {hunts.data.map(hunt => (
                        <HuntCard key={hunt.id} hunt={hunt} />
                    ))}
                </InfiniteScroll>
            </div>
        </AppLayout>
    );
}
```

### In Floating Dialog

```tsx
// resources/js/components/feed/FloatingCreateHunt.tsx
import { CreateHunt } from '@/components/feed/CreateHunt';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';

export function FloatingCreateHunt() {
    const { isFloatCreateHuntOpen, setIsFloatCreateHuntOpen } = useHuntStore();

    return (
        <Dialog open={isFloatCreateHuntOpen} onOpenChange={setIsFloatCreateHuntOpen}>
            <DialogTrigger>
                <Plus size={24} />
            </DialogTrigger>
            <DialogContent className="w-full max-w-md">
                <CreateHunt />
            </DialogContent>
        </Dialog>
    );
}
```

## Best Practices

### 1. Character Limit Enforcement

Always validate on both frontend and backend:

```tsx
// Frontend: Prevent submission
disabled={contentLength === 0 || isOverLimit}

// Backend: Validate again
'content' => 'required|string|max:500'
```

### 2. Image Size Optimization

```tsx
// Show clear error for large files
if (file.size > 400 * 1024) {
    toast({
        variant: 'destructive',
        title: 'Imagem muito grande',
        description: 'O tamanho máximo é de 400 KB.',
    });
    return;
}
```

### 3. Success Feedback

```tsx
// Show success state before clearing
setTimeout(() => {
    setIsSuccess(false);
    // Clear form
}, 1500);
```

### 4. Clean Up Resources

```tsx
// Revoke object URLs to prevent memory leaks
imagePreview.forEach(url => URL.revokeObjectURL(url));
```

### 5. Preserve Scroll Position

```tsx
post(hunts.store.url(), {
    preserveScroll: true,  // Keep user's position in feed
    forceFormData: true,   // Required for file uploads
});
```

## Performance

### Memoization

```tsx
// Avoid recalculating on every render
const itemsWithBadge = useMemo(() => {
    return mainNavItems.map(item => {
        if (item.title === 'Chat' && totalUnreadCount > 0) {
            return { ...item, badge: totalUnreadCount };
        }
        return item;
    });
}, [totalUnreadCount]);
```

### Debounced Validation

```tsx
// Only validate after user stops typing
const debouncedValidation = useDebounce(data.content, 300);

useEffect(() => {
    // Validate debouncedValidation
}, [debouncedValidation]);
```

## Testing

### Component Tests

```tsx
import { render, screen, fireEvent } from '@testing-library/react';
import { CreateHunt } from './CreateHunt';

it('shows character count', () => {
    render(<CreateHunt />);
    expect(screen.getByText(/0 \/ 500/)).toBeInTheDocument();
});

it('shows warning when near limit', () => {
    const { getByRole } = render(<CreateHunt />);
    const textarea = getByRole('textbox');
    
    fireEvent.change(textarea, { target: { value: 'x'.repeat(460) } });
    
    expect(screen.getByText(/Próximo do limite/)).toBeInTheDocument();
});

it('prevents submission when over limit', () => {
    const { getByRole } = render(<CreateHunt />);
    const textarea = getByRole('textbox');
    const button = screen.getByText(/Partilhar Hunt/);
    
    fireEvent.change(textarea, { target: { value: 'x'.repeat(501) } });
    
    expect(button).toBeDisabled();
});
```

### Integration Tests

```php
it('creates a hunt with valid content', function () {
    $user = User::factory()->create();
    
    actingAs($user)
        ->post('/hunts', [
            'content' => 'My first hunt!',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
    
    expect(Hunt::count())->toBe(1);
});

it('validates content length', function () {
    $user = User::factory()->create();
    
    actingAs($user)
        ->post('/hunts', [
            'content' => str_repeat('x', 501),
        ])
        ->assertSessionHasErrors('content');
});
```

## Related Documentation

- [Hunts (Posts)](./04-hunts.md) - Complete Hunt system documentation
- [Markdown Editor](./16-markdown-editor.md) - Markdown editing features
- [Frontend Development](./09-frontend-development.md) - React & TypeScript guide
- [Testing](./10-testing.md) - Testing guide
