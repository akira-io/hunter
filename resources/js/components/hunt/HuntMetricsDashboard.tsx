import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Hunt } from '@/types';
import { Activity, Eye, Heart, MessageCircle, Repeat2, Star, Target, TrendingUp, Zap } from 'lucide-react';

interface HuntMetricsDashboardProps {
    hunt: Hunt;
}

function PerformanceBadge({ level }: { level: string }) {
    const badgeConfig = {
        poor: { label: 'Fraco', className: 'bg-red-500/10 text-red-500 border-red-500/20' },
        below_average: { label: 'Abaixo', className: 'bg-orange-500/10 text-orange-500 border-orange-500/20' },
        average: { label: 'Médio', className: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20' },
        good: { label: 'Bom', className: 'bg-green-500/10 text-green-500 border-green-500/20' },
        excellent: { label: 'Excelente', className: 'bg-blue-500/10 text-blue-500 border-blue-500/20' },
    };

    const config = badgeConfig[level as keyof typeof badgeConfig] || badgeConfig.poor;

    return <Badge className={`${config.className} border text-xs`}>{config.label}</Badge>;
}

function StarRating({ rank }: { rank: number }) {
    return (
        <div className="flex items-center gap-0.5">
            {Array.from({ length: 5 }).map((_, i) => (
                <Star key={i} size={14} className={i < rank ? 'fill-yellow-500 text-yellow-500' : 'text-muted-foreground/20'} />
            ))}
        </div>
    );
}

interface MetricCardProps {
    icon: React.ReactNode;
    label: string;
    value: number | string;
    color: string;
    bgColor: string;
}

function MetricCard({ icon, label, value, color, bgColor }: MetricCardProps) {
    return (
        <Card className={`${bgColor} border-none`}>
            <CardContent className="flex items-center gap-3 p-4 sm:gap-4 sm:p-6">
                <div className={`rounded-full ${color} bg-white/10 p-2 sm:p-3 dark:bg-black/10`}>{icon}</div>
                <div className="flex min-w-0 flex-1 flex-col">
                    <span className="text-muted-foreground truncate text-xs sm:text-sm">{label}</span>
                    <span className="text-xl font-bold sm:text-2xl">{value}</span>
                </div>
            </CardContent>
        </Card>
    );
}

interface RateCardProps {
    label: string;
    value: string;
    icon: React.ReactNode;
    color: string;
}

function RateCard({ label, value, icon, color }: RateCardProps) {
    return (
        <div className="bg-muted/50 flex items-center justify-between rounded-lg p-3 sm:p-4">
            <div className="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                <div className={`${color} flex-shrink-0`}>{icon}</div>
                <span className="truncate text-xs font-medium sm:text-sm">{label}</span>
            </div>
            <span className="ml-2 flex-shrink-0 text-base font-bold sm:text-lg">{value}</span>
        </div>
    );
}

export function HuntMetricsDashboard({ hunt }: HuntMetricsDashboardProps) {
    if (!hunt.is_owner || !hunt.metrics) {
        return null;
    }

    const { metrics } = hunt;

    return (
        <div className="mb-4 space-y-4 sm:mb-6 sm:space-y-6">
            {/* Viral Alert */}
            {metrics.is_viral && (
                <Card className="border-2 border-purple-500 bg-purple-500/5">
                    <CardContent className="p-4 sm:p-6">
                        <div className="flex items-start gap-3 sm:items-center sm:gap-4">
                            <div className="flex-shrink-0 rounded-full bg-purple-500/10 p-3 sm:p-4">
                                <Zap size={24} className="animate-pulse text-purple-500 sm:h-8 sm:w-8" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <h3 className="mb-1 text-base font-bold text-purple-500 sm:text-xl">🔥 Conteúdo Viral!</h3>
                                <p className="text-muted-foreground text-xs sm:text-sm">
                                    Este hunt está com performance excepcional! Continue criando conteúdo de qualidade para manter o engajamento alto.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Hero Section - Performance Overview */}
            <Card className="gradient border-2">
                <CardHeader className="pb-3 sm:pb-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
                            <Activity size={20} className="text-purple-500 sm:h-6 sm:w-6" />
                            <span className="truncate">Performance</span>
                        </CardTitle>
                        <div className="flex items-center gap-2 sm:gap-3">
                            <PerformanceBadge level={metrics.performance_level} />
                            <StarRating rank={metrics.rank} />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <MetricCard
                            icon={<Eye size={20} className="sm:h-6 sm:w-6" />}
                            label="Visualizações"
                            value={metrics.views}
                            color="text-blue-500"
                            bgColor="bg-blue-500/5"
                        />
                        <MetricCard
                            icon={<Heart size={20} className="sm:h-6 sm:w-6" />}
                            label="Likes"
                            value={hunt.likes_count}
                            color="text-purple-500"
                            bgColor="bg-purple-500/5"
                        />
                        <MetricCard
                            icon={<MessageCircle size={20} className="sm:h-6 sm:w-6" />}
                            label="Comentários"
                            value={hunt.comments?.length || 0}
                            color="text-green-500"
                            bgColor="bg-green-500/5"
                        />
                        <MetricCard
                            icon={<Repeat2 size={20} className="sm:h-6 sm:w-6" />}
                            label="Partilhas"
                            value={metrics.shares}
                            color="text-orange-500"
                            bgColor="bg-orange-500/5"
                        />
                    </div>
                </CardContent>
            </Card>

            {/* Detailed Metrics Grid */}
            <div className="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
                {/* Engagement Rates */}
                <Card className="gradient">
                    <CardHeader className="pb-3 sm:pb-6">
                        <CardTitle className="flex items-center gap-2 text-sm sm:text-base">
                            <TrendingUp size={16} className="sm:h-[18px] sm:w-[18px]" />
                            Nivel
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-2 sm:space-y-3">
                        <RateCard
                            label="Engajamento"
                            value={`${metrics.engagement_rate.toFixed(1)}%`}
                            icon={<Zap size={16} className="sm:h-[18px] sm:w-[18px]" />}
                            color="text-blue-500"
                        />
                        <RateCard
                            label="Interaç˜ões"
                            value={`${metrics.interaction_rate.toFixed(1)}%`}
                            icon={<Heart size={16} className="sm:h-[18px] sm:w-[18px]" />}
                            color="text-purple-500"
                        />
                        <RateCard
                            label="Comentários"
                            value={`${metrics.comment_rate.toFixed(1)}%`}
                            icon={<MessageCircle size={16} className="sm:h-[18px] sm:w-[18px]" />}
                            color="text-green-500"
                        />
                        <RateCard
                            label="Partilhas"
                            value={`${metrics.share_rate.toFixed(1)}%`}
                            icon={<Repeat2 size={16} className="sm:h-[18px] sm:w-[18px]" />}
                            color="text-orange-500"
                        />
                    </CardContent>
                </Card>

                {/* Quality & Performance */}
                <Card className="gradient">
                    <CardHeader className="pb-3 sm:pb-6">
                        <CardTitle className="flex items-center gap-2 text-sm sm:text-base">
                            <Target size={16} className="sm:h-[18px] sm:w-[18px]" />
                            Qualidade
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 sm:space-y-4">
                        <div className="space-y-2">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-muted-foreground text-xs sm:text-sm">Pontos de Qualidade</span>
                                <span className="text-xl font-bold text-purple-500 sm:text-2xl">{metrics.quality_score.toFixed(1)}/100</span>
                            </div>
                            <div className="bg-muted h-2 w-full overflow-hidden rounded-full sm:h-3">
                                <div
                                    className="h-full rounded-full bg-gradient-to-r from-purple-500 to-blue-500 transition-all"
                                    style={{ width: `${metrics.quality_score}%` }}
                                />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-muted-foreground truncate text-xs sm:text-sm">Coef. Viralidade</span>
                                <span className="flex-shrink-0 text-lg font-bold sm:text-xl">{(metrics.virality_coefficient * 100).toFixed(1)}%</span>
                            </div>
                            <div className="bg-muted h-2 w-full overflow-hidden rounded-full sm:h-3">
                                <div
                                    className="h-full rounded-full bg-gradient-to-r from-orange-500 to-red-500 transition-all"
                                    style={{ width: `${Math.min(metrics.virality_coefficient * 100, 100)}%` }}
                                />
                            </div>
                        </div>

                        <div className="bg-muted/50 flex items-center justify-between gap-2 rounded-lg p-3 sm:p-4">
                            <span className="text-muted-foreground truncate text-xs sm:text-sm">Engaj. Médio/View</span>
                            <span className="flex-shrink-0 text-base font-bold sm:text-lg">{metrics.avg_engagement_per_view.toFixed(2)}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Performance Tips */}
            {metrics.is_performing_well && !metrics.is_viral && (
                <Card className="border-2 border-green-500/50 bg-green-500/5">
                    <CardContent className="p-4 sm:p-6">
                        <div className="flex items-start gap-3 sm:items-center sm:gap-4">
                            <div className="flex-shrink-0 rounded-full bg-green-500/10 p-3 sm:p-4">
                                <TrendingUp size={24} className="text-green-500 sm:h-8 sm:w-8" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <h3 className="mb-1 text-base font-bold text-green-500 sm:text-xl">✨ Ótima Performance!</h3>
                                <p className="text-muted-foreground text-xs sm:text-sm">
                                    Este hunt está performando bem! Continue assim para aumentar ainda mais o alcance.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
