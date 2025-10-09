import { HuntComments } from '@/components/commentable/HuntComments';
import { LikeButton } from '@/components/likeable/LikeButton';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { OnboardingAvatar } from '@/components/Onboarding';
import hunts from '@/routes/hunts';
import { Hunt } from '@/types';
import { router } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Eye, MessageCircle, Repeat2, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { createPortal } from 'react-dom';

interface HuntModalProps {
    hunt: Hunt;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onNext?: () => void;
    onPrevious?: () => void;
    hasNext?: boolean;
    hasPrevious?: boolean;
}

export function HuntModal({ hunt, open, onOpenChange, onNext, onPrevious, hasNext = false, hasPrevious = false }: HuntModalProps) {
    const [scrollPosition, setScrollPosition] = useState(0);

    const handleLike = useCallback(() => {
        router.post(
            hunts.toggleLike.url(hunt),
            {},
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    }, [hunt]);

    const handleShare = useCallback(() => {
        if (navigator.share) {
            navigator
                .share({
                    title: `Hunt by ${hunt.owner.name}`,
                    text: hunt.content,
                    url: window.location.href,
                })
                .catch(() => {
                    // User cancelled share
                });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(window.location.href);
        }
    }, [hunt]);

    const handleClose = useCallback(() => {
        onOpenChange(false);
    }, [onOpenChange]);

    const handleKeyDown = useCallback(
        (e: KeyboardEvent) => {
            if (!open) return;

            switch (e.key) {
                case 'ArrowLeft':
                    if (hasPrevious && onPrevious) {
                        e.preventDefault();
                        onPrevious();
                    }
                    break;
                case 'ArrowRight':
                    if (hasNext && onNext) {
                        e.preventDefault();
                        onNext();
                    }
                    break;
            }
        },
        [open, onNext, onPrevious, hasNext, hasPrevious],
    );

    // Save and restore scroll position
    useEffect(() => {
        if (open) {
            setScrollPosition(window.scrollY);
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
            window.scrollTo(0, scrollPosition);
        }

        return () => {
            document.body.style.overflow = '';
        };
    }, [open, scrollPosition]);

    useEffect(() => {
        document.addEventListener('keydown', handleKeyDown);
        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [handleKeyDown]);

    if (!open) return null;

    return createPortal(
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            {/* Overlay - non-interactive */}
            <div className="animate-in fade-in-0 fixed inset-0 bg-black/90" />

            {/* Modal Content */}
            <div
                className="animate-in fade-in-0 zoom-in-95 relative z-50 h-[90vh] max-h-[90vh] w-[95vw] overflow-y-auto border-zinc-700 bg-transparent p-0 shadow-2xl md:h-[95vh] md:max-w-[90vw] md:overflow-hidden"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Close Button */}
                <button
                    onClick={handleClose}
                    className="absolute top-4 right-4 z-50 rounded-full bg-zinc-900/80 p-2 text-white transition-all hover:scale-110 hover:bg-zinc-800"
                    aria-label="Close modal"
                >
                    <X size={24} />
                </button>

                {/* Navigation Arrows (Desktop) */}
                {hasPrevious && onPrevious && (
                    <button
                        onClick={onPrevious}
                        className="absolute top-1/2 -left-16 z-50 hidden -translate-y-1/2 rounded-full bg-zinc-900/80 p-3 text-white transition-colors hover:bg-zinc-800 md:block"
                        aria-label="Previous hunt"
                    >
                        <ArrowLeft size={24} />
                    </button>
                )}

                {hasNext && onNext && (
                    <button
                        onClick={onNext}
                        className="absolute top-1/2 -right-16 z-50 hidden -translate-y-1/2 rounded-full bg-zinc-900/80 p-3 text-white transition-colors hover:bg-zinc-800 md:block"
                        aria-label="Next hunt"
                    >
                        <ArrowRight size={24} />
                    </button>
                )}

                {/* Two-Column Layout */}
                <div className="gradient bg-card grid h-full grid-cols-1 rounded-2xl md:grid-cols-2">
                    {/* Left Column - Image */}
                    <div className="relative flex items-center justify-center overflow-hidden bg-black md:h-full">
                        {hunt.image_url ? (
                            <img src={hunt.image_url} alt="Hunt image" className="h-auto w-full object-contain md:h-full" loading="lazy" />
                        ) : (
                            <div className="flex h-full min-h-[300px] w-full items-center justify-center bg-gradient-to-br from-zinc-800 to-zinc-900 md:min-h-0">
                                <div className="text-center text-zinc-500">
                                    <Eye size={64} className="mx-auto mb-4 opacity-20" />
                                    <p className="text-sm">Sem imagem</p>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Column - Details, Comments, Interactions */}
                    <div className="flex flex-col md:h-full md:overflow-hidden">
                        {/* Hunt Header */}
                        <div className="flex items-start gap-3 border-b border-zinc-200 p-4 dark:border-zinc-700">
                            <OnboardingAvatar avatarUrl={hunt.owner.avatar_url} size={10} />
                            <div className="flex-1">
                                <div className="flex items-center gap-2">
                                    <h3 className="font-semibold text-zinc-900 dark:text-zinc-100">{hunt.owner.name}</h3>
                                    <span className="text-sm text-zinc-500 dark:text-zinc-400">•</span>
                                    <span className="text-sm text-zinc-500 dark:text-zinc-400">{hunt.created_at}</span>
                                </div>
                                {hunt.owner.user_name && <p className="text-sm text-zinc-500 dark:text-zinc-400">@{hunt.owner.user_name}</p>}
                            </div>
                        </div>

                        {/* Hunt Content */}
                        {hunt.content && (
                            <div className="border-b border-zinc-200 p-4 dark:border-zinc-700">
                                <MarkdownRenderer content={hunt.content} />
                            </div>
                        )}

                        {/* Stats and Actions Combined */}
                        <div className="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                            <div className="flex items-center gap-4">
                                <LikeButton count={hunt.likes_count} hasLiked={hunt.has_liked} onLike={handleLike} iconSize={18} />
                                <button className="flex items-center gap-1.5 text-zinc-600 transition-colors hover:text-purple-600 dark:text-zinc-400 dark:hover:text-purple-400">
                                    <MessageCircle size={18} />
                                    <span className="text-sm">{hunt.comments.length}</span>
                                </button>

                                <div className="flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                                    <Eye size={16} />
                                    <span>{hunt.views ?? 0}</span>
                                </div>
                                <button
                                    onClick={handleShare}
                                    className="flex items-center gap-1.5 text-zinc-600 transition-colors hover:text-purple-600 dark:text-zinc-400 dark:hover:text-purple-400"
                                >
                                    <Repeat2 size={18} />
                                    <span className="text-sm">{hunt.shares ?? 0}</span>
                                </button>
                            </div>
                        </div>

                        {/* Comments Section - Scrollable on Desktop only */}
                        <div className="mt-10 md:min-h-0 md:flex-1 md:overflow-y-auto">
                            <HuntComments isOpen={true} hunt={hunt} />
                        </div>
                    </div>
                </div>
            </div>
        </div>,
        document.body,
    );
}
