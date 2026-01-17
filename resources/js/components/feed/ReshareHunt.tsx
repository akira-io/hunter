import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import hunts from '@/routes/hunts';
import { Hunt } from '@/types';
import { router } from '@inertiajs/react';
import { Repeat2 } from 'lucide-react';
import { useState } from 'react';

interface ReshareHuntProps {
    hunt: Hunt;
}

export function ReshareHunt({ hunt }: ReshareHuntProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [comment, setComment] = useState('');

    const handleReshare = () => {
        router.post(
            hunts.reshare.store.url(hunt),
            { comment },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsOpen(false);
                },
            },
        );
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <DialogTrigger asChild>
                <Button
                    variant="ghost"
                    size="sm"
                    className="flex items-center gap-1 px-2 text-orange-500 sm:px-3"
                >
                    <Repeat2 className="h-4 w-4 sm:h-5 sm:w-5" />
                    <span className="hidden sm:inline">
                        {hunt.shares}
                    </span>
                    <span className="sm:hidden">{hunt.shares}</span>
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Reshare Hunt</DialogTitle>
                    <DialogDescription>
                        Add a comment to your reshare (optional).
                    </DialogDescription>
                </DialogHeader>
                <Textarea
                    value={comment}
                    onChange={(e) => setComment(e.target.value)}
                    placeholder="Add a comment..."
                />
                <DialogFooter>
                    <Button
                        variant="ghost"
                        onClick={() => setIsOpen(false)}
                    >
                        Cancel
                    </Button>
                    <Button onClick={handleReshare}>Reshare</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
