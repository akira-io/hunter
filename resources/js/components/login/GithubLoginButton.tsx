import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-react';
import { RiGithubFill } from '@remixicon/react';
import { useState, useEffect } from 'react';
import { useToast } from '@/hooks/use-toast';
import github from '@/routes/github';

export function GithubLoginButton() {
    const [loading, setLoading] = useState(false);
    const { toast } = useToast();

    const handleGithubLogin = () => {
        try {
            setLoading(true);
            // Simulate connection timeout detection
            const timeoutId = setTimeout(() => {
                if (loading) {
                    setLoading(false);
                    toast({
                        title: 'Erro de conexão',
                        description: 'Não foi possível conectar com GitHub. Verifique sua conexão e tente novamente.',
                        variant: 'destructive',
                    });
                }
            }, 10000); // 10 second timeout

            // Clear timeout if user navigates away successfully
            window.addEventListener('beforeunload', () => clearTimeout(timeoutId));

            window.location.assign(github.login.url());
        } catch {
            setLoading(false);
            toast({
                title: 'Erro no GitHub',
                description: 'Ocorreu um problema ao tentar conectar com GitHub. Tente novamente.',
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
                            description: 'O login com GitHub foi cancelado ou falhou. Tente novamente se necessário.',
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
            onClick={handleGithubLogin}
            tabIndex={1}
            disabled={loading}
        >
            {loading ? (
                <LoaderCircle className='h-4 w-4 animate-spin' />
            ) : (
                <RiGithubFill className='me-1 text-[#333333] dark:text-white/60'
                              size={16}
                              aria-hidden='true' />
            )}
            {loading ? 'Conectando com GitHub...' : 'Continuar com GitHub'}
        </Button>
    );
}