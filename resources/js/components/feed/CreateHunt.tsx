import InputError from '@/components/input-error';
import { MarkdownEditor } from '@/components/markdown/MarkdownEditor';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useSanitizeImageUrls } from '@/hooks/use-sanitize-image-url';
import { useToast } from '@/hooks/use-toast';
import hunts from '@/routes/hunts';
import { useHuntStore } from '@/stores/huntStore';
import { useForm } from '@inertiajs/react';
import { ImageIcon, Loader2, PlusCircleIcon, X } from 'lucide-react';
import { ChangeEvent, FormEvent, useState } from 'react';

interface HuntForm {
    content: string;
    image: File | string | null;
}

export function CreateHunt() {
    const { setIsFloatCreateHuntOpen } = useHuntStore();
    const { errors, processing, post, data, setData } = useForm<Required<HuntForm>>({
        content: '',
        image: '',
    });

    const { toast } = useToast();

    const [imagePreview, setImagePreview] = useState<string[]>([]);
    const sanitizedImageUrls = useSanitizeImageUrls(imagePreview);

    const handleImageChange = (e: ChangeEvent<HTMLInputElement>) => {
        setImagePreview([]);
        const files = e.target.files;
        const file = e.target.files?.[0];

        if (file && file.size > 400 * 1024) {
            toast({
                description: 'O ficheiro é demasiado grande. O tamanho máximo é de 400 KB.',
            });
            return;
        }

        if (file) {
            setData('image', file);
        }

        if (files) {
            const fileArray = Array.from(files).map((file) => URL.createObjectURL(file));
            setImagePreview((prev) => [...prev, ...fileArray]);
        }
    };

    const handleRemoveImage = () => {
        imagePreview.forEach((url) => URL.revokeObjectURL(url));

        setImagePreview([]);
        setData('image', '');

        const fileInput = document.getElementById('image-upload') as HTMLInputElement;
        if (fileInput) {
            fileInput.value = '';
        }
    };

    const shareHunt = (e: FormEvent) => {
        e.preventDefault();
        post(hunts.store.url(), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                toast({
                    description: 'Hunt partilhada com sucesso.',
                });

                imagePreview.forEach((url) => URL.revokeObjectURL(url));

                setData('content', '');
                setData('image', '');
                setImagePreview([]);
                setIsFloatCreateHuntOpen(false);

                const fileInput = document.getElementById('image-upload') as HTMLInputElement;
                if (fileInput) {
                    fileInput.value = '';
                }
            },
        });
    };

    return (
        <Card className="gradient mx-auto w-full max-w-2xl">
            <CardContent className="flex items-start gap-4">
                <form className="relative flex w-full flex-col" onSubmit={shareHunt} encType="multipart/form-data">
                    <MarkdownEditor
                        value={data.content}
                        onChange={(e) => setData('content', e.target.value)}
                        maxLength={500}
                        name="content"
                        rows={3}
                    />
                    <InputError message={errors.content} />
                    {sanitizedImageUrls.length > 0 && (
                        <div className="mt-2 grid grid-cols-1 gap-2">
                            {sanitizedImageUrls.map((src, index) => (
                                <div key={index} className="relative">
                                    <img
                                        src={src}
                                        alt={`Preview ${index}`}
                                        className="max-h-50 w-full rounded-xl border-2 object-cover shadow-lg transition-all duration-300"
                                    />
                                    <button
                                        type="button"
                                        onClick={handleRemoveImage}
                                        className="absolute top-2 right-2 rounded-full bg-red-500 p-1.5 text-white shadow-lg transition-all duration-200 hover:scale-110 hover:bg-red-600"
                                        title="Remover imagem"
                                    >
                                        <X size={16} />
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                    <div className="mt-4 flex items-center justify-between">
                        <div className="flex items-center gap-3 text-gray-500">
                            <input type="file" accept="image/*" id="image-upload" className="hidden" onChange={handleImageChange} name="image" />
                            <label htmlFor="image-upload" className="cursor-pointer text-xl">
                                <ImageIcon className="h-5 w-5" />
                            </label>
                        </div>
                        <div className="flex-1" />
                        <Button
                            type="submit"
                            disabled={processing || data.content.trim().length === 0}
                            className="disabled:bg-foreground-muted transition-all duration-300"
                        >
                            {processing ? <Loader2 className="animate-spin" size={16} /> : <PlusCircleIcon size={16} />}
                            Partilhar Hunt
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
