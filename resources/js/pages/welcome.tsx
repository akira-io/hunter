import AppLogo from '@/components/app-logo';
import DevCount from '@/components/dev-count';
import { Finder } from '@/components/Finder';
import { NavUser } from '@/components/nav-user';
import { SidebarProvider } from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/toaster';
import { home, login, register } from '@/routes';
import { type SharedData, User } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { RiDiscordFill, RiGithubFill } from '@remixicon/react';
import { LogInIcon, UserPlus } from 'lucide-react';
import React, { useRef, useState } from 'react';

export interface WelcomeProps {
    users: User[];
    paginator: {
        data: User[];
        total: 0;
    };
}

export default function Welcome({ users, paginator }: WelcomeProps) {
    const { auth, quote } = usePage<SharedData>().props;

    const [isSearchLoading, setIsSearchLoading] = useState(false);

    const initialUsersRef = useRef<User[]>(paginator.data);
    const initialTotalRef = useRef(paginator.total);

    const _users = initialUsersRef.current;
    const _total = initialTotalRef.current;

    function search(e: React.ChangeEvent<HTMLInputElement>) {
        e.preventDefault();
        setIsSearchLoading(true);
        router.get(
            home().url,
            { q: e.target.value },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => {
                    setIsSearchLoading(false);
                },
            },
        );
    }

    return (
        <>
            <Head title="Dev Hunter">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <SidebarProvider className="bg-background flex min-h-screen flex-col items-center justify-start bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a]">
                <header className="bg-card fixed top-0 z-50 w-full bg-[#FDFDFC]/90 p-4 text-sm backdrop-blur md:px-40 dark:bg-[#0a0a0a]/90">
                    <nav className="flex items-center justify-end gap-4">
                        <AppLogo />
                        <div className="flex-1" />
                        {auth.user ? (
                            <div>
                                <NavUser />
                            </div>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="flex items-center justify-center gap-2 rounded-sm border px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] md:border-transparent dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    <LogInIcon size={16} />
                                    Iniciar sessão
                                </Link>
                                <Link
                                    href={register()}
                                    className="hidden items-center justify-center gap-2 rounded-sm border border-[#19140035] px-5 py-1.5 text-sm text-[#1b1b18] hover:border-[#1915014a] md:flex dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    <UserPlus size={16} />
                                    Criar conta
                                </Link>
                                <div className="flex items-center gap-2">
                                    <a
                                        className="cursor-pointer dark:text-white"
                                        href="https://github.com/akira-io/devhunter"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <RiGithubFill />
                                    </a>
                                    <a
                                        className="cursor-pointer dark:text-white"
                                        href="https://discord.gg/ghPqZg3RcZ"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <RiDiscordFill />
                                    </a>
                                </div>
                            </>
                        )}
                    </nav>
                </header>
                <div className="mb-50 flex w-full flex-col items-center justify-start opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <div className="mt-20 flex w-full flex-col items-center justify-center py-2 md:max-w-4xl lg:max-w-6xl">
                        <h1 className="mb-4 text-4xl font-bold dark:text-white">Hunter 🇨🇻</h1>
                        <p className="text-md mb-8 max-w-2xl text-center font-normal text-[#1b1b18] sm:text-lg dark:text-[#EDEDEC]">
                            O ponto de partida para inovação, colaboração e tecnologia em Cabo Verde. Um ecossistema digital onde projetos ganham vida
                            e talento local encontra visibilidade global.
                        </p>
                        <p className="text-muted-foreground -mt-6 mb-8 text-center text-xs">
                            "{quote.message} - <b>{quote.author}</b>"
                        </p>
                        <DevCount users={_users} total={_total} />
                    </div>
                    <Finder users={users} onSearch={search} isSearchLoading={isSearchLoading} />
                </div>
            </SidebarProvider>
            <Toaster />
        </>
    );
}
