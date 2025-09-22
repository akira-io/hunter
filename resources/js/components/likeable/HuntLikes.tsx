import { LikeButton } from '@/components/likeable/LikeButton';
import { Hunt } from '@/types';
import { useForm } from '@inertiajs/react';
import hunts from '@/routes/hunts';

interface LikesProps {
    hunt: Hunt;
}

export function HuntLikes({ hunt }: LikesProps) {
    const { post } = useForm();

    function handleLike() {
        post(hunts.toggleLike.url(hunt.id), {
            preserveScroll: true,
            preserveState: true,
        });
    }

    return <LikeButton count={hunt.likes_count} hasLiked={hunt.has_liked} onLike={handleLike} />;
}
