import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { useHuntStore } from '@/stores/huntStore';
import { useForm } from '@inertiajs/react';
import { ImageIcon, Loader2, PlusCircleIcon } from 'lucide-react';
import { ChangeEvent, FormEvent, useState } from 'react';
import hunts from '@/routes/hunts';

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

    const shareHunt = (e: FormEvent) => {
        e.preventDefault();
        post(hunts.store.url(), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                toast({
                    description: 'Hunt partilhada com sucesso.',
                });
                setData('content', '');
                setImagePreview([]);
                setIsFloatCreateHuntOpen(false);
            },
        });
    };

    return (
        <Card className="gradient mx-auto w-full max-w-xl">
            <CardContent className="flex items-start gap-4">
                <form className="relative flex w-full flex-col" onSubmit={shareHunt} encType="multipart/form-data">
                    <textarea
                        name="content"
                        value={data.content}
                        onChange={(e) => setData('content', e.target.value)}
                        rows={3}
                        maxLength={500}
                        placeholder="Escreva algo interessante…"
                        className="w-full border-none p-3 ring-0 outline-none focus:border-none focus:ring-0 focus:outline-none focus-visible:outline-none"
                    />
                    <span className="text-right text-sm text-gray-500">{data.content.length}/500</span>
                    <InputError message={errors.content} />
                    {imagePreview && (
                        <div className="mt-2 grid grid-cols-1 gap-2">
                            {imagePreview.map((src, index) => (
                                <img
                                    key={index}
                                    src={src}
                                    alt={`Preview ${index}`}
                                    className="effect max-h-50 w-full rounded-xl border-2 object-cover shadow-lg transition-all duration-300 hover:scale-105"
                                />
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
