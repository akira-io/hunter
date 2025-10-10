import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { ArrowDownIcon } from 'lucide-react';
import * as React from 'react';
import { useEffect, useState } from 'react';

export function ScrollDown({
    className,
    ...props
}: React.ComponentProps<'div'>) {
    const [isScrolled, setIsScrolled] = useState(false);
    const [isScrollable, setIsScrollable] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            setIsScrolled(window.scrollY > 0);
        };

        const checkScrollable = () => {
            setIsScrollable(
                document.documentElement.scrollHeight > window.innerHeight,
            );
        };

        handleScroll();
        checkScrollable();

        window.addEventListener('scroll', handleScroll);
        window.addEventListener('resize', checkScrollable);

        return () => {
            window.removeEventListener('scroll', handleScroll);
            window.removeEventListener('resize', checkScrollable);
        };
    }, []);

    const handleClick = () => {
        window.scrollTo({
            top: window.innerHeight,
            behavior: 'smooth',
        });
    };

    return (
        <>
            {isScrollable && !isScrolled && (
                <div
                    className={cn(
                        'flex transform animate-bounce cursor-pointer flex-col items-center justify-center',
                        className,
                    )}
                    {...props}
                >
                    <Button
                        variant="ghost"
                        className="flex items-center justify-center"
                        onClick={handleClick}
                    >
                        <ArrowDownIcon size={24} />
                    </Button>
                </div>
            )}
        </>
    );
}
