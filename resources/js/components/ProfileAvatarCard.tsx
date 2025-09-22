import { ImagePlusIcon, XIcon } from 'lucide-react';
import AvatarGenerator from 'react-nice-avatar';

import { HunterAlertDialog } from '@/components/core/HunterAlertDialog';
import InputError from '@/components/input-error';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useProfileUpdate } from '@/hooks/profile/useProfileUpdate';

export default function ProfileAvatarCard() {
    const {
        open,
        setOpen,
        fileTooLargeDialogOpen,
        setFileTooLargeDialogOpen,
        fileTooLarge,
        avatarPreview,
        backgroundPreview,
        data,
        setData,
        errors,
        processing,
        config,
        handleFileChange,
        removeImage,
        updateProfile,
    } = useProfileUpdate();

    const sanitizedAvatarPreview = useSanitizeImageUrl(avatarPreview);
    const sanitizedBackgroundPreview = useSanitizeImageUrl(backgroundPreview);

    return (
        <>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogTrigger asChild>
                    <div className="relative cursor-pointer">
                        <div className="h-32 w-auto">
                            {sanitizedAvatarPreview ? (
                                <img src={sanitizedAvatarPreview} alt={data.name} className="mb-4 h-32 w-32 rounded-full object-cover" />
                            ) : (
                                <AvatarGenerator className="mb-4 h-32 w-32 rounded-full object-cover" {...config} />
                            )}
                        </div>
                        <ImagePlusIcon size={16} className="absolute top-0 right-0" />
                    </div>
                </DialogTrigger>
                <DialogContent className="flex flex-col gap-0 overflow-y-visible p-0 sm:max-w-lg">
                    <form className="space-y-4" onSubmit={(e) => updateProfile(e)} encType="multipart/form-data">
                        <DialogHeader className="contents space-y-0 text-left">
                            <DialogTitle className="border-b px-6 py-4 text-base">Editar Perfil</DialogTitle>
                        </DialogHeader>
                        <div className="overflow-y-auto">
                            <div className="h-32">
                                <div className="bg-muted relative flex size-full items-center justify-center overflow-hidden">
                                    {sanitizedBackgroundPreview && (
                                        <img src={sanitizedBackgroundPreview} className="size-full object-cover" alt="Imagem de capa do perfil" />
                                    )}
                                    <div className="absolute inset-0 flex items-center justify-center gap-2">
                                        <label className="flex size-10 cursor-pointer items-center justify-center rounded-full bg-black/60 text-white">
                                            <ImagePlusIcon size={16} />
                                            <input
                                                type="file"
                                                accept="image/*"
                                                onChange={(e) => handleFileChange(e, 'background')}
                                                className="hidden"
                                            />
                                        </label>
                                        {backgroundPreview && (
                                            <button
                                                type="button"
                                                onClick={() => removeImage('background')}
                                                className="flex size-10 items-center justify-center rounded-full bg-black/60 text-white"
                                            >
                                                <XIcon size={16} />
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                            <div className="-mt-10 px-6">
                                <div className="bg-muted relative flex size-20 items-center justify-center overflow-hidden rounded-full border-4 shadow-xs">
                                    {sanitizedAvatarPreview ? (
                                        <img src={sanitizedAvatarPreview} className="size-full object-cover" alt="Imagem de perfil" />
                                    ) : (
                                        <AvatarGenerator className="size-full object-cover" {...config} />
                                    )}
                                    <label className="absolute flex size-8 cursor-pointer items-center justify-center rounded-full bg-black/60 text-white">
                                        <ImagePlusIcon size={16} />
                                        <input type="file" accept="image/*" onChange={(e) => handleFileChange(e, 'avatar')} className="hidden" />
                                    </label>
                                </div>
                            </div>
                            <div className="space-y-4 px-6 pt-4 pb-6">
                                <InputError message={errors.avatar_url} />
                                <InputError message={errors.background_image_url} />
                                <div className="flex flex-col gap-4 sm:flex-row">
                                    <div className="flex-1 space-y-2">
                                        <Label htmlFor="name">Nome</Label>
                                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} name="name" />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="flex-1 space-y-2">
                                        <Label htmlFor="user_name">Nome Utilizador</Label>
                                        <Input
                                            name="user_name"
                                            id="user_name"
                                            value={data.user_name}
                                            onChange={(e) => setData('user_name', e.target.value)}
                                        />
                                        <InputError message={errors.user_name} />
                                    </div>
                                </div>
                                <div className="flex flex-col gap-4 sm:flex-row">
                                    <div className="flex-1 space-y-2">
                                        <Label htmlFor="email">E-mail</Label>
                                        <Input
                                            name="email"
                                            id="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            type="email"
                                        />
                                        <InputError message={errors.email} />
                                    </div>
                                    <div className="flex-1 space-y-2">
                                        <Label htmlFor="location">Localização</Label>
                                        <Input
                                            id="location"
                                            name="location"
                                            value={data.location}
                                            onChange={(e) => setData('location', e.target.value)}
                                        />
                                        <InputError message={errors.location} />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <DialogFooter className="border-t px-6 py-4">
                            <Button type="submit" disabled={processing || fileTooLarge}>
                                {processing ? 'Atualizando...' : 'Salvar'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
            <HunterAlertDialog
                title="Ficheiro muito grande"
                description="O ficheiro selecionado excede o limite de 400KB. Por favor escolha outro."
                open={fileTooLargeDialogOpen}
                onOpenChange={setFileTooLargeDialogOpen}
                onClick={() => setFileTooLargeDialogOpen(false)}
            />
        </>
    );
}
