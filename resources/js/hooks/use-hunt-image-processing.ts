import { Hunt } from '@/types';
import { useEchoPublic } from '@laravel/echo-react';
import { Dispatch, SetStateAction, useEffect } from 'react';

interface HuntImageProcessedEvent {
    hunt: Hunt;
}

export function useHuntImageProcessing(setHunts: Dispatch<SetStateAction<Hunt[]>>) {
    const echo = useEchoPublic<HuntImageProcessedEvent>('hunts', undefined, undefined, []);

    useEffect(() => {
        const channel = echo.channel();
        if (!channel) {
            console.log('Channel not available');
            return;
        }

        console.log('Listening for hunt image processed events on hunts channel');

        channel.listen('.hunt.image.processed', (event: HuntImageProcessedEvent) => {
            console.log('Hunt image processed event received:', {
                huntId: event.hunt.id,
                imageUrl: event.hunt.image_url,
                status: event.hunt.image_processing_status,
            });

            // Update the local hunts state
            setHunts((currentHunts) => {
                return currentHunts.map((hunt) => {
                    if (hunt.id === event.hunt.id) {
                        console.log('Updating hunt in local state:', {
                            oldStatus: hunt.image_processing_status,
                            newStatus: event.hunt.image_processing_status,
                            oldImageUrl: hunt.image_url,
                            newImageUrl: event.hunt.image_url,
                        });
                        return {
                            ...hunt,
                            image_url: event.hunt.image_url,
                            image_processing_status: event.hunt.image_processing_status,
                        };
                    }
                    return hunt;
                });
            });
        });

        return () => {
            // Laravel Echo React handles cleanup automatically
        };
    }, [echo, setHunts]);
}
