import InputError from '@/components/input-error';
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { CharacterCounter } from '@/components/ui/character-counter';
import { CONTENT_LIMITS } from '@/constants/validation';
import { useCharacterCount } from '@/hooks/use-character-count';
import { useSanitizeImageUrls } from '@/hooks/use-sanitize-image-url';
import { useToast } from '@/hooks/use-toast';
import { cn } from '@/lib/utils';
import hunts from '@/routes/hunts';
import { useHuntStore } from '@/stores/huntStore';
import { useForm } from '@inertiajs/react';
import { CheckCircle2, ImageIcon, Loader2, Sparkles, X } from 'lucide-react';
import { type ChangeEvent, type FormEvent, useState } from 'react';

interface HuntForm {
    content: string;
    image: File | string | null;
}

export function CreateHunt() {
    const { setIsFloatCreateHuntOpen } = useHuntStore();
    const { errors, processing, post, data, setData } = useForm<
        Required<HuntForm>
    >({
        content: '',
        image: '',
    });

    const { toast } = useToast();

    const [imagePreview, setImagePreview] = useState<string[]>([]);
    const [isSuccess, setIsSuccess] = useState(false);
    const [isFocused, setIsFocused] = useState(false);
    const sanitizedImageUrls = useSanitizeImageUrls(imagePreview);

    // Use shared character count logic
    const characterCount = useCharacterCount({
        content: data.content || '',
        maxLength: CONTENT_LIMITS.HUNT,
        trim: true,
    });

    const hasImage = imagePreview.length > 0;
    const canSubmit =
        (characterCount.hasContent || hasImage) && !characterCount.isOverLimit;

    const handleImageChange = (e: ChangeEvent<HTMLInputElement>) => {
        setImagePreview([]);
        const files = e.target.files;
        const file = e.target.files?.[0];

        if (file && file.size > 2048 * 1024) {
            toast({
                variant: 'destructive',
                title: 'Imagem muito grande',
                description:
                    'O ficheiro é demasiado grande. O tamanho máximo é de 2 MB.',
            });
            return;
        }

        if (file) {
            setData('image', file);
        }

        if (files) {
            const fileArray = Array.from(files).map((file) =>
                URL.createObjectURL(file),
            );
            setImagePreview((prev) => [...prev, ...fileArray]);
        }
    };

    const handleRemoveImage = () => {
        imagePreview.forEach((url) => URL.revokeObjectURL(url));

        setImagePreview([]);
        setData('image', '');

        const fileInput = document.getElementById(
            'image-upload',
        ) as HTMLInputElement;
        if (fileInput) {
            fileInput.value = '';
        }
    };

    const shareHunt = (e: FormEvent) => {
        e.preventDefault();

        if (characterCount.isOverLimit) {
            toast({
                variant: 'destructive',
                title: 'Texto muito longo',
                description: `O conteúdo excede o limite de ${CONTENT_LIMITS.HUNT} caracteres.`,
            });
            return;
        }

        post(hunts.store.url(), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setIsSuccess(true);

                setTimeout(() => {
                    setIsSuccess(false);
                    imagePreview.forEach((url) => URL.revokeObjectURL(url));

                    setData('content', '');
                    setData('image', '');
                    setImagePreview([]);
                    setIsFloatCreateHuntOpen(false);

                    const fileInput = document.getElementById(
                        'image-upload',
                    ) as HTMLInputElement;
                    if (fileInput) {
                        fileInput.value = '';
                    }
                }, 2000);
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    title: 'Erro ao partilhar',
                    description:
                        'Ocorreu um erro ao partilhar a hunt. Tente novamente.',
                });
            },
        });
    };

    return (
        <Card
            className={cn(
                'gradient mx-auto w-full max-w-2xl transition-all duration-300',
                isFocused &&
                    'shadow-md ring-1 ring-zinc-300 dark:ring-zinc-700',
                isSuccess &&
                    'shadow-lg ring-2 shadow-emerald-500/20 ring-emerald-500/50',
            )}
        >
            <CardContent className="p-0">
                {isSuccess ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center duration-300 animate-in fade-in zoom-in">
                        <div className="mb-4 rounded-full bg-emerald-500/10 p-4">
                            <CheckCircle2 className="h-12 w-12 text-emerald-500 duration-500 animate-in zoom-in" />
                        </div>
                        <h3 className="text-xl font-semibold text-emerald-600 dark:text-emerald-400">
                            Hunt Partilhado!
                        </h3>
                        <p className="mt-2 text-sm text-muted-foreground">
                            O seu hunt está agora visível para todos
                        </p>
                    </div>
                ) : (
                    <form
                        className="relative flex w-full flex-col"
                        onSubmit={shareHunt}
                        encType="multipart/form-data"
                        onFocus={() => setIsFocused(true)}
                        onBlur={() => setIsFocused(false)}
                    >
                        <div className="p-4 sm:p-6">
                            <MarkdownEditor
                                value={data.content}
                                onChange={(e) => setData('content', e.target.value)}
                                maxLength={CONTENT_LIMITS.HUNT}
                                name="content"
                                rows={4}
                                hideCounter={true}
                                placeholder="O que descobriu hoje? Partilhe insights, conquistas ou desafios interessantes..."
                                renderMobileControls={(controls) => (
                                    <div className="mb-3 flex items-center justify-between gap-2">
                                        <div className="flex items-center gap-1">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                id="image-upload-mobile"
                                                className="hidden"
                                                onChange={handleImageChange}
                                                name="image"
                                            />
                                            <label
                                                htmlFor="image-upload-mobile"
                                                className="cursor-pointer rounded-lg p-2 transition-all hover:scale-105 hover:bg-muted active:scale-95 active:bg-muted/80"
                                                aria-label="Carregar imagem"
                                            >
                                                <ImageIcon className="h-5 w-5" />
                                            </label>
                                            {controls.emojiPicker}
                                            {controls.markdownHelp}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            {controls.editButton}
                                            {controls.previewButton}
                                        </div>
                                    </div>
                                )}
                            />

                            {/* Character count and progress */}
                            <CharacterCounter
                                count={characterCount.count}
                                max={characterCount.max}
                                progressPercentage={characterCount.progressPercentage}
                                isNearLimit={characterCount.isNearLimit}
                                isOverLimit={characterCount.isOverLimit}
                                variant="with-progress"
                                className="mt-3"
                            />

                            <InputError
                                message={errors.content}
                                className="mt-2 text-sm"
                            />

                            {/* Image preview */}
                            {sanitizedImageUrls.length > 0 && (
                                <div className="mt-4 duration-300 animate-in fade-in slide-in-from-bottom-4">
                                    {sanitizedImageUrls.map((src, index) => (
                                        <div
                                            key={index}
                                            className="group relative overflow-hidden rounded-xl"
                                        >
                                            <img
                                                src={src}
                                                alt={`Preview ${index}`}
                                                className="max-h-96 w-full rounded-xl border-2 border-border object-cover shadow-lg transition-transform duration-300 group-hover:scale-[1.02]"
                                            />
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                                            <button
                                                type="button"
                                                onClick={handleRemoveImage}
                                                className="absolute top-3 right-3 rounded-full bg-red-500 p-2 text-white shadow-lg transition-all duration-200 hover:scale-110 hover:bg-red-600 active:scale-95"
                                                title="Remover imagem"
                                                aria-label="Remover imagem"
                                            >
                                                <X size={18} />
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Actions footer */}
                        <div className="flex flex-col gap-3 border-t px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div className="hidden items-center gap-2 sm:flex">
                                <input
                                    type="file"
                                    accept="image/*"
                                    id="image-upload"
                                    className="hidden"
                                    onChange={handleImageChange}
                                    name="image"
                                />
                                <label
                                    htmlFor="image-upload"
                                    className={cn(
                                        'flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:scale-105 hover:bg-muted active:scale-95 active:bg-muted/80',
                                        imagePreview.length > 0 &&
                                            'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300',
                                    )}
                                    aria-label="Carregar imagem"
                                >
                                    <ImageIcon className="h-4 w-4" />
                                    {imagePreview.length > 0
                                        ? 'Alterar imagem'
                                        : 'Adicionar imagem'}
                                </label>

                                {imagePreview.length > 0 && (
                                    <span className="text-xs text-muted-foreground duration-300 animate-in fade-in slide-in-from-left-2">
                                        1 imagem selecionada
                                    </span>
                                )}
                            </div>

                            <Button
                                type="submit"
                                disabled={processing || !canSubmit}
                                size="lg"
                                className={cn(
                                    'w-full gap-2 bg-gradient-to-r from-purple-500 to-purple-700 transition-all duration-300 hover:from-purple-600 hover:to-purple-800 hover:shadow-lg hover:shadow-purple-500/30 active:scale-95 disabled:opacity-50 sm:w-auto',
                                    processing && 'animate-pulse',
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
                        </div>
                    </form>
                )}
            </CardContent>
        </Card>
    );
}
