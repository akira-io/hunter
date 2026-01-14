import { HuntCard } from '@/components/feed/HuntCard';
import { UserAvatar } from '@/components/UserAvatar';
import publicRoutes from '@/routes/public';
import { Hunt, User } from '@/types';
import { router } from '@inertiajs/react';
import { Repeat2 } from 'lucide-react';

interface ResharedHuntCardProps {
    hunt: Hunt;
    resharer: User;
}

export function ResharedHuntCard({ hunt, resharer }: ResharedHuntCardProps) {
    const gotoProfile = () => {
        router.get(publicRoutes.profile.show.url(resharer.id));
    };

    return (
        <div className="relative mb-4">
            <div className="absolute left-4 top-2 z-10 flex items-center gap-2 text-xs text-muted-foreground">
                <Repeat2 className="h-4 w-4" />
                <span className="cursor-pointer" onClick={gotoProfile}>
                    {resharer.name} reshared
                </span>
            </div>
            <div className="pt-8">
                <HuntCard hunt={hunt} />
            </div>
        </div>
    );
}
