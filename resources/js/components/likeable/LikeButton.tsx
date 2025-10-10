import { cn } from '@/lib/utils';
import { Heart } from 'lucide-react';
import { useState } from 'react';

interface LikeButtonProps {
    count: number;
    hasLiked: boolean;
    onLike: () => void;
    iconSize?: number;
}

export function LikeButton({
    count,
    hasLiked,
    onLike,
    iconSize = 20,
}: LikeButtonProps) {
    const [isAnimating, setIsAnimating] = useState(false);

    const handleClick = () => {
        onLike();
        setIsAnimating(true);
        setTimeout(() => setIsAnimating(false), 300);
    };

    return (
        <div className="flex items-center gap-2">
            <Heart
                onClick={handleClick}
                size={iconSize}
                className={cn(
                    'cursor-pointer transition-transform duration-300',
                    {
                        'scale-150 text-primary': isAnimating,
                        'fill-primary': hasLiked,
                    },
                )}
            />
            <span>{count}</span>
        </div>
    );
}
