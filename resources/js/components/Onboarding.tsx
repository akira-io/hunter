import { FollowButton } from '@/components/followable/FollowButton';
import UnfollowButton from '@/components/followable/UnfollowButton';
import { SocialDropdownMenu } from '@/components/hunter/SocialDropdownMenu';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardTitle } from '@/components/ui/card';
import { useProfile } from '@/hooks/use-profile';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import { useStartConversation } from '@/hooks/use-start-conversation';
import { useTruncate } from '@/hooks/use-truncate-text';
import { SharedData, User } from '@/types';
import { usePage } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';
import * as React from 'react';
import AvatarGenerator, { genConfig } from 'react-nice-avatar';

interface OnboardingProps extends React.ComponentProps<'div'> {
    user: User;
    hasFollowed?: boolean;
}

interface OnboardingAvatarProps {
    avatarUrl: string | undefined;
    size?: number;
    onClick?: () => void;
}

export function OnboardingAvatar({ avatarUrl, onClick, size = 16 }: OnboardingAvatarProps) {
    const avatarSize = size * 4;
    const config = genConfig({ sex: 'man', hairStyle: 'thick' });
    const sanitizedAvatarUrl = useSanitizeImageUrl(avatarUrl);

    return (
        <Avatar style={{ height: `${avatarSize}px`, width: `${avatarSize}px` }} className="shadow" onClick={onClick}>
            {sanitizedAvatarUrl ? (
                <AvatarImage
                    src={sanitizedAvatarUrl}
                    alt={sanitizedAvatarUrl}
                    className="rounded-full object-cover"
                    style={{ height: `${avatarSize}px`, width: `${avatarSize}px` }}
                />
            ) : (
                <AvatarGenerator style={{ width: `${avatarSize}px`, height: `${avatarSize}px` }} {...config} />
            )}
        </Avatar>
    );
}

export default function Onboarding({ user, hasFollowed, ...props }: OnboardingProps) {
    const { auth } = usePage<SharedData>().props;
    const { startConversation, isStarting } = useStartConversation();
    const { truncate } = useTruncate();
    const { showProfile } = useProfile(user);

    // Use hasFollowed prop if provided, otherwise fallback to user.has_followed
    const has_followed = hasFollowed !== undefined ? hasFollowed : user.has_followed;

    const handleMessageClick = async (e: React.MouseEvent) => {
        e.stopPropagation();
        await startConversation(user.id);
    };

    return (
        <div {...props}>
            <Card className="relative min-h-40 w-full cursor-pointer overflow-hidden">
                <CardContent className="flex w-full flex-1 items-center justify-center gap-2">
                    <OnboardingAvatar avatarUrl={user.avatar_url} onClick={showProfile} />
                    <div className="w-full">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-xl" onClick={showProfile}>
                                {user.name}
                            </CardTitle>
                            <SocialDropdownMenu user={user} hasFollowed={has_followed} />
                        </div>
                        <CardDescription className="text-sm">
                            {user.bio ? truncate(user.bio, 45) : <span className="text-muted">Bio indisponível...</span>}
                        </CardDescription>
                    </div>
                </CardContent>
                {auth.user && auth.user.id !== user.id && (
                    <CardFooter className="flex justify-between gap-2">
                        <Button variant="outline" size="sm" onClick={handleMessageClick} disabled={isStarting} className="flex items-center gap-2">
                            <MessageCircle size={16} />
                            {isStarting ? 'Abrindo...' : 'Mensagem'}
                        </Button>
                        <div className="flex-1" />
                        {!has_followed && <FollowButton user={user} />}
                        {has_followed && <UnfollowButton user={user} />}
                    </CardFooter>
                )}
            </Card>
        </div>
    );
}
