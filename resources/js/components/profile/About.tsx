import InputError from '@/components/input-error';
import { ProfileCard } from '@/components/profile-card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/hooks/use-toast';
import profile from '@/routes/profile';
import { useAboutStore } from '@/stores/about';
import type { SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { EditIcon, PlusIcon, UserIcon } from 'lucide-react';
import { FormEventHandler } from 'react';

interface AboutForm {
    bio?: string;
}

export function About() {
    const { auth } = usePage<SharedData>().props;
    const { toast } = useToast();
    const { isOpen, open, close, set } = useAboutStore();

    const { data, setData, patch, errors, processing } = useForm<Required<AboutForm>>({
        bio: auth.user.bio ?? '',
    });

    // const [openBioDialog, setOpenBioDialog] = useState(false);
    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(profile.about().url, {
            preserveScroll: true,
            onFinish: () => {
                close();
                toast({
                    description: 'A sua apresentação foi atualizada com sucesso.',
                });
            },
        });
    };
    return (
        <ProfileCard title="Apresentação" icon={auth?.user.bio ? <EditIcon /> : <PlusIcon />} onClick={open}>
            {!auth.user.bio && (
                <>
                    <UserIcon />
                    Compartilhe um pouco sobre você e suas experiências. Isso ajudará os recrutadores a conhecerem melhor o seu perfil.
                </>
            )}
            <p className="block w-full break-all whitespace-normal dark:text-gray-400">{auth.user.bio}</p>
            <Dialog open={isOpen} onOpenChange={set}>
                <DialogTrigger asChild>
                    <Button>
                        <UserIcon />
                        {auth.user.bio ? 'Atualizar Apresentação' : 'Adicionar Apresentação'}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Apresentação</DialogTitle>
                        <DialogDescription>
                            Adicione uma breve descrição sobre você. Isso ajudará os recrutadores a conhecerem melhor o seu perfil.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit}>
                        <Textarea id="bio" value={data.bio} onChange={(e) => setData('bio', e.target.value)} maxLength={200} className="h-50" />
                        <InputError className="mt-2" message={errors.bio} />
                        <div className="mt-4 flex flex-col sm:flex-row sm:justify-end">
                            <span className="text-muted float-end mb-4 flex-1 text-right text-sm md:text-left">{data.bio.length} / 200</span>
                            <Button type="submit" disabled={processing}>
                                <UserIcon /> Guardar
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </ProfileCard>
    );
}
