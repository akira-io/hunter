import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-react';
import { RiGithubFill } from '@remixicon/react';
import { useState } from 'react';
import github from '@/routes/github';

export function GithubLoginButton() {

    const [loading, setLoading] = useState(false);

    const handleGithubLogin = () => {
        setLoading(true);
        window.location.assign(github.login.url());
    };
    return <Button variant='outline'
                   type='button'
                   className='w-full'
                   onClick={handleGithubLogin}
                   tabIndex={1}
                   disabled={loading}>
        {loading ? (
            <LoaderCircle className='h-4 w-4 animate-spin' />
        ) : (
            <RiGithubFill className='me-1 text-[#333333] dark:text-white/60'
                          size={16}
                          aria-hidden='true' />
        )}
        Continuar com GitHub
    </Button>;
}