import google from '@/routes/google';
import { LoaderCircle } from 'lucide-react';
import { RiGoogleFill } from 'react-icons/ri';
import { useState, useEffect } from 'react';
import { useToast } from '@/hooks/use-toast';
import { Button } from '@/components/ui/button';

export function GoogleLoginButton() {
    const [loading, setLoading] = useState(false);
    const { toast } = useToast();

    const handleLogin = () => {
        try {
            setLoading(true);
            // Simulate connection timeout detection
            const timeoutId = setTimeout(() => {
                if (loading) {
                    setLoading(false);
                    toast({
                        title: 'Erro de conexão',
                        description: 'Não foi possível conectar com Google. Verifique sua conexão e tente novamente.',
                        variant: 'destructive',
                    });
                }
            }, 10000); // 10 second timeout

            // Clear timeout if user navigates away successfully
            window.addEventListener('beforeunload', () => clearTimeout(timeoutId));

            window.location.assign(google.login.url());
        } catch (err) {
            setLoading(false);
            toast({
                title: 'Erro no Google',
                description: 'Ocorreu um problema ao tentar conectar com Google. Tente novamente.',
                variant: 'destructive',
            });
        }
    };

    // Handle page visibility to detect failed redirects
    useEffect(() => {
        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible' && loading) {
                // User came back to the page, likely auth failed
                setTimeout(() => {
                    if (loading) {
                        setLoading(false);
                        toast({
                            title: 'Login cancelado',
                            description: 'O login com Google foi cancelado ou falhou. Tente novamente se necessário.',
                            variant: 'default',
                        });
                    }
                }, 1000); // Small delay to ensure state is correct
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);
        return () => document.removeEventListener('visibilitychange', handleVisibilityChange);
    }, [loading, toast]);

    return (
        <Button
            variant='outline'
            type='button'
            className='w-full'
            onClick={handleLogin}
            tabIndex={6}
            disabled={loading}
        >
            {loading ? (
                <LoaderCircle className='h-4 w-4 animate-spin' />
            ) : (
                <RiGoogleFill className='me-1 text-[#333333] dark:text-white/60'
                              size={16}
                              aria-hidden='true' />
            )}
            {loading ? 'Conectando com Google...' : 'Continuar com Google'}
        </Button>
    );
}