import { About } from '@/components/profile/About';
import { AcademicBackground } from '@/components/profile/AcademicBackground';
import { HighlightSkills } from '@/components/profile/HighlightSkills';
import { ProfileLinks } from '@/components/profile/Links';
import { ProfileCompletion } from '@/components/profile/ProfileCompletion';
import ProfileAvatarCard from '@/components/ProfileAvatarCard';
import { Card, CardContent } from '@/components/ui/card';
import { Option } from '@/components/ui/multiselect';
import AppLayout from '@/layouts/app-layout';
import followable from '@/routes/followable';
import publicRoutes from '@/routes/public';
import verification from '@/routes/verification';
import { type AcademicBackground as ProfessionalEducationType, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { formatDate } from 'date-fns/format';
import { GoLocation } from 'react-icons/go';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Definições de perfil',
        href: '/settings/profile',
    },
];

interface ProfileProps {
    mustVerifyEmail: boolean;
    status?: string;
    skills: Option[];
    highlightedSkills: Option[];
    academicBackgrounds: ProfessionalEducationType[];
    followers: number;
    followings: number;
}

export default function Profile({ mustVerifyEmail, status, skills, highlightedSkills, academicBackgrounds, followings, followers }: ProfileProps) {
    const { auth } = usePage<SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Definições Perfil" />
            <div className="bg-background mx-auto flex max-w-6xl flex-col gap-4 text-gray-200 md:flex-row md:p-8">
                <aside className="bg-background flex w-full flex-shrink-0 flex-col items-center self-start p-6 md:sticky md:top-10 md:w-80">
                    <div className="mb-4 w-full md:hidden">
                        <ProfileCompletion academicBackgrounds={academicBackgrounds} skills={highlightedSkills} />
                    </div>
                    <Card className="gradient w-full items-center justify-center p-6 md:w-80">
                        <CardContent className="flex flex-col items-center text-center">
                            <ProfileAvatarCard />
                            <h2 className="mt-4 text-xl font-semibold">
                                <Link href={publicRoutes.profile.show.url(auth.user.id)} prefetch>
                                    {auth.user.name}
                                </Link>
                            </h2>
                            <div className="space-y-2 text-center text-xs text-gray-400">
                                <p>{auth.user.email}</p>
                                {/*<p>*/}
                                {/*    <span>Software Developer :</span> <b>Debtges</b>*/}
                                {/*</p>*/}
                                <p className="flex items-center justify-center gap-1">
                                    <GoLocation /> {auth.user.location}
                                </p>
                            </div>
                        </CardContent>
                        <div className="grid grid-cols-2 items-end justify-end gap-4">
                            <Link href={followable.followers.url()} className="flex gap-1 text-xs">
                                <b>{followers}</b> Hunters
                            </Link>
                            <Link href={followable.followings.url()} className="flex gap-1 text-xs">
                                <b>{followings}</b> Huntings
                            </Link>
                        </div>
                        <HighlightSkills skills={skills} authSkills={highlightedSkills} />
                        <p className="mt-0 border-t-1 py-2 text-xs text-gray-500">
                            Hunter desde de: <b>{formatDate(auth.user.created_at, 'dd-MM-Y')}</b>
                        </p>
                    </Card>
                    <ProfileLinks user={auth.user} />
                </aside>
                <main className="flex-1 space-y-6 overflow-y-auto p-6">
                    <div className="hidden md:block">
                        <ProfileCompletion academicBackgrounds={academicBackgrounds} skills={highlightedSkills} />
                    </div>
                    <About />
                    <AcademicBackground academicBackgrounds={academicBackgrounds} />
                    {/*<HighlightedProjects />*/}
                    {/*<section className="space-y-6">*/}
                    {/*    <ProfileCard title="Habilidades" icon={<PlusIcon />}>*/}
                    {/*        <Award />*/}
                    {/*        <p className="mb-4 max-w-100 text-center">*/}
                    {/*            Potencialize sua trajetória: destaque suas principais habilidades e abra portas para novas oportunidades!*/}
                    {/*        </p>*/}
                    {/*        <Dialog>*/}
                    {/*            <DialogTrigger asChild>*/}
                    {/*                <Button variant="secondary">*/}
                    {/*                    <Award />*/}
                    {/*                    Experiências Profissionais*/}
                    {/*                </Button>*/}
                    {/*            </DialogTrigger>*/}
                    {/*            <DialogContent>*/}
                    {/*                <DialogHeader>*/}
                    {/*                    <DialogTitle>Apresentação</DialogTitle>*/}
                    {/*                    <DialogDescription>*/}
                    {/*                        Adicione uma breve descrição sobre você. Isso ajudará os recrutadores a conhecerem melhor o seu perfil.*/}
                    {/*                    </DialogDescription>*/}
                    {/*                </DialogHeader>*/}
                    {/*                <Textarea id="bio" value={data.bio} onChange={(e) => setData('bio', e.target.value)} maxLength={200} />*/}
                    {/*                <InputError className="mt-2" message={errors.bio} />*/}
                    {/*                <div className="flex flex-col md:flex-row md:justify-end">*/}
                    {/*                    <span className="text-muted-foreground text-md float-end flex-1">{data.bio.length} / 200</span>*/}
                    {/*                    <Button type="button" onClick={submit}>*/}
                    {/*                        <DialogClose>Guardar</DialogClose>*/}
                    {/*                    </Button>*/}
                    {/*                </div>*/}
                    {/*            </DialogContent>*/}
                    {/*        </Dialog>*/}
                    {/*    </ProfileCard>*/}
                    {/*</section>*/}
                </main>
            </div>
            {mustVerifyEmail && auth.user.email_verified_at === null && (
                <div>
                    <p className="text-muted-foreground text-md -mt-4">
                        O seu endereço de e-mail não está verificado.
                        <Link
                            href={verification.send.url()}
                            method="post"
                            as="button"
                            className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click aqui para reenviar o e-mail de verificação.
                        </Link>
                    </p>
                    {status === 'verification-link-sent' && (
                        <div className="text-md mt-2 font-medium text-green-600">
                            Um novo link de verificação foi enviado para o seu endereço de e-mail.
                        </div>
                    )}
                </div>
            )}
        </AppLayout>
    );
}
