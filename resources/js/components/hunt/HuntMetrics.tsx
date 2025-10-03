import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Hunt } from '@/types';
import { BarChart3, Eye, Heart, MessageCircle, Repeat2, TrendingUp } from 'lucide-react';
import { ReactNode } from 'react';

interface HuntMetricsProps {
    hunt: Hunt;
}

interface MetricItemProps {
    icon: ReactNode;
    label: string;
    value: number | string;
    color?: string;
}

function MetricItem({ icon, label, value, color = 'text-primary' }: MetricItemProps) {
    return (
        <div className="flex items-center justify-between py-3">
            <div className="flex items-center gap-3">
                <div className={`${color} opacity-80`}>{icon}</div>
                <span className="text-muted-foreground text-sm">{label}</span>
            </div>
            <span className="font-semibold">{value}</span>
        </div>
    );
}

export function HuntMetrics({ hunt }: HuntMetricsProps) {
    const totalEngagements = (hunt.likes_count || 0) + (hunt.comments?.length || 0);
    const engagementRate = hunt.views > 0 ? ((totalEngagements / hunt.views) * 100).toFixed(1) : '0.0';

    return (
        <div className="sticky top-4 space-y-4">
            <Card className="gradient">
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-base">
                        <BarChart3 size={18} />
                        Métricas
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-2">
                    <MetricItem icon={<Eye size={18} />} label="Visualizações" value={hunt.views || 0} color="text-blue-500" />
                    <Separator />
                    <MetricItem icon={<Heart size={18} />} label="Likes" value={hunt.likes_count || 0} color="text-purple-500" />
                    <Separator />
                    <MetricItem icon={<MessageCircle size={18} />} label="Comentários" value={hunt.comments?.length || 0} color="text-green-500" />
                    <Separator />
                    <MetricItem icon={<Repeat2 size={18} />} label="Partilhas" value={hunt.shares || 0} color="text-orange-500" />
                    <Separator />
                    <MetricItem icon={<TrendingUp size={18} />} label="Engajamento" value={`${engagementRate}%`} color="text-purple-500" />
                </CardContent>
            </Card>
        </div>
    );
}
