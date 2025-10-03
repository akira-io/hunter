import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Hunt } from '@/types';
import { BarChart3, Eye, Heart, MessageCircle, Repeat2, TrendingUp, UserPlus } from 'lucide-react';
import { ReactNode } from 'react';

interface HuntMetricsProps {
    hunt: Hunt;
}

interface MetricCardProps {
    icon: ReactNode;
    label: string;
    value: number | string;
    change?: string;
    trend?: 'up' | 'down' | 'neutral';
}

function MetricCard({ icon, label, value, change, trend = 'neutral' }: MetricCardProps) {
    const trendColors = {
        up: 'text-green-500',
        down: 'text-red-500',
        neutral: 'text-muted-foreground',
    };

    return (
        <Card className="gradient">
            <CardContent className="flex items-center gap-4 p-4">
                <div className="bg-primary/10 flex h-12 w-12 items-center justify-center rounded-full">{icon}</div>
                <div className="flex-1">
                    <p className="text-muted-foreground text-sm">{label}</p>
                    <p className="text-2xl font-bold">{value}</p>
                    {change && (
                        <p className={`text-xs ${trendColors[trend]}`}>
                            {trend === 'up' && '↑'} {trend === 'down' && '↓'} {change}
                        </p>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

export function HuntMetrics({ hunt }: HuntMetricsProps) {

    const totalEngagements = (hunt.likes_count || 0) + (hunt.comments?.length || 0);
    const engagementRate = hunt.views > 0 ? ((totalEngagements / hunt.views) * 100).toFixed(1) : '0.0';

    return (
        <div className="space-y-4">
            <Card className="gradient">
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <BarChart3 size={20} />
                        Métricas da Hunt
                    </CardTitle>
                </CardHeader>
            </Card>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <MetricCard icon={<Eye size={20} className="text-primary" />} label="Visualizações" value={hunt.views || 0} />

                <MetricCard icon={<Heart size={20} className="text-pink-500" />} label="Likes" value={hunt.likes_count || 0} />

                <MetricCard icon={<MessageCircle size={20} className="text-blue-500" />} label="Comentários" value={hunt.comments?.length || 0} />

                <MetricCard icon={<Repeat2 size={20} className="text-green-500" />} label="Partilhas" value={hunt.shares || 0} />
            </div>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <MetricCard icon={<TrendingUp size={20} className="text-orange-500" />} label="Taxa de Engajamento" value={`${engagementRate}%`} trend="neutral" />

                <MetricCard
                    icon={<UserPlus size={20} className="text-purple-500" />}
                    label="Alcance do Autor"
                    value={(hunt.owner as any).followers_count || 0}
                    change="seguidores"
                />
            </div>
        </div>
    );
}
