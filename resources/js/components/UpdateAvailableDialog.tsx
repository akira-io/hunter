import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { History, Smile, Sparkles } from 'lucide-react';
import { useEffect } from 'react';

interface UpdateAvailableDialogProps {
    open: boolean;
    onUpdate: () => void;
    onLater: () => void;
}

export function UpdateAvailableDialog({
    open,
    onUpdate,
    onLater,
}: UpdateAvailableDialogProps) {
    useEffect(() => {
        console.log('[UpdateDialog] Open state changed:', open);
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onLater()}>
            <DialogContent className="top-[50%] left-[50%] max-w-md -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-3xl bg-white p-0 shadow-2xl dark:bg-zinc-900">
                <div className="gradient absolute inset-0">
                    {/*<div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(147,51,234,0.1),transparent_50%)] dark:bg-[radial-gradient(circle_at_50%_50%,rgba(147,51,234,0.2),transparent_50%)]" />*/}
                </div>
                {/* Floating particles effect */}
                <div className="absolute inset-0 overflow-hidden">
                    {[...Array(6)].map((_, i) => (
                        <div
                            key={i}
                            className="animate-float absolute"
                            style={{
                                left: `${Math.random() * 100}%`,
                                top: `${Math.random() * 100}%`,
                                animationDelay: `${Math.random() * 2}s`,
                                animationDuration: `${3 + Math.random() * 2}s`,
                            }}
                        >
                            <Sparkles className="h-3 w-3 text-purple-500/40 dark:text-purple-400/40" />
                        </div>
                    ))}
                </div>
                {/* Content */}
                <div className="relative z-10 p-8">
                    <DialogHeader className="gap-6">
                        {/* Icon with pulse animation */}
                        <div className="animate-pulse-slow mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-purple-500/10 ring-4 ring-purple-500/20 backdrop-blur-sm dark:bg-purple-500/20 dark:ring-purple-400/30">
                            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-700 shadow-lg">
                                <History
                                    className="animate-ping-slow h-8 w-8 text-white"
                                    style={{ animationDuration: '1.5s' }}
                                />
                            </div>
                        </div>

                        <div className="space-y-3">
                            <DialogTitle className="text-center text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                                <span className="animate-fade-in inline-block">
                                    Nova Versão
                                </span>{' '}
                            </DialogTitle>
                            <DialogDescription className="text-center text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
                                🚀 O{' '}
                                <span className="font-semibold">Hunter</span>{' '}
                                acaba de dar mais um grande passo!
                                <span className="mt-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    A tua experiência foi atualizada para a
                                    versão mais recente
                                    <br /> Obrigado por construir esta jornada
                                    connosco! ✨
                                </span>
                            </DialogDescription>
                        </div>
                    </DialogHeader>

                    <DialogFooter className="mt-8 flex-col gap-0">
                        <Button
                            onClick={onUpdate}
                            className="h-12 w-full gap-2 text-base font-semibold shadow-lg transition-all duration-300 hover:shadow-xl"
                            variant="gradient"
                        >
                            <Smile className="h-5 w-5" />
                            Continuar
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>

            <style>{`
                @keyframes float {
                    0%, 100% {
                        transform: translateY(0) translateX(0);
                        opacity: 0;
                    }
                    50% {
                        opacity: 1;
                    }
                    100% {
                        transform: translateY(-100px) translateX(20px);
                    }
                }
                @keyframes pulse-slow {
                    0%, 100% {
                        opacity: 1;
                        transform: scale(1);
                    }
                    50% {
                        opacity: 0.8;
                        transform: scale(1.05);
                    }
                }
                @keyframes fade-in {
                    from {
                        opacity: 0;
                        transform: translateY(10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .animate-float {
                    animation: float ease-in-out infinite;
                }
                .animate-pulse-slow {
                    animation: pulse-slow 2s ease-in-out infinite;
                }
                .animate-fade-in {
                    animation: fade-in 0.6s ease-out forwards;
                    opacity: 0;
                }
            `}</style>
        </Dialog>
    );
}
