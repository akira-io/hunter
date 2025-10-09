import { CreateHunt } from '@/components/feed/CreateHunt';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { useHuntStore } from '@/stores/huntStore';
import { Plus } from 'lucide-react';

export function FloatingCreateHunt() {
    const { isFloatCreateHuntOpen, setIsFloatCreateHuntOpen } = useHuntStore();

    return (
        <div className="fixed right-4 bottom-4 z-50 md:right-10 md:bottom-10">
            <Dialog open={isFloatCreateHuntOpen} onOpenChange={setIsFloatCreateHuntOpen}>
                <DialogTrigger className="bg-primary hover:bg-primary-dark flex h-10 w-10 items-center justify-center rounded-full text-white shadow-lg transition-all duration-300">
                    <Plus size={24} />
                </DialogTrigger>
                <DialogContent className="overflow-y-auto sm:max-w-2xl">
                    <div className="p-4 sm:p-0">
                        <CreateHunt />
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    );
}
