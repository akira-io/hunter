import google from '@/routes/google';
import { LoaderCircle } from 'lucide-react';
import { RiGoogleFill } from 'react-icons/ri';
import { useState } from 'react';
import { Button } from '@/components/ui/button';

export function GoogleLoginButton() {

    const [loading, setLoading] = useState(false);

    const handleLogin = () => {
        setLoading(true);
        window.location.assign(google.login.url());
    };
    return <Button variant='outline'
                   type='button'
                   className='w-full'
                   onClick={handleLogin}
                   tabIndex={6}
                   disabled={loading}>
        {loading ? (
            <LoaderCircle className='h-4 w-4 animate-spin' />
        ) : (
            <RiGoogleFill className='me-1 text-[#333333] dark:text-white/60'
                          size={16}
                          aria-hidden='true' />
        )}
        Continuar com Google
    </Button>;
}