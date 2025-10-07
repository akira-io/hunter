import { HuntComments } from '@/components/commentable/HuntComments';
import DeleteHunt from '@/components/feed/DeleteHunt';
import { HuntModal } from '@/components/feed/HuntModal';
import { HuntLikes } from '@/components/likeable/HuntLikes';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { UserAvatar } from '@/components/UserAvatar';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import hunts from '@/routes/hunts';
import publicRoutes from '@/routes/public';
import { Hunt, SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { BarChart, Edit, EllipsisVerticalIcon, Eye, MessageCircle, Repeat2, SaveIcon, Share2Icon, ShieldAlert, StopCircle } from 'lucide-react';
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
    const [isModalOpen, setIsModalOpen] = useState(false);

    const sanitizedImageUrl = useSanitizeImageUrl(hunt.image_url);

    function gotoProfile() {
        router.get(publicRoutes.profile.show.url(hunt.owner.id));
    }

    function gotoHuntDetail() {
        // Always open in modal
        setIsModalOpen(true);
    }

    function gotoMetrics() {
        // Navigate to full page with metrics (owner only)
        router.get(hunts.show.url(hunt));
    }

    return (
        <>
            {/* Hunt Modal - opens for all hunts */}
            <HuntModal hunt={hunt} open={isModalOpen} onOpenChange={setIsModalOpen} />

            <Card className="relative mx-auto mb-4 w-full max-w-2xl overflow-hidden">
                <CardHeader className="flex flex-row items-start gap-4">
                    <UserAvatar
                        avatarUrl={hunt.owner.avatar_url}
                        userName={hunt.owner.name}
                        className="shrink-0 cursor-pointer"
                        onClick={gotoProfile}
                    />
                    <div className="flex min-w-0 flex-1 flex-col">
                        <CardTitle className="cursor-pointer truncate text-base font-semibold" onClick={gotoProfile}>
                            {hunt.owner.name}
                        </CardTitle>
                        <div
                            className="text-muted-foreground cursor-pointer truncate text-sm"
                            onClick={() => router.get(publicRoutes.profile.show.url(hunt.owner.id))}
                        >
                            @{hunt.owner.user_name || hunt.owner.name} · {hunt.created_at}
                        </div>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button className="text-muted-forground flex h-8 w-8 shrink-0 cursor-pointer border-none shadow-none" variant="secondary">
                                <EllipsisVerticalIcon />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="gradient">
                            <DropdownMenuItem onClick={gotoHuntDetail}>
                                <Eye size={16} className="opacity-60" aria-hidden="true" />
                                Ver Hunt
                            </DropdownMenuItem>
                            {hunt.is_owner && (
                                <DropdownMenuItem onClick={gotoMetrics}>
                                    <BarChart size={16} className="opacity-60" aria-hidden="true" />
                                    Ver Métricas
                                </DropdownMenuItem>
                            )}
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
                <CardContent className="space-y-4 overflow-hidden break-words">
                    <div className="cursor-pointer overflow-hidden" onClick={gotoHuntDetail}>
                        <MarkdownRenderer content={hunt.content} />
                    </div>
                    {sanitizedImageUrl && (
                        <div className="relative w-full overflow-hidden rounded-lg" style={{ height: '200px' }}>
                            <img
                                src={sanitizedImageUrl}
                                alt="Hunt image"
                                className="h-full w-full cursor-pointer object-cover transition-transform hover:scale-105"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setIsModalOpen(true);
                                }}
                            />
                        </div>
                    )}
                </CardContent>
                <CardFooter className="text-muted-foreground flex flex-wrap justify-between gap-2 text-sm">
                    <HuntLikes hunt={hunt} />
                    <Button variant="ghost" size="sm" className="flex items-center gap-1" onClick={() => setOpenComments((prev) => !prev)}>
                        <MessageCircle size={20} /> {hunt.comments?.length || 0}
                    </Button>
                    {hunt.is_owner && hunt.shares !== null && (
                        <Button variant="ghost" size="sm" className="flex items-center gap-1">
                            <Repeat2 size={20} /> {hunt.shares}
                        </Button>
                    )}
                    {hunt.is_owner && hunt.views !== null && (
                        <Button variant="ghost" size="sm" className="flex items-center gap-1">
                            <BarChart size={20} /> {hunt.views}
                        </Button>
                    )}
                </CardFooter>
                {isOpenComments && (
                    <div className="mt-0 overflow-hidden">
                        <HuntComments isOpen={isOpenComments} hunt={hunt} />
                    </div>
                )}
            </Card>
        </>
    );
}
