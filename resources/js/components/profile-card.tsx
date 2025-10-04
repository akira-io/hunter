import { Button } from '@/components/ui/button';
import { Card, CardContent, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { ReactNode } from 'react';

interface ProfileCard {
    title: string;
    children: ReactNode;
    className?: string;
    icon?: ReactNode;
    onClick?: () => void;
}

export function ProfileCard({ title, className, icon, onClick, children }: ProfileCard) {
    return (
        <Card className={cn('gradient mt-4 max-h-100 w-full max-w-4xl overflow-y-auto', className)}>
            <CardTitle className="sticky -top-6 z-50 flex items-center justify-between px-6 py-2 text-sm shadow-sm backdrop-blur">
                <h3 className="text-foreground text-lg font-semibold">{title}</h3>
                <Button variant="ghost" onClick={onClick}>
                    {icon}
                </Button>
            </CardTitle>
            <CardContent className="mt-4 flex flex-col items-center space-y-6 px-4 text-center text-gray-500">{children}</CardContent>
        </Card>
    );
}
