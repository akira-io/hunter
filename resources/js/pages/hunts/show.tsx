import { HuntCard } from '@/components/feed/HuntCard';
import { HuntMetricsDashboard } from '@/components/hunt/HuntMetricsDashboard';
import { Button } from '@/components/ui/button';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import AppLayout from '@/layouts/app-layout';
import hunts from '@/routes/hunts';
import { type BreadcrumbItem, Hunt } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface HuntShowProps {
    hunt: Hunt;
}

export default function HuntShow({ hunt }: HuntShowProps) {
    useSanitizeImageUrl(hunt.image_url);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Hunt Line',
            href: hunts.index.url(),
        },
        {
            title: `Hunt #${hunt.id}`,
            href: '#',
        },
    ];
    function goBack() {
        router.get(hunts.index.url());
    }
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Hunt by ${hunt.owner.name}`} />
            <div className="mx-auto flex h-full max-w-7xl flex-1 flex-col gap-3 p-3 sm:gap-4 sm:p-4">
                {/* Back Button */}
                <Button variant="ghost" size="sm" className="w-fit" onClick={goBack}>
                    <ArrowLeft size={16} />
                    Voltar
                </Button>

                {/* Owner Layout - Metrics First */}
                {hunt.is_owner ? (
                    <div className="flex flex-col gap-3 sm:gap-4">
                        {/* Metrics Dashboard - Full Width at Top */}
                        <HuntMetricsDashboard hunt={hunt} />
                    </div>
                ) : (
                    /* Non-Owner Layout - Just the Hunt */
                    <div className="mx-auto w-full max-w-2xl">
                        <HuntCard hunt={hunt} withOpenComments={true} />
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
