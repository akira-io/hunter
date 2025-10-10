import { Button } from '@/components/ui/button';
import { useSocialActions } from '@/hooks/use-social-actions';
import { cn } from '@/lib/utils';
import { User } from '@/types';
import { UserPlusIcon } from 'lucide-react';
import { HTMLAttributes } from 'react';

interface FollowButtonProps extends HTMLAttributes<HTMLButtonElement> {
    user: User;
}

export function FollowButton({ user, className }: FollowButtonProps) {
    const { handleFollow, processing } = useSocialActions(user);

    return (
        <Button
            className={cn(className)}
            size="sm"
            variant="default"
            onClick={handleFollow}
            disabled={processing}
        >
            <UserPlusIcon />
            Seguir
        </Button>
    );
}
