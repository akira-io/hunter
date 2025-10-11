import AppLogo from '@/components/app-logo';
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/react';
import { WifiOff } from 'lucide-react';

export default function Offline() {
    const handleReload = () => {
        window.location.replace('/');
    };

    return (
        <>
            <Head title="Offline - Hunter" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background p-6 dark:bg-[#0a0a0a]">
                <div className="mx-auto flex w-full max-w-md flex-col items-center justify-center space-y-8 text-center">
                    {/* Logo */}
                    <div className="flex items-center gap-2">
                        <AppLogo />
                    </div>

                    {/* Icon */}
                    <div className="rounded-full bg-muted p-6 dark:bg-[#1a1a1a]">
                        <WifiOff
                            className="h-16 w-16 text-muted-foreground dark:text-[#62605b]"
                            strokeWidth={1.5}
                        />
                    </div>

                    {/* Content */}
                    <div className="space-y-4">
                        <h1 className="text-3xl font-bold tracking-tight text-[#1b1b18] dark:text-white">
                            Offline
                        </h1>
                        <p className="text-base text-[#1b1b18]/70 dark:text-[#EDEDEC]/70">
                            Por favor, verifique sua conexão com a internet
                        </p>
                    </div>

                    {/* Reload Button */}
                    <Button
                        onClick={handleReload}
                        size="lg"
                        variant="gradient"
                        // className="mt-4 gap-2 rounded-sm bg-[#1b1b18] px-8 py-2.5 font-medium text-white transition-colors hover:bg-[#1b1b18]/90 dark:bg-white dark:text-[#0a0a0a] dark:hover:bg-white/90"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="animate-spin"
                        >
                            <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8" />
                            <path d="M21 3v5h-5" />
                        </svg>
                        Tentar novamente
                    </Button>

                    {/* Help text */}
                    <p className="mt-6 text-xs text-muted-foreground dark:text-[#62605b]">
                        A página será recarregada automaticamente quando a
                        conexão for restabelecida
                    </p>
                </div>
            </div>
        </>
    );
}
