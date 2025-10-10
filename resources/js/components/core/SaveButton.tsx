import { Button } from '@/components/ui/button';
import { CheckCircle } from 'lucide-react';
import React from 'react';

interface SaveButtonProps {
    disabled: boolean;
    title?: string;
    icon?: React.ReactNode;
}

export function SaveButton({
    disabled,
    title = 'Guardar Alterações',
    icon = <CheckCircle className="size-4" />,
}: SaveButtonProps) {
    return (
        <Button
            type="submit"
            disabled={disabled}
            className="w-full cursor-pointer gap-2 md:w-auto"
            variant="gradient"
        >
            {disabled ? (
                <>
                    <div className="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                    A processar...
                </>
            ) : (
                <>
                    {icon}
                    {title}
                </>
            )}
        </Button>
    );
}
