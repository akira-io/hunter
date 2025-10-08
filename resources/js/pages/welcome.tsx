import AppLogo from '@/components/app-logo';
import DevCount from '@/components/dev-count';
import { Finder } from '@/components/Finder';
import { NavUser } from '@/components/nav-user';
import { SidebarProvider } from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/toaster';
import { WelcomeLoader } from '@/components/WelcomeLoader';
import { home, login, register } from '@/routes';
import { type SharedData, User } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { RiDiscordFill, RiGithubFill } from '@remixicon/react';
import { LogInIcon, UserPlus } from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';

export interface WelcomeProps {
    users: User[];
    paginator: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        next_page_url?: string;
        prev_page_url?: string;
        from?: number;
        to?: number;
    };
    // nextPageUsers?: User[];
}

export default function Welcome({ users, paginator }: WelcomeProps) {
    const { auth, quote } = usePage<SharedData>().props;

    const [isSearchLoading, setIsSearchLoading] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const [showLoader, setShowLoader] = useState(true);
    const [, setHasVisited] = useState(false);

    const initialQuote = useRef(quote);

    useEffect(() => {
        const visited = sessionStorage.getItem('hasVisitedWelcome');
        if (visited) {
            setShowLoader(false);
            setHasVisited(true);
        }
    }, []);

    const handleLoaderComplete = () => {
        setShowLoader(false);
        sessionStorage.setItem('hasVisitedWelcome', 'true');
    };

    const uniqueUsers = users.filter((user, index, self) => index === self.findIndex((u) => u.id === user.id));

    const hasMore = paginator.current_page < paginator.last_page;

    function loadMoreUsers() {
        if (isLoadingMore || !hasMore) return;

        setIsLoadingMore(true);
        const nextPage = paginator.current_page + 1;

        router.get(
            home().url,
            { page: nextPage },
            {
                preserveState: true,
                preserveScroll: true,
                replace: false,
                only: ['users', 'paginator'],
                onFinish: () => {
                    setIsLoadingMore(false);
                },
            },
        );
    }

    function search(e: React.ChangeEvent<HTMLInputElement>) {
        e.preventDefault();
        const query = e.target.value;
        setSearchQuery(query);
        setIsSearchLoading(true);

        router.get(home().url, query ? { q: query } : {}, {
            preserveScroll: query ? true : false,
            preserveState: true,
            replace: true,
            onFinish: () => {
                setIsSearchLoading(false);
            },
        });
    }

    return (
        <>
            {showLoader && <WelcomeLoader onComplete={handleLoaderComplete} />}
            <Head title="Dev Hunter">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <SidebarProvider className="bg-background flex min-h-screen flex-col items-center justify-start p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a]">
                <header className="bg-card fixed top-0 z-50 w-full p-4 text-sm backdrop-blur md:px-40 dark:bg-[#0a0a0a]/90">
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
                <div
                    className={`mb-50 flex w-full flex-col items-center justify-start transition-opacity duration-1000 lg:grow ${
                        showLoader ? 'opacity-0' : 'animate-fade-in opacity-100'
                    }`}
                >
                    <div className="mt-20 flex w-full flex-col items-center justify-center py-2 md:max-w-4xl lg:max-w-6xl">
                        <h1 className="mb-4 text-4xl font-bold dark:text-white">Hunter 🇨🇻</h1>
                        <p className="text-md mb-8 max-w-2xl text-center font-normal text-[#1b1b18] sm:text-lg dark:text-[#EDEDEC]">
                            O ponto de partida para inovação, colaboração e tecnologia em Cabo Verde. Um ecossistema digital onde projetos ganham vida
                            e talento local encontra visibilidade global.
                        </p>
                        <p className="text-muted-foreground -mt-6 mb-8 text-center text-xs">
                            "{initialQuote.current.message} - <b>{initialQuote.current.author}</b>"
                        </p>
                        <DevCount users={uniqueUsers} total={paginator.total} />
                    </div>
                    <Finder users={uniqueUsers} onSearch={search} isSearchLoading={isSearchLoading} />
                    {/* Infinite scroll trigger */}
                    {!searchQuery && hasMore && (
                        <div
                            className="flex w-full items-center justify-center py-8"
                            ref={(el) => {
                                if (el && !isLoadingMore) {
                                    const observer = new IntersectionObserver(
                                        (entries) => {
                                            const [entry] = entries;
                                            if (entry.isIntersecting) {
                                                loadMoreUsers();
                                            }
                                        },
                                        { rootMargin: '200px' },
                                    );
                                    observer.observe(el);
                                    return () => observer.disconnect();
                                }
                            }}
                        >
                            {isLoadingMore ? (
                                <div className="text-muted-foreground flex items-center gap-2">
                                    <div className="h-5 w-5 animate-spin rounded-full border-b-2 border-current"></div>
                                    <span>Carregando mais hunters...</span>
                                </div>
                            ) : (
                                <div className="text-muted-foreground text-sm">Scroll para carregar mais...</div>
                            )}
                        </div>
                    )}
                    {/* End message when no more items */}
                    {!hasMore && uniqueUsers.length > 0 && (
                        <div className="flex w-full items-center justify-center py-8">
                            <p className="text-muted-foreground text-sm">Todos os hunters foram carregados!</p>
                        </div>
                    )}
                </div>
            </SidebarProvider>
            <Toaster />
        </>
    );
}
