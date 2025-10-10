import { cn } from '@/lib/utils';
import { useEffect, useState } from 'react';

interface WelcomeLoaderProps {
    onComplete?: () => void;
}

export function WelcomeLoader({ onComplete }: WelcomeLoaderProps) {
    const [progress, setProgress] = useState(0);
    const [isComplete, setIsComplete] = useState(false);
    const [showContent, setShowContent] = useState(true);

    useEffect(() => {
        const interval = setInterval(() => {
            setProgress((prev) => {
                if (prev >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        setIsComplete(true);
                        setTimeout(() => {
                            setShowContent(false);
                            onComplete?.();
                        }, 800);
                    }, 300);
                    return 100;
                }

                const increment =
                    prev < 70
                        ? Math.random() * 15 + 10
                        : Math.random() * 8 + 15;
                return Math.min(prev + increment, 100);
            });
        }, 300);

        return () => clearInterval(interval);
    }, [onComplete]);

    if (!showContent) return null;

    return (
        <div
            className={cn(
                'fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-purple-950 via-slate-900 to-black transition-all duration-800',
                isComplete && 'opacity-0',
            )}
        >
            {/* Animated background particles */}
            <div className="absolute inset-0 overflow-hidden">
                {[...Array(20)].map((_, i) => (
                    <div
                        key={i}
                        className="animate-float absolute h-2 w-2 rounded-full bg-purple-400/20"
                        style={{
                            left: `${Math.random() * 100}%`,
                            top: `${Math.random() * 100}%`,
                            animationDelay: `${Math.random() * 3}s`,
                            animationDuration: `${3 + Math.random() * 4}s`,
                        }}
                    />
                ))}
            </div>

            {/* Main content */}
            <div className="relative z-10 flex flex-col items-center gap-8 px-6 text-center">
                {/* Logo/Icon with pulse animation */}
                <div className="animate-scale-in relative">
                    <div className="absolute inset-0 animate-ping rounded-full bg-purple-500/50 opacity-75" />
                    <div className="relative flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-800 shadow-2xl shadow-purple-500/50">
                        <span className="text-5xl">🎯</span>
                    </div>
                </div>

                {/* Welcome text with staggered animation */}
                <div className="space-y-3">
                    <h1
                        className="animate-slide-up text-5xl font-bold text-white opacity-0"
                        style={{ animationDelay: '0.2s' }}
                    >
                        Bem-vindo ao
                    </h1>
                    <h2
                        className="animate-slide-up bg-gradient-to-r from-purple-400 via-purple-600 to-purple-400 bg-clip-text text-6xl font-extrabold text-transparent opacity-0"
                        style={{ animationDelay: '0.4s' }}
                    >
                        Hunter
                    </h2>
                    <p
                        className="animate-slide-up text-lg text-purple-200 opacity-0"
                        style={{ animationDelay: '0.6s' }}
                    >
                        Conectando talentos, construindo o futuro 🇨🇻
                    </p>
                </div>

                {/* Progress bar container */}
                <div
                    className="animate-slide-up w-full max-w-md space-y-3 opacity-0"
                    style={{ animationDelay: '0.8s' }}
                >
                    {/* Progress bar */}
                    <div className="relative h-3 overflow-hidden rounded-full bg-slate-800/50 backdrop-blur-sm">
                        <div
                            className="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-purple-500 via-purple-900 to-purple-500 transition-all duration-300 ease-out"
                            style={{
                                width: `${progress}%`,
                                backgroundSize: '200% 100%',
                                animation: 'shimmer 2s infinite linear',
                            }}
                        >
                            {/* Glow effect */}
                            <div className="absolute inset-0 rounded-full bg-gradient-to-r from-transparent via-white/30 to-transparent" />
                        </div>
                    </div>

                    {/* Progress percentage */}
                    <div className="flex items-center justify-between text-sm">
                        <span className="text-purple-300">
                            Carregando experiência...
                        </span>
                        <span className="font-mono text-xl font-bold text-purple-400">
                            {Math.round(progress)}%
                        </span>
                    </div>
                </div>

                {/* Loading dots */}
                <div
                    className="animate-slide-up flex gap-2 opacity-0"
                    style={{ animationDelay: '1s' }}
                >
                    {[...Array(3)].map((_, i) => (
                        <div
                            key={i}
                            className="h-3 w-3 animate-bounce rounded-full bg-purple-400"
                            style={{
                                animationDelay: `${i * 0.15}s`,
                            }}
                        />
                    ))}
                </div>
            </div>

            {/* Bottom decorative element */}
            <div className="absolute right-0 bottom-0 left-0 h-1 bg-gradient-to-r from-transparent via-purple-500 to-transparent opacity-50" />
        </div>
    );
}
