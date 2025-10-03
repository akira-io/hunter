import { HuntComments } from '@/components/commentable/HuntComments';
import DeleteHunt from '@/components/feed/DeleteHunt';
import { HuntMetrics } from '@/components/hunt/HuntMetrics';
import { HuntLikes } from '@/components/likeable/HuntLikes';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { UserAvatar } from '@/components/UserAvatar';
import { useSanitizeImageUrl } from '@/hooks/use-sanitize-image-url';
import AppLayout from '@/layouts/app-layout';
import hunts from '@/routes/hunts';
import publicRoutes from '@/routes/public';
import { type BreadcrumbItem, Hunt, SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { ArrowLeft, BarChart, Edit, EllipsisVerticalIcon, MessageCircle, Repeat2, SaveIcon, Share2Icon, ShieldAlert, StopCircle } from 'lucide-react';
import { useState } from 'react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '../../components/ui/dropdown-menu';

interface HuntShowProps {
    hunt: Hunt;
}

export default function HuntShow({ hunt }: HuntShowProps) {
    const { auth } = usePage<SharedData>().props;
    const [isOpenComments, setOpenComments] = useState(true);

    const sanitizedImageUrl = useSanitizeImageUrl(hunt.image_url);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Hunt Line',
            href: hunts.index().url,
        },
        {
            title: `Hunt #${hunt.id}`,
            href: hunts.show(hunt.id).url,
        },
    ];

    function gotoProfile() {
        router.get(publicRoutes.profile.show.url(hunt.owner.id));
    }

    function goBack() {
        router.get(hunts.index().url);
    }

    function handleShare() {
        hunt.shares++;
        // TODO: Implement share functionality
        if (navigator.share) {
            navigator.share({
                title: `Hunt by ${hunt.owner.name}`,
                text: hunt.content.substring(0, 100),
                url: window.location.href,
            });
        } else {
            navigator.clipboard.writeText(window.location.href);
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Hunt by ${hunt.owner.name}`} />
            <div className="mx-auto flex h-full max-w-4xl flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Back Button */}
                <Button variant="ghost" size="sm" className="w-fit" onClick={goBack}>
                    <ArrowLeft size={16} />
                    Voltar
                </Button>

                {/* Hunt Card */}
                <Card className="relative w-full">
                    <CardHeader className="flex flex-row items-start gap-4">
                        <UserAvatar avatarUrl={hunt.owner.avatar_url} userName={hunt.owner.name} className="cursor-pointer" onClick={gotoProfile} />
                        <div className="flex flex-col">
                            <CardTitle className="cursor-pointer text-base font-semibold" onClick={gotoProfile}>
                                {hunt.owner.name}
                            </CardTitle>
                            <div className="text-muted-foreground cursor-pointer text-sm" onClick={() => router.get(publicRoutes.profile.show.url(hunt.owner.id))}>
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
                                <DropdownMenuItem onClick={handleShare}>
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
                        <MarkdownRenderer content={hunt.content} />
                        {sanitizedImageUrl && <img src={sanitizedImageUrl} alt="Hunt image" className="max-h-96 w-full rounded-md object-cover" />}
                    </CardContent>
                    <CardFooter className="text-muted-foreground flex justify-between text-sm">
                        <HuntLikes hunt={hunt} />
                        <Button variant="ghost" size="sm" className="flex items-center gap-1" onClick={() => setOpenComments((prev) => !prev)}>
                            <MessageCircle size={20} /> {hunt.comments?.length || 0}
                        </Button>
                        <Button variant="ghost" size="sm" className="flex items-center gap-1" onClick={handleShare}>
                            <Repeat2 size={20} /> {hunt.shares}
                        </Button>
                        <Button variant="ghost" size="sm" className="flex items-center gap-1">
                            <BarChart size={20} /> {hunt.views}
                        </Button>
                    </CardFooter>
                </Card>

                {/* Hunt Metrics */}
                <HuntMetrics hunt={hunt} />

                {/* Comments Section */}
                {isOpenComments && (
                    <Card className="w-full">
                        <CardHeader>
                            <CardTitle className="text-lg">Comentários</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <HuntComments isOpen={isOpenComments} hunt={hunt} />
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
