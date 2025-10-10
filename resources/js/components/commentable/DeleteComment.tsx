import { HunterConfirmDialog } from '@/components/core/HuntDialog';
import { useToast } from '@/hooks/use-toast';
import comments from '@/routes/comments';
import { Comment } from '@/types';
import { useForm } from '@inertiajs/react';
import { Ban } from 'lucide-react';

interface DeleteComentProps {
    comment: Comment;
}

export default function DeleteComment({ comment }: DeleteComentProps) {
    const { toast } = useToast();
    const { delete: destroy, processing } = useForm();

    function deleteComment() {
        destroy(comments.destroy(comment).url, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast({ description: 'Comentário  eliminada com sucesso.' });
            },
            onError: () => {
                toast({
                    title: 'Erro',
                    variant: 'destructive',
                    description: 'Erro ao eliminar comentário.',
                    icon: <Ban />,
                });
            },
        });
    }

    return (
        <HunterConfirmDialog
            processing={processing}
            onConfirm={deleteComment}
            title="Comentário"
        />
    );
}
