import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { type User } from '@/types';

export function UserInfo({ user }: { user: User }) {
    const getInitials = useInitials();
    const sanitizedAvatarUrl = useSanitizeImageUrl(user.avatar_url);

    return (
        <Avatar className='h-8 w-8 overflow-hidden rounded-full ring-2 ring-background shadow-sm'>
            <AvatarImage src={sanitizedAvatarUrl} alt={user.name} className='object-cover' />
            <AvatarFallback className='rounded-full bg-gradient-to-br from-purple-500 to-purple-800 text-white font-semibold'>
                {getInitials(user.name)}
            </AvatarFallback>
        </Avatar>
    );
}
