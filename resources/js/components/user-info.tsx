import { UserAvatar } from '@/components/UserAvatar';
import { type User } from '@/types';

export function UserInfo({ user }: { user: User }) {
    return <UserAvatar avatarUrl={user.avatar_url} userName={user.name} className="ring-foreground h-8 w-8 rounded-full shadow-md" />;
}
