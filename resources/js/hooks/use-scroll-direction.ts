import { useEffect, useState } from 'react';

type ScrollDirection = 'up' | 'down' | null;

interface UseScrollDirectionOptions {
    threshold?: number;
    onScrollUp?: () => void;
    onScrollDown?: () => void;
}

export const useScrollDirection = (options: UseScrollDirectionOptions = {}) => {
    const { threshold = 10, onScrollUp, onScrollDown } = options;
    const [scrollDirection, setScrollDirection] = useState<ScrollDirection>(null);
    const [lastScrollY, setLastScrollY] = useState(0);

    useEffect(() => {
        let ticking = false;

        const updateScrollDirection = () => {
            const scrollY = window.scrollY;

            if (Math.abs(scrollY - lastScrollY) < threshold) {
                ticking = false;
                return;
            }

            const direction = scrollY > lastScrollY ? 'down' : 'up';

            if (direction !== scrollDirection) {
                setScrollDirection(direction);

                if (direction === 'up' && onScrollUp) {
                    onScrollUp();
                } else if (direction === 'down' && onScrollDown) {
                    onScrollDown();
                }
            }

            setLastScrollY(scrollY > 0 ? scrollY : 0);
            ticking = false;
        };

        const onScroll = () => {
            if (!ticking) {
                window.requestAnimationFrame(updateScrollDirection);
                ticking = true;
            }
        };

        window.addEventListener('scroll', onScroll, { passive: true });

        return () => {
            window.removeEventListener('scroll', onScroll);
        };
    }, [scrollDirection, lastScrollY, threshold, onScrollUp, onScrollDown]);

    return scrollDirection;
};
