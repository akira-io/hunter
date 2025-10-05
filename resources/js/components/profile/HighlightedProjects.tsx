import { ProfileCard } from '@/components/profile-card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { PlusIcon, Star } from 'lucide-react';

export function HighlightedProjects() {
    return (
        <ProfileCard title="Destaques" icon={<PlusIcon />}>
            <Star />
            <p className="mb-4 max-w-100 text-center">Compartilhe o link dos seus melhores projetos para se destacar e conquistar oportunidades</p>
            <Dialog>
                <DialogTrigger asChild>
                    <Button variant="secondary">
                        <Star />
                        Adicionar Destaques
                    </Button>
                </DialogTrigger>
                <DialogContent className="bg-white dark:bg-zinc-950">
                    <DialogHeader>
                        <DialogTitle className="text-zinc-900 dark:text-zinc-100">Apresentação</DialogTitle>
                        <DialogDescription className="text-zinc-600 dark:text-zinc-400">
                            Adicione uma breve descrição sobre você. Isso ajudará os recrutadores a conhecerem melhor o seu perfil.
                        </DialogDescription>
                    </DialogHeader>
                    {/*<Textarea id="bio" value={data.bio} onChange={(e) => setData('bio', e.target.value)} maxLength={200} />*/}
                    {/*<InputError className="mt-2" message={errors.bio} />*/}
                    {/*<div className="flex flex-col sm:flex-row sm:justify-end">*/}
                    {/*    <span className="text-muted-foreground float-end flex-1 text-sm">{data.bio.length} / 200</span>*/}
                    {/*    <Button type="button" onClick={submit}>*/}
                    {/*        <DialogClose>Guardar</DialogClose>*/}
                    {/*    </Button>*/}
                    {/*</div>*/}
                </DialogContent>
            </Dialog>
        </ProfileCard>
    );
}
