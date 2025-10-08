import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Hunt } from '@/types';
import { BarChart3, Eye, Heart, MessageCircle, Repeat2, Star, Target, TrendingUp, Zap } from 'lucide-react';
import { ReactNode } from 'react';

interface HuntMetricsProps {
    hunt: Hunt;
}

interface MetricItemProps {
    icon: ReactNode;
    label: string;
    value: number | string;
    color?: string;
    subtitle?: string;
}

function MetricItem({ icon, label, value, color = 'text-primary', subtitle }: MetricItemProps) {
    return (
        <div className="flex items-center justify-between py-3">
            <div className="flex items-center gap-3">
                <div className={`${color} opacity-80`}>{icon}</div>
                <div className="flex flex-col">
                    <span className="text-muted-foreground text-sm">{label}</span>
                    {subtitle && <span className="text-muted-foreground/60 text-xs">{subtitle}</span>}
                </div>
            </div>
            <span className="font-semibold">{value}</span>
        </div>
    );
}

function PerformanceBadge({ level }: { level: string }) {
    const badgeConfig = {
        poor: { label: 'Fraco', className: 'bg-red-500/10 text-red-500' },
        below_average: { label: 'Abaixo da Média', className: 'bg-orange-500/10 text-orange-500' },
        average: { label: 'Médio', className: 'bg-yellow-500/10 text-yellow-500' },
        good: { label: 'Bom', className: 'bg-green-500/10 text-green-500' },
        excellent: { label: 'Excelente', className: 'bg-blue-500/10 text-blue-500' },
    };

    const config = badgeConfig[level as keyof typeof badgeConfig] || badgeConfig.poor;

    return <Badge className={config.className}>{config.label}</Badge>;
}

function StarRating({ rank }: { rank: number }) {
    return (
        <div className="flex items-center gap-1">
            {Array.from({ length: 5 }).map((_, i) => (
                <Star key={i} size={14} className={i < rank ? 'fill-yellow-500 text-yellow-500' : 'text-muted-foreground/20'} />
            ))}
        </div>
    );
}

export function HuntMetrics({ hunt }: HuntMetricsProps) {
    // Only display if user is the owner and has metrics
    if (!hunt.is_owner || !hunt.metrics) {
        return null;
    }

    const { metrics } = hunt;

    return (
        <div className="sticky top-4 space-y-4">
            {/* Basic Metrics Card */}
            <Card className="gradient">
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-base">
                        <BarChart3 size={18} />
                        Métricas Básicas
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-2">
                    <MetricItem icon={<Eye size={18} />} label="Visualizações" value={hunt.views} color="text-blue-500" />
                    <Separator />
                    <MetricItem icon={<Heart size={18} />} label="Likes" value={hunt.likes_count} color="text-purple-500" />
                    <Separator />
                    <MetricItem icon={<MessageCircle size={18} />} label="Comentários" value={hunt.comments?.length || 0} color="text-green-500" />
                    <Separator />
                    <MetricItem icon={<Repeat2 size={18} />} label="Partilhas" value={hunt.shares} color="text-orange-500" />
                </CardContent>
            </Card>

            {/* Performance Card */}
            <Card className="gradient">
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-base">
                        <Target size={18} />
                        Performance
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="flex items-center justify-between">
                        <span className="text-muted-foreground text-sm">Nível de Performance</span>
                        <PerformanceBadge level={metrics.performance_level} />
                    </div>
                    <Separator />
                    <div className="flex items-center justify-between">
                        <span className="text-muted-foreground text-sm">Classificação</span>
                        <StarRating rank={metrics.rank} />
                    </div>
                    <Separator />
                    <MetricItem
                        icon={<TrendingUp size={18} />}
                        label="Quality Score"
                        value={`${metrics.quality_score.toFixed(1)}/100`}
                        color="text-purple-500"
                        subtitle="Score ponderado de qualidade"
                    />
                </CardContent>
            </Card>

            {/* Engagement Rates Card */}
            <Card className="gradient">
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-base">
                        <TrendingUp size={18} />
                        Taxas de Engajamento
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-2">
                    <MetricItem
                        icon={<Zap size={18} />}
                        label="Engagement Rate"
                        value={`${metrics.engagement_rate.toFixed(1)}%`}
                        color="text-blue-500"
                        subtitle="Total de interações"
                    />
                    <Separator />
                    <MetricItem
                        icon={<Heart size={18} />}
                        label="Interaction Rate"
                        value={`${metrics.interaction_rate.toFixed(1)}%`}
                        color="text-purple-500"
                        subtitle="Likes + Comentários"
                    />
                    <Separator />
                    <MetricItem
                        icon={<MessageCircle size={18} />}
                        label="Comment Rate"
                        value={`${metrics.comment_rate.toFixed(1)}%`}
                        color="text-green-500"
                        subtitle="Taxa de comentários"
                    />
                    <Separator />
                    <MetricItem
                        icon={<Repeat2 size={18} />}
                        label="Share Rate"
                        value={`${metrics.share_rate.toFixed(1)}%`}
                        color="text-orange-500"
                        subtitle="Taxa de partilhas"
                    />
                </CardContent>
            </Card>

            {/* Virality Card */}
            {metrics.is_viral && (
                <Card className="gradient border-2 border-purple-500/50">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base text-purple-500">
                            <Zap size={18} className="animate-pulse" />
                            Conteúdo Viral! 🔥
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-muted-foreground text-sm">
                            Este hunt está viral! Coeficiente de viralidade:{' '}
                            <span className="font-semibold">{(metrics.virality_coefficient * 100).toFixed(1)}%</span>
                        </p>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
