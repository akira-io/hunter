import { HuntComments } from '@/components/commentable/HuntComments';
import DeleteHunt from '@/components/feed/DeleteHunt';
import { HuntLikes } from '@/components/likeable/HuntLikes';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import publicRoutes from '@/routes/public';
import { Hunt, SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { BarChart, Edit, EllipsisVerticalIcon, MessageCircle, Repeat2, SaveIcon, Share2Icon, ShieldAlert, StopCircle } from 'lucide-react';
import { useState } from 'react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '../ui/dropdown-menu';

interface HuntCardProps {
    hunt: Hunt;
    ligatures?: boolean;
}

export function HuntCardConnector() {
    return (
        <>
            <div className="absolute -top-10 left-5 flex h-10 w-1 items-center justify-center rounded-full bg-white text-xs dark:bg-zinc-900" />
            <div className="bg-card absolute -top-10 right-5 flex h-10 w-1 items-center justify-center rounded-full text-xs dark:bg-zinc-900" />
        </>
    );
}

export function HuntCard({ hunt }: HuntCardProps) {
    const { auth } = usePage<SharedData>().props;
    const [isOpenComments, setOpenComments] = useState(false);

    const sanitizedAvatarUrl = useSanitizeImageUrl(hunt.owner.avatar_url);
    const sanitizedImageUrl = useSanitizeImageUrl(hunt.image_url);

    function gotoProfile() {
        router.get(publicRoutes.profile.show.url(hunt.owner.id));
    }

    return (
        <>
            <Card className="relative mx-auto mb-4 w-full max-w-xl">
                <CardHeader className="flex flex-row items-start gap-4">
                    <Avatar onClick={gotoProfile}>
                        <AvatarImage src={sanitizedAvatarUrl} className="object-cover" />
                        <AvatarFallback>U</AvatarFallback>
                    </Avatar>
                    <div className="flex flex-col">
                        <CardTitle className="cursor-pointer text-base font-semibold" onClick={gotoProfile}>
                            {hunt.owner.name}
                        </CardTitle>
                        <div
                            className="text-muted-foreground cursor-pointer text-sm"
                            onClick={() => router.get(publicRoutes.profile.show.url(hunt.owner.id))}
                        >
                            @{hunt.owner.user_name || hunt.owner.name} · {hunt.created_at}
                        </div>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                className="text-muted-forground absolute top-4 right-4 flex h-8 w-8 cursor-pointer border-none shadow-none"
                                variant="secondary"
                            >
                                <EllipsisVerticalIcon />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="gradient">
                            <DropdownMenuItem>
                                <Share2Icon size={16} className="opacity-60" aria-hidden="true" />
                                Partilhar
                            </DropdownMenuItem>
                            {auth.user.id === hunt.owner.id ? (
                                <>
                                    <DropdownMenuItem>
                                        <Edit size={16} className="opacity-60" aria-hidden="true" />
                                        Editar
                                    </DropdownMenuItem>
                                    <DropdownMenuItem onSelect={(e) => e.preventDefault()}>
                                        <DeleteHunt hunt={hunt} />
                                    </DropdownMenuItem>
                                </>
                            ) : (
                                <>
                                    <DropdownMenuItem>
                                        <SaveIcon size={16} className="opacity-60" aria-hidden="true" />
                                        Guardar
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <StopCircle size={16} className="opacity-60" aria-hidden="true" />
                                        Ignorar
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <ShieldAlert size={16} className="opacity-60" aria-hidden="true" />
                                        Reportar
                                    </DropdownMenuItem>
                                </>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </CardHeader>
                <CardContent className="space-y-4">
                    <p>{hunt.content}</p>
                    {sanitizedImageUrl && <img src={sanitizedImageUrl} alt="Hunt image" className="max-h-50 w-full rounded-md object-cover" />}
                </CardContent>
                <CardFooter className="text-muted-foreground flex justify-between text-sm">
                    <HuntLikes hunt={hunt} />
                    <Button variant="ghost" size="sm" className="flex items-center gap-1" onClick={() => setOpenComments((prev) => !prev)}>
                        <MessageCircle size={20} /> {hunt.comments?.length || 0}
                    </Button>
                    <Button variant="ghost" size="sm" className="flex items-center gap-1">
                        <Repeat2 size={20} /> {hunt.shares}
                    </Button>
                    <Button variant="ghost" size="sm" className="flex items-center gap-1">
                        <BarChart size={20} /> {hunt.views}
                    </Button>
                </CardFooter>
                {isOpenComments && (
                    <div className="mt-0">
                        <HuntComments isOpen={isOpenComments} hunt={hunt} />
                    </div>
                )}
            </Card>
        </>
    );
}
