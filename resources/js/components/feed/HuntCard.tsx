import { HuntComments } from '@/components/commentable/HuntComments';
import DeleteHunt from '@/components/feed/DeleteHunt';
import { HuntModal } from '@/components/feed/HuntModal';
import { HuntLikes } from '@/components/likeable/HuntLikes';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { ReshareHunt } from '@/components/feed/ReshareHunt';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { UserAvatar } from '@/components/UserAvatar';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { cn } from '@/lib/utils';
import hunts from '@/routes/hunts';
import publicRoutes from '@/routes/public';
import { Hunt, SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import {
    BarChart,
    EllipsisVerticalIcon,
    Eye,
    Flame,
    Heart,
    Loader2,
    MessageCircle,
    Repeat2,
    ShieldAlert,
    TrendingUp,
} from 'lucide-react';
import { useState } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '../ui/dropdown-menu';

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
            <div className="absolute -top-10 right-5 flex h-10 w-1 items-center justify-center rounded-full bg-card text-xs dark:bg-zinc-900" />
        </>
    );
}

export function HuntCard({
    hunt,
    withOpenComments = false,
    width = 'default',
    showViralBanner = true,
}: HuntCardProps) {
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
            <HuntModal
                hunt={hunt}
                open={isModalOpen}
                onOpenChange={setIsModalOpen}
            />

            <Card
                className={cn(
                    'relative mx-auto mb-4 overflow-hidden',
                    widthClasses[width],
                )}
                // Only track views for hunts that don't belong to the current user
                {...(!isOwner && { 'data-pan': `hunt-${hunt.id}` })}
            >
                {/* Owner's Viral Performance Banner - Mobile Optimized - Only show in feed, not on show page */}
                {isOwner && isViral && showViralBanner && (
                    <div className="border-b border-orange-500/20 bg-gradient-to-r from-orange-500/10 via-red-500/10 to-pink-500/10 px-3 py-2 sm:px-4 sm:py-2.5">
                        <div className="flex items-center justify-between gap-2 text-xs sm:text-sm">
                            <div className="flex items-center gap-1.5 sm:gap-2">
                                <Flame className="h-3.5 w-3.5 text-orange-500 sm:h-4 sm:w-4" />
                                <span className="font-semibold text-orange-600 dark:text-orange-400">
                                    Conteúdo Viral!
                                </span>
                            </div>
                            <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                <span className="hidden sm:inline">
                                    {metrics.virality_coefficient?.toFixed(1)}%
                                    taxa de share
                                </span>
                                <span className="sm:hidden">
                                    {metrics.virality_coefficient?.toFixed(0)}%
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* Owner's Metrics Dashboard - Compact Inline Design */}
                {showViralBanner && isOwner && (
                    <div className="border-b bg-muted/30 px-3 py-2 sm:px-4 sm:py-3">
                        <div className="mb-2 flex items-center justify-between">
                            <h3 className="text-xs font-semibold tracking-wider text-muted-foreground uppercase sm:text-sm">
                                Performance
                            </h3>
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-6 gap-1 px-2 text-xs sm:h-8 sm:px-3 sm:text-sm"
                                onClick={gotoMetrics}
                            >
                                <BarChart className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                <span className="inline">Ver Detalhes</span>
                                {/*<span className="sm:hidden">Ver</span>*/}
                            </Button>
                        </div>

                        {/* Metrics - Horizontal Inline Layout */}
                        <div className="flex items-center justify-between gap-2 rounded-lg bg-background/50 p-2 sm:gap-4 sm:p-3">
                            {/* Views */}
                            <button
                                className="flex flex-col items-center gap-0.5 transition-opacity hover:opacity-80 sm:flex-row sm:gap-2"
                                onClick={gotoHuntDetail}
                            >
                                <Eye className="h-4 w-4 shrink-0 text-blue-500" />
                                <div className="flex flex-col items-center sm:flex-row sm:gap-1">
                                    <span className="text-base font-bold sm:text-lg">
                                        {metrics?.views ?? hunt.views}
                                    </span>
                                    {metrics?.engagement_rate !== undefined && (
                                        <span className="text-xs text-muted-foreground">
                                            {metrics.engagement_rate.toFixed(1)}
                                            %
                                        </span>
                                    )}
                                </div>
                            </button>

                            {/* Likes */}
                            <button
                                className="flex flex-col items-center gap-0.5 transition-opacity hover:opacity-80 sm:flex-row sm:gap-2"
                                onClick={gotoHuntDetail}
                            >
                                <Heart className="h-4 w-4 shrink-0 text-purple-500" />
                                <div className="flex flex-col items-center sm:flex-row sm:gap-1">
                                    <span className="text-base font-bold sm:text-lg">
                                        {metrics?.likes ?? hunt.likes_count}
                                    </span>
                                    {metrics?.interaction_rate !==
                                        undefined && (
                                        <span className="text-xs text-muted-foreground">
                                            {metrics.interaction_rate.toFixed(
                                                1,
                                            )}
                                            %
                                        </span>
                                    )}
                                </div>
                            </button>

                            {/* Shares */}
                            <button
                                className="flex flex-col items-center gap-0.5 transition-opacity hover:opacity-80 sm:flex-row sm:gap-2"
                                onClick={gotoHuntDetail}
                            >
                                <Repeat2 className="h-4 w-4 shrink-0 text-orange-500" />
                                <div className="flex flex-col items-center sm:flex-row sm:gap-1">
                                    <span className="text-base font-bold sm:text-lg">
                                        {metrics?.shares ?? hunt.shares}
                                    </span>
                                    {metrics?.share_rate !== undefined && (
                                        <span className="text-xs text-muted-foreground">
                                            {metrics.share_rate.toFixed(1)}%
                                        </span>
                                    )}
                                </div>
                            </button>

                            {/* Comments */}
                            <button
                                className="flex flex-col items-center gap-0.5 transition-opacity hover:opacity-80 sm:flex-row sm:gap-2"
                                onClick={() => setOpenComments((prev) => !prev)}
                            >
                                <MessageCircle className="h-4 w-4 shrink-0 text-green-500" />
                                <div className="flex flex-col items-center sm:flex-row sm:gap-1">
                                    <span className="text-base font-bold sm:text-lg">
                                        {metrics?.comments ??
                                            hunt.comments?.length ??
                                            0}
                                    </span>
                                    {metrics?.comment_rate !== undefined && (
                                        <span className="text-xs text-muted-foreground">
                                            {metrics.comment_rate.toFixed(1)}%
                                        </span>
                                    )}
                                </div>
                            </button>
                        </div>

                        {/* Quality Score Bar - Compact */}
                        {metrics.quality_score !== undefined && (
                            <div className="mt-2">
                                <div className="mb-1 flex items-center justify-between text-xs">
                                    <span className="text-muted-foreground">
                                        Score de Qualidade
                                    </span>
                                    <span className="font-semibold">
                                        {metrics.quality_score.toFixed(0)}/100
                                    </span>
                                </div>
                                <div className="h-1.5 w-full overflow-hidden rounded-full bg-muted">
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
                                        style={{
                                            width: `${metrics.quality_score}%`,
                                        }}
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
                        <CardTitle
                            className="cursor-pointer truncate text-sm font-semibold sm:text-base"
                            onClick={gotoProfile}
                        >
                            {hunt.owner.name}
                        </CardTitle>
                        <div
                            className="cursor-pointer truncate text-xs text-muted-foreground sm:text-sm"
                            onClick={() =>
                                router.get(
                                    publicRoutes.profile.show.url(
                                        hunt.owner.id,
                                    ),
                                )
                            }
                        >
                            @{hunt.owner.user_name || hunt.owner.name} ·{' '}
                            {hunt.created_at}
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
                                <Eye
                                    size={16}
                                    className="opacity-60"
                                    aria-hidden="true"
                                />
                                Ver
                            </DropdownMenuItem>
                            {hunt.is_owner && (
                                <DropdownMenuItem onClick={gotoMetrics}>
                                    <BarChart
                                        size={16}
                                        className="opacity-60"
                                        aria-hidden="true"
                                    />
                                    Métricas Completas
                                </DropdownMenuItem>
                            )}
                            <DropdownMenuItem
                                onClick={() => setOpenComments((prev) => !prev)}
                            >
                                <MessageCircle
                                    size={16}
                                    className="opacity-60"
                                    aria-hidden="true"
                                />
                                {isOpenComments ? 'Fechar' : 'Ver'} Comentários
                            </DropdownMenuItem>
                            {/*<DropdownMenuItem>*/}
                            {/*    <Repeat2 size={16} className="opacity-60" aria-hidden="true" />*/}
                            {/*    Partilhar*/}
                            {/*</DropdownMenuItem>*/}
                            {auth.user.id === hunt.owner.id ? (
                                <>
                                    {/*<DropdownMenuItem>*/}
                                    {/*    <Edit size={16} className="opacity-60" aria-hidden="true" />*/}
                                    {/*    Editar*/}
                                    {/*</DropdownMenuItem>*/}
                                    <DropdownMenuItem
                                        onSelect={(e) => e.preventDefault()}
                                    >
                                        <DeleteHunt hunt={hunt} />
                                    </DropdownMenuItem>
                                </>
                            ) : (
                                <>
                                    {/*<DropdownMenuItem>*/}
                                    {/*    <SaveIcon size={16} className="opacity-60" aria-hidden="true" />*/}
                                    {/*    Guardar*/}
                                    {/*</DropdownMenuItem>*/}
                                    {/*<DropdownMenuItem>*/}
                                    {/*    <StopCircle size={16} className="opacity-60" aria-hidden="true" />*/}
                                    {/*    Ignorar*/}
                                    {/*/!*</DropdownMenuItem>*!/*/}
                                    {/*<DropdownMenuItem>*/}
                                    {/*    <ShieldAlert size={16} className="opacity-60" aria-hidden="true" />*/}
                                    {/*    Reportar*/}
                                    {/*</DropdownMenuItem>*/}
                                </>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </CardHeader>
                <CardContent className="space-y-3 overflow-hidden px-3 break-words sm:space-y-4 sm:px-6">
                    {hunt.content && (
                        <div
                            className="cursor-pointer overflow-hidden"
                            onClick={gotoHuntDetail}
                        >
                            <MarkdownRenderer content={hunt.content} />
                        </div>
                    )}
                    {/* Image or Processing State */}
                    {hunt.image_processing_status === 'pending' ||
                    hunt.image_processing_status === 'processing' ? (
                        <div
                            className="relative w-full overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800"
                            style={{ height: '200px' }}
                        >
                            <div className="flex h-full w-full items-center justify-center">
                                <div className="text-center">
                                    <Loader2 className="mx-auto mb-2 h-8 w-8 animate-spin text-purple-500" />
                                    <p className="text-sm text-zinc-600 dark:text-zinc-400">
                                        {hunt.image_processing_status ===
                                        'pending'
                                            ? 'Preparando imagem...'
                                            : 'Processando imagem...'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : hunt.image_processing_status === 'failed' ? (
                        <div
                            className="relative w-full overflow-hidden rounded-lg border-2 border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/20"
                            style={{ height: '200px' }}
                        >
                            <div className="flex h-full w-full items-center justify-center">
                                <div className="text-center">
                                    <ShieldAlert className="mx-auto mb-2 h-8 w-8 text-red-500" />
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        Falha ao processar imagem
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : sanitizedImageUrl ? (
                        <div
                            className="relative w-full overflow-hidden rounded-lg"
                            style={{ height: '200px' }}
                        >
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
                    ) : null}
                </CardContent>

                {/* Footer - Always show basic metrics for non-owners */}
                {!isOwner && (
                    <CardFooter className="flex flex-wrap justify-between gap-1.5 px-3 text-xs text-muted-foreground sm:gap-2 sm:px-6 sm:text-sm">
                        <HuntLikes hunt={hunt} />
                        <Button
                            variant="ghost"
                            size="sm"
                            className="flex cursor-pointer items-center gap-1 px-2 text-green-500 sm:px-3"
                            onClick={() => setOpenComments((prev) => !prev)}
                        >
                            <MessageCircle className="h-4 w-4 sm:h-5 sm:w-5" />
                            <span className="hidden sm:inline">
                                {hunt.comments?.length || 0}
                            </span>
                            <span className="sm:hidden">
                                {hunt.comments?.length || 0}
                            </span>
                        </Button>

                        <ReshareHunt hunt={hunt} />

                        <Button
                            variant="ghost"
                            size="sm"
                            className="flex items-center gap-1 px-2 text-blue-500 sm:px-3"
                        >
                            <Eye className="h-4 w-4 sm:h-5 sm:w-5" />
                            <span className="hidden sm:inline">
                                {hunt.views}
                            </span>
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
