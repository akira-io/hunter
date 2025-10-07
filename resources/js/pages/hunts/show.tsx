import { HuntCard } from '@/components/feed/HuntCard';
import { HuntMetrics } from '@/components/hunt/HuntMetrics';
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
            <div className="mx-auto flex h-full max-w-5xl flex-1 flex-col gap-4 p-4">
                {/* Back Button */}
                <Button variant="ghost" size="sm" className="w-fit" onClick={goBack}>
                    <ArrowLeft size={16} />
                    Voltar
                </Button>

                {/* Two Column Layout on Desktop */}
                <div className="grid grid-cols-1 gap-0 md:gap-4 lg:grid-cols-3">
                    <div className={`flex flex-col gap-0 ${hunt.is_owner ? 'lg:col-span-2' : 'lg:col-span-3'}`}>
                        <HuntCard hunt={hunt} withOpenComments={true} />
                    </div>

                    {hunt.is_owner && (
                        <div className="lg:col-span-1">
                            <HuntMetrics hunt={hunt} />
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
