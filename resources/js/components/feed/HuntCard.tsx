import { HuntComments } from '@/components/commentable/HuntComments';
import DeleteHunt from '@/components/feed/DeleteHunt';
import { HuntModal } from '@/components/feed/HuntModal';
import { HuntLikes } from '@/components/likeable/HuntLikes';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { UserAvatar } from '@/components/UserAvatar';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { cn } from '@/lib/utils';
import hunts from '@/routes/hunts';
import publicRoutes from '@/routes/public';
import { Hunt, SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import {
    BarChart,
    Edit,
    EllipsisVerticalIcon,
    Eye,
    Flame,
    Heart,
    MessageCircle,
    Repeat2,
    SaveIcon,
    ShieldAlert,
    StopCircle,
    TrendingUp,
} from 'lucide-react';
import { useState } from 'react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '../ui/dropdown-menu';

interface HuntCardProps {
    hunt: Hunt;
    ligatures?: boolean;
    withOpenComments?: boolean;
    width?: 'full' | 'default' | 'narrow';
    showViralBanner?: boolean;
}

export function HuntCardConnector() {
    return (
        <>
            <div className="absolute -top-10 left-5 flex h-10 w-1 items-center justify-center rounded-full bg-white text-xs dark:bg-zinc-900" />
            <div className="bg-card absolute -top-10 right-5 flex h-10 w-1 items-center justify-center rounded-full text-xs dark:bg-zinc-900" />
        </>
    );
}

export function HuntCard({ hunt, withOpenComments = false, width = 'default', showViralBanner = true }: HuntCardProps) {
    const { auth } = usePage<SharedData>().props;
    const [isOpenComments, setOpenComments] = useState(withOpenComments);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const sanitizedImageUrl = useSanitizeImageUrl(hunt.image_url);
    const isOwner = auth.user.id === hunt.owner.id;

    // Extract metrics from hunt - provide default values for non-owner
    const metrics = hunt.metrics || {
        views: hunt.views || 0,
        likes: hunt.likes_count || 0,
        comments: hunt.comments?.length || 0,
        shares: hunt.shares || 0,
        total_engagements: 0,
        engagement_rate: 0,
        interaction_rate: 0,
        comment_rate: 0,
        share_rate: 0,
        quality_score: 0,
        virality_coefficient: 0,
        avg_engagement_per_view: 0,
        performance_level: 'average' as const,
        rank: 0,
        is_viral: false,
        is_performing_well: false,
    };
    const isViral = metrics.is_viral || false;
    function gotoProfile() {
        router.get(publicRoutes.profile.show.url(hunt.owner.id));
    }

    function gotoHuntDetail() {
        // Always open in modal
        setIsModalOpen(true);
    }

    function gotoMetrics() {
        // Navigate to full page with metrics (owner only)
        router.get(hunts.show.url(hunt));
    }

    // Calculate width classes
    const widthClasses = {
        full: 'w-full',
        default: 'w-full max-w-2xl',
        narrow: 'w-full max-w-xl',
    };

    return (
        <>
            {/* Hunt Modal - opens for all hunts */}
            <HuntModal hunt={hunt} open={isModalOpen} onOpenChange={setIsModalOpen} />

            <Card className={cn('relative mx-auto mb-4 overflow-hidden', widthClasses[width])} data-pan={`hunt-${hunt.id}`}>
                {/* Owner's Viral Performance Banner - Mobile Optimized - Only show in feed, not on show page */}
                {isOwner && isViral && showViralBanner && (
                    <div className="border-b border-orange-500/20 bg-gradient-to-r from-orange-500/10 via-red-500/10 to-pink-500/10 px-3 py-2 sm:px-4 sm:py-2.5">
                        <div className="flex items-center justify-between gap-2 text-xs sm:text-sm">
                            <div className="flex items-center gap-1.5 sm:gap-2">
                                <Flame className="h-3.5 w-3.5 text-orange-500 sm:h-4 sm:w-4" />
                                <span className="font-semibold text-orange-600 dark:text-orange-400">Conteúdo Viral!</span>
                            </div>
                            <div className="text-muted-foreground flex items-center gap-1 text-xs">
                                <TrendingUp className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                <span className="hidden sm:inline">{metrics.virality_coefficient?.toFixed(1)}% taxa de share</span>
                                <span className="sm:hidden">{metrics.virality_coefficient?.toFixed(0)}%</span>
                            </div>
                        </div>
                    </div>
                )}

                {/* Owner's Metrics Dashboard - Mobile First Design */}
                {showViralBanner && isOwner && (
                    <div className="bg-muted/30 border-b px-3 py-3 sm:px-4 sm:py-4">
                        <div className="mb-2 flex items-center justify-between">
                            <h3 className="text-muted-foreground text-xs font-semibold tracking-wider uppercase sm:text-sm">Performance</h3>
                            <Button variant="ghost" size="sm" className="h-7 gap-1.5 text-xs sm:h-8 sm:text-sm" onClick={gotoMetrics}>
                                <BarChart className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                <span className="hidden sm:inline">Ver Detalhes</span>
                                <span className="sm:hidden">Ver</span>
                            </Button>
                        </div>

                        {/* Metrics Grid - Responsive */}
                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
                            {/* Views */}
                            <button
                                className="bg-background/50 hover:bg-background/80 rounded-lg p-2.5 text-left transition-colors sm:p-3"
                                onClick={gotoHuntDetail}
                            >
                                <div className="text-muted-foreground flex items-center gap-1.5">
                                    <Eye className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    <span className="text-xs">Views</span>
                                </div>
                                <p className="mt-1 text-lg font-bold sm:text-xl">{metrics.views?.toLocaleString() || hunt.views}</p>
                                {metrics.engagement_rate !== undefined && (
                                    <p className="text-muted-foreground mt-0.5 text-xs">{metrics.engagement_rate.toFixed(1)}% engaj.</p>
                                )}
                            </button>

                            {/* Likes */}
                            <button
                                className="bg-background/50 hover:bg-background/80 rounded-lg p-2.5 text-left transition-colors sm:p-3"
                                onClick={gotoHuntDetail}
                            >
                                <div className="text-muted-foreground flex items-center gap-1.5">
                                    <span className="text-xs">
                                        <Heart className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                        Likes
                                    </span>
                                </div>
                                <p className="mt-1 text-lg font-bold sm:text-xl">{metrics.likes?.toLocaleString() || hunt.likes_count}</p>
                                {metrics.interaction_rate !== undefined && (
                                    <p className="text-muted-foreground mt-0.5 text-xs">{metrics.interaction_rate.toFixed(1)}% taxa</p>
                                )}
                            </button>

                            {/* Shares */}
                            <button
                                className="bg-background/50 hover:bg-background/80 rounded-lg p-2.5 text-left transition-colors sm:p-3"
                                onClick={gotoHuntDetail}
                            >
                                <div className="text-muted-foreground flex items-center gap-1.5">
                                    <Repeat2 className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    <span className="text-xs">Shares</span>
                                </div>
                                <p className="mt-1 text-lg font-bold sm:text-xl">{metrics.shares?.toLocaleString() || hunt.shares}</p>
                                {metrics.share_rate !== undefined && (
                                    <p className="text-muted-foreground mt-0.5 text-xs">{metrics.share_rate.toFixed(1)}% taxa</p>
                                )}
                            </button>

                            {/* Comments - Opens Comments Section */}
                            <button
                                className="bg-background/50 hover:bg-background/80 cursor-pointer rounded-lg p-2.5 text-left transition-colors sm:p-3"
                                onClick={() => setOpenComments((prev) => !prev)}
                            >
                                <div className="text-muted-foreground flex items-center gap-1.5">
                                    <MessageCircle className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    <span className="text-xs">Coment.</span>
                                </div>
                                <p className="mt-1 text-lg font-bold sm:text-xl">
                                    {metrics.comments?.toLocaleString() || hunt.comments?.length || 0}
                                </p>
                                {metrics.comment_rate !== undefined && (
                                    <p className="text-muted-foreground mt-0.5 text-xs">{metrics.comment_rate.toFixed(1)}% taxa</p>
                                )}
                            </button>
                        </div>

                        {/* Quality Score Bar - Mobile Optimized */}
                        {metrics.quality_score !== undefined && (
                            <div className="mt-3">
                                <div className="mb-1 flex items-center justify-between text-xs">
                                    <span className="text-muted-foreground">Score de Qualidade</span>
                                    <span className="font-semibold">{metrics.quality_score.toFixed(0)}/100</span>
                                </div>
                                <div className="bg-muted h-1.5 w-full overflow-hidden rounded-full sm:h-2">
                                    <div
                                        className={cn(
                                            'h-full transition-all',
                                            metrics.quality_score >= 75
                                                ? 'bg-gradient-to-r from-green-500 to-emerald-500'
                                                : metrics.quality_score >= 50
                                                  ? 'bg-gradient-to-r from-blue-500 to-cyan-500'
                                                  : metrics.quality_score >= 25
                                                    ? 'bg-gradient-to-r from-yellow-500 to-orange-500'
                                                    : 'bg-gradient-to-r from-gray-400 to-gray-500',
                                        )}
                                        style={{ width: `${metrics.quality_score}%` }}
                                    />
                                </div>
                            </div>
                        )}
                    </div>
                )}

                <CardHeader className="flex flex-row items-start gap-3 sm:gap-4">
                    <UserAvatar
                        avatarUrl={hunt.owner.avatar_url}
                        userName={hunt.owner.name}
                        className="shrink-0 cursor-pointer"
                        onClick={gotoProfile}
                    />
                    <div className="flex min-w-0 flex-1 flex-col">
                        <CardTitle className="cursor-pointer truncate text-sm font-semibold sm:text-base" onClick={gotoProfile}>
                            {hunt.owner.name}
                        </CardTitle>
                        <div
                            className="text-muted-foreground cursor-pointer truncate text-xs sm:text-sm"
                            onClick={() => router.get(publicRoutes.profile.show.url(hunt.owner.id))}
                        >
                            @{hunt.owner.user_name || hunt.owner.name} · {hunt.created_at}
                        </div>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                className="text-muted-forground flex h-7 w-7 shrink-0 cursor-pointer border-none shadow-none sm:h-8 sm:w-8"
                                variant="secondary"
                            >
                                <EllipsisVerticalIcon className="h-4 w-4 sm:h-5 sm:w-5" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="gradient">
                            <DropdownMenuItem onClick={gotoHuntDetail}>
                                <Eye size={16} className="opacity-60" aria-hidden="true" />
                                Preview
                            </DropdownMenuItem>
                            {hunt.is_owner && (
                                <DropdownMenuItem onClick={gotoMetrics}>
                                    <BarChart size={16} className="opacity-60" aria-hidden="true" />
                                    Métricas Completas
                                </DropdownMenuItem>
                            )}
                            <DropdownMenuItem onClick={() => setOpenComments((prev) => !prev)}>
                                <MessageCircle size={16} className="opacity-60" aria-hidden="true" />
                                {isOpenComments ? 'Fechar' : 'Ver'} Comentários
                            </DropdownMenuItem>
                            <DropdownMenuItem>
                                <Repeat2 size={16} className="opacity-60" aria-hidden="true" />
                                Partilhar
                            </DropdownMenuItem>
                            {auth.user.id === hunt.owner.id ? (
                                <>
                                    <DropdownMenuItem>
                                        <Edit size={16} className="opacity-60" aria-hidden="true" />
                                        Editar
                                    </DropdownMenuItem>
                                    <DropdownMenuItem onSelect={(e) => e.preventDefault()}>
                                        <DeleteHunt hunt={hunt} />
                                    </DropdownMenuItem>
                                </>
                            ) : (
                                <>
                                    <DropdownMenuItem>
                                        <SaveIcon size={16} className="opacity-60" aria-hidden="true" />
                                        Guardar
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <StopCircle size={16} className="opacity-60" aria-hidden="true" />
                                        Ignorar
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <ShieldAlert size={16} className="opacity-60" aria-hidden="true" />
                                        Reportar
                                    </DropdownMenuItem>
                                </>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </CardHeader>
                <CardContent className="space-y-3 overflow-hidden px-3 break-words sm:space-y-4 sm:px-6">
                    <div className="cursor-pointer overflow-hidden" onClick={gotoHuntDetail}>
                        <MarkdownRenderer content={hunt.content} />
                    </div>
                    {sanitizedImageUrl && (
                        <div className="relative w-full overflow-hidden rounded-lg" style={{ height: '200px' }}>
                            <img
                                src={sanitizedImageUrl}
                                alt="Hunt image"
                                className="h-full w-full cursor-pointer object-cover transition-transform hover:scale-105"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setIsModalOpen(true);
                                }}
                            />
                        </div>
                    )}
                </CardContent>

                {/* Footer - Always show basic metrics for non-owners */}
                {!isOwner && (
                    <CardFooter className="text-muted-foreground flex flex-wrap justify-between gap-1.5 px-3 text-xs sm:gap-2 sm:px-6 sm:text-sm">
                        <HuntLikes hunt={hunt} />
                        <Button
                            variant="ghost"
                            size="sm"
                            className="flex cursor-pointer items-center gap-1 px-2 sm:px-3"
                            onClick={() => setOpenComments((prev) => !prev)}
                        >
                            <MessageCircle className="h-4 w-4 sm:h-5 sm:w-5" />
                            <span className="hidden sm:inline">{hunt.comments?.length || 0}</span>
                            <span className="sm:hidden">{hunt.comments?.length || 0}</span>
                        </Button>

                        <Button variant="ghost" size="sm" className="flex items-center gap-1 px-2 sm:px-3">
                            <Repeat2 className="h-4 w-4 sm:h-5 sm:w-5" />
                            <span className="hidden sm:inline">{hunt.shares}</span>
                            <span className="sm:hidden">{hunt.shares}</span>
                        </Button>

                        <Button variant="ghost" size="sm" className="flex items-center gap-1 px-2 sm:px-3">
                            <Eye className="h-4 w-4 sm:h-5 sm:w-5" />
                            <span className="hidden sm:inline">{hunt.views}</span>
                            <span className="sm:hidden">{hunt.views}</span>
                        </Button>
                    </CardFooter>
                )}

                {isOpenComments && (
                    <div className="mt-0 overflow-hidden">
                        <HuntComments isOpen={isOpenComments} hunt={hunt} />
                    </div>
                )}
            </Card>
        </>
    );
}
