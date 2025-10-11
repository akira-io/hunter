'use client';

import { useToast } from '@/hooks/use-toast';
import { Toast, ToastClose, ToastDescription, ToastProvider, ToastTitle, ToastViewport } from '@/components/ui/toast';
import { CircleCheckBigIcon } from 'lucide-react';

export function Toaster() {
    const { toasts } = useToast();

    return (
        <ToastProvider duration={2000}>
            {toasts.map(function({ id, title = 'Sucesso', description, icon, action, ...props }) {
                return (
                    <Toast key={id} {...props} className='mt-2' duration={2000}>
                        <div className='flex w-full justify-between gap-2 mt-4'>
                            <div className='flex flex-col gap-3'>
                                <div className='space-y-1'>
                                    <div className='flex items-start gap-2'>
                                        <div className='text-foreground'>{icon ? icon :
                                            <CircleCheckBigIcon className='text-green-400' />}</div>
                                        <div>
                                            {title && <ToastTitle>{title}</ToastTitle>}
                                            {description && (
                                                <ToastDescription className='mt-2'>{description}</ToastDescription>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                <div>{action}</div>
                            </div>
                            <div>
                                <ToastClose />
                            </div>
                        </div>
                    </Toast>
                );
            })}
            <ToastViewport />
        </ToastProvider>
    );
}
