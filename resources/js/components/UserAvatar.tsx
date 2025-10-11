import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { cn } from '@/lib/utils';
import { type ComponentProps, useEffect, useState } from 'react';

interface UserAvatarProps extends ComponentProps<typeof Avatar> {
    avatarUrl?: string | null;
    userName: string;
    fallbackClassName?: string;
}

/**
 * UserAvatar component - Always shows a fallback with user initials
 * if the avatar_url is missing or fails to load
 */
export function UserAvatar({
    avatarUrl,
    userName,
    fallbackClassName,
    className,
    ...props
}: UserAvatarProps) {
    const getInitials = useInitials();
    const sanitizedAvatarUrl = useSanitizeImageUrl(avatarUrl);
    const [imageStatus, setImageStatus] = useState<
        'loading' | 'loaded' | 'error'
    >('loading');

    // Reset and test image quando a URL muda
    useEffect(() => {
        if (!sanitizedAvatarUrl) {
            setImageStatus('error');
            return;
        }

        setImageStatus('loading');

        // Testa se a imagem pode carregar
        const img = new Image();

        const timeout = setTimeout(() => {
            setImageStatus('error');
        }, 5000); // 5 seconds timeout

        img.onload = () => {
            clearTimeout(timeout);
            setImageStatus('loaded');
        };

        img.onerror = () => {
            clearTimeout(timeout);
            setImageStatus('error');
        };

        img.src = sanitizedAvatarUrl;

        return () => clearTimeout(timeout);
    }, [sanitizedAvatarUrl]);

    // Só mostra imagem se carregou com sucesso
    const shouldShowImage = sanitizedAvatarUrl && imageStatus === 'loaded';

    return (
        <Avatar className={cn('overflow-hidden', className)} {...props}>
            {shouldShowImage ? (
                <AvatarImage
                    src={sanitizedAvatarUrl}
                    alt={userName}
                    className="object-cover"
                />
            ) : (
                <AvatarFallback
                    className={cn(
                        'bg-gradient-to-br from-purple-500 to-purple-800 font-semibold text-white',
                        fallbackClassName,
                    )}
                >
                    {getInitials(userName)}
                </AvatarFallback>
            )}
        </Avatar>
    );
}
