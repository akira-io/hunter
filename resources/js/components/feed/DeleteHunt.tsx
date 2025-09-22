import { HunterConfirmDialog } from '@/components/core/HuntDialog';
import { useToast } from '@/hooks/use-toast';
import hunts from '@/routes/hunts';
import { Hunt } from '@/types';
import { useForm } from '@inertiajs/react';
import { Ban } from 'lucide-react';

interface DeleteHuntProps {
    hunt: Hunt;
}

export default function DeleteHunt({ hunt }: DeleteHuntProps) {
    const { toast } = useToast();
    const { delete: destroy, processing } = useForm();

    function deleteHunt() {
        destroy(hunts.destroy.url(hunt.id), {
            preserveScroll: true,
            onSuccess: () => {
                toast({ description: 'Hunt eliminada com sucesso.' });
            },
            onError: () => {
                toast({
                    title: 'Erro',
                    variant: 'destructive',
                    description: 'Erro ao eliminar a hunt.',
                    icon: <Ban />,
                });
            },
        });
    }

    return <HunterConfirmDialog processing={processing} onConfirm={deleteHunt} title="Hunt" />;
}
