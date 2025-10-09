import { CreateHunt } from '@/components/feed/CreateHunt';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { useHuntStore } from '@/stores/huntStore';
import { ArrowLeft, Plus } from 'lucide-react';

export function FloatingCreateHunt() {
    const { isFloatCreateHuntOpen, setIsFloatCreateHuntOpen } = useHuntStore();

    return (
        <div className="fixed right-4 bottom-4 z-50 md:right-10 md:bottom-10">
            <Dialog open={isFloatCreateHuntOpen} onOpenChange={setIsFloatCreateHuntOpen}>
                <DialogTrigger className="bg-primary hover:bg-primary-dark flex h-10 w-10 items-center justify-center rounded-full text-white shadow-lg transition-all duration-300">
                    <Plus size={24} />
                </DialogTrigger>
                <DialogContent className="overflow-y-auto sm:max-w-2xl">
                    {/* Mobile header - similar to app layout */}
                    <div className="bg-background/95 supports-[backdrop-filter]:bg-background/60 sticky top-0 z-10 flex items-center gap-3 border-b p-4 backdrop-blur sm:hidden">
                        <button
                            onClick={() => setIsFloatCreateHuntOpen(false)}
                            className="hover:bg-muted flex h-8 w-8 items-center justify-center rounded-full"
                        >
                            <ArrowLeft className="h-5 w-5" />
                        </button>
                        <h2 className="text-lg font-semibold">Criar Hunt</h2>
                    </div>

                    {/* Content */}
                    <div className="p-4 pt-0 sm:p-0">
                        <CreateHunt />
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    );
}
