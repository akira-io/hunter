import DeleteComment from '@/components/commentable/DeleteComment';
import InputError from '@/components/input-error';
import { LikeButton } from '@/components/likeable/LikeButton';
import { OnboardingAvatar } from '@/components/Onboarding';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/hooks/use-toast';
import comments from '@/routes/comments';
import hunts from '@/routes/hunts';
import { Hunt, SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { EllipsisVerticalIcon, SendHorizonal } from 'lucide-react';
import { FormEvent } from 'react';

interface TweetCommentsProps {
    isOpen: boolean;
    hunt: Hunt;
}

export function HuntComments({ isOpen, hunt }: TweetCommentsProps) {
    const { auth } = usePage<SharedData>().props;
    const { toast } = useToast();
    const { data, post, setData, errors, processing } = useForm({
        content: '',
    });

    const handleAddComment = (e: FormEvent) => {
        e.preventDefault();
        post(hunts.comment.url(hunt), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setData('content', '');
                toast({
                    description: 'Comentário adicionado com sucesso.',
                });
            },
        });
    };

    const handleLike = (commentId: number) => {
        post(comments.toggleLike.url(commentId), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="mx-auto max-h-100 w-full max-w-2xl space-y-2 overflow-x-auto px-6">
            {isOpen && (
                <>
                    {hunt.can_comment && (
                        <>
                            <form className="relative flex gap-2" onSubmit={handleAddComment}>
                                <Textarea
                                    name="content"
                                    placeholder="Deixe o seu comentário aqui..."
                                    value={data.content}
                                    onChange={(e) => setData('content', e.target.value)}
                                    className="focus:ring-primary focus:border-primary transition-all duration-300 focus:ring-2"
                                />
                                <Button
                                    disabled={processing}
                                    variant="ghost"
                                    size="sm"
                                    type="submit"
                                    className="hover:bg-transparente group absolute right-0 bottom-0 flex items-center gap-1"
                                >
                                    <SendHorizonal className="group-hover:text-purple-500" />
                                </Button>
                            </form>
                            {errors.content ? (
                                <InputError message={errors.content} />
                            ) : (
                                <span className="float-right text-right text-xs text-gray-500">{data.content.length}/200</span>
                            )}
                        </>
                    )}
                    {!hunt.can_comment && (
                        <div className="rounded-lg bg-gray-100 p-4 text-center text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            Não tem permissão para comentar nesta publicação.
                        </div>
                    )}
                    <div className="mt-8 space-y-2.5">
                        {hunt.comments.map((comment) => (
                            <div
                                key={comment.id}
                                className="bg-muted animate-[fadeIn_0.5s_forwards] rounded-lg p-0 opacity-0 shadow-md transition-all duration-500 ease-out"
                                style={{
                                    animationName: 'fadeIn',
                                    animationDuration: '0.5s',
                                    animationTimingFunction: 'ease-out',
                                    animationFillMode: 'forwards',
                                }}
                            >
                                <div className="flex flex-col items-start justify-start gap-2 p-2">
                                    <div className="flex w-full items-start justify-between gap-1">
                                        <div className="flex w-full flex-1 flex-grow items-start gap-1">
                                            <OnboardingAvatar avatarUrl={comment.commenter.avatar_url} size={4} />
                                            <small className="fleex text-xs">{comment.commenter.name}</small>
                                            <small className="text-xs text-gray-400">{comment.created_at}</small>
                                        </div>
                                        {auth.user.id === comment.commenter.id && (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button
                                                        className="text-muted-forground bg-tr absolute top-0 right-0 flex cursor-pointer"
                                                        variant="secondary"
                                                    >
                                                        <EllipsisVerticalIcon />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent className="gradient">
                                                    <DropdownMenuItem onSelect={(e) => e.preventDefault()}>
                                                        <DeleteComment comment={comment} />
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        )}
                                    </div>
                                    <p className="text-sm leading-relaxed">{comment.content}</p>
                                </div>
                                <div className="text-muted-foreground mb-4 flex px-4 py-2 text-sm">
                                    <LikeButton
                                        count={comment.likes_count}
                                        hasLiked={comment.has_liked}
                                        onLike={() => handleLike(comment.id)}
                                        iconSize={16}
                                    />
                                </div>
                            </div>
                        ))}
                    </div>
                    <style>{`
                @keyframes fadeIn {
                    0% {
                        opacity: 0;
                        transform: translateY(20px) scale(0.95);
                    }
                    100% {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
            `}</style>
                </>
            )}
        </div>
    );
}
