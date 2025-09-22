import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { type User } from '@/types';

export function UserInfo({ user }: { user: User }) {
    const getInitials = useInitials();
    const sanitizedAvatarUrl = useSanitizeImageUrl(user.avatar_url);

    return (
        <>
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarImage src={sanitizedAvatarUrl} alt={user.name} className="object-cover" />
                <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                    {getInitials(user.name)}
                </AvatarFallback>
            </Avatar>
        </>
    );
}
