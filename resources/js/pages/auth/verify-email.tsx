// Components
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';
import { logout } from '@/routes';
import verification from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(verification.send().url);
    };

    return (
        <AuthLayout
            title="Verificar E-mail"
            description="Verifique seu endereço de e-mail clicando no link que acabamos de enviar "
        >
            <Head title="Email verification" />
            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    Um novo link de verificação foi enviado para o seu endereço
                    de e-mail.
                </div>
            )}
            <form onSubmit={submit} className="space-y-6 text-center">
                <Button disabled={processing} variant="secondary">
                    {processing && (
                        <LoaderCircle className="h-4 w-4 animate-spin" />
                    )}
                    Reenviar o e-mail de verificação
                </Button>
                <TextLink
                    href={logout()}
                    method="post"
                    className="mx-auto block text-sm"
                >
                    Sair
                </TextLink>
            </form>
        </AuthLayout>
    );
}
