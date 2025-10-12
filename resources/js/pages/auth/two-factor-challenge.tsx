import { Head, router, useForm } from '@inertiajs/react';
import { LoaderCircle, ShieldCheck, X } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import twoFactor from '@/routes/two-factor';

type TwoFactorChallengeForm = {
    code: string;
    recovery_code: string;
};

export default function TwoFactorChallenge() {
    const [recovery, setRecovery] = useState(false);
    const [otpValue, setOtpValue] = useState('');
    const [canceling, setCanceling] = useState(false);

    const { data, setData, post, processing, errors } =
        useForm<TwoFactorChallengeForm>({
            code: '',
            recovery_code: '',
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(twoFactor.login.store.url(), {
            onError: () => {
                setOtpValue('');
                setData('code', '');
            },
        });
    };

    useEffect(() => {
        if (!recovery && otpValue.length === 6) {
            setData('code', otpValue);
            setTimeout(() => {
                post(twoFactor.login.store.url(), {
                    onError: () => {
                        setOtpValue('');
                        setData('code', '');
                    },
                });
            }, 100);
        }
    }, [otpValue, recovery]);

    const handleCancel = () => {
        setCanceling(true);
        router.post(twoFactor.cancel.url(), {}, {
            onFinish: () => {
                setCanceling(false);
            },
            onError: (errors) => {
                console.error('Erro ao cancelar 2FA:', errors);
            },
        });
    };

    return (
        <AuthLayout
            title="Autenticação de Dois Fatores"
            description={
                recovery
                    ? 'Introduza um dos seus códigos de recuperação'
                    : 'Introduza o código de autenticação da sua aplicação autenticadora'
            }
        >
            <Head title="Autenticação de Dois Fatores" />

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    {!recovery ? (
                        <div className="grid gap-2">
                            <Label htmlFor="code" className="text-center">
                                Código de Autenticação
                            </Label>
                            <div className="flex justify-center">
                                <InputOTP
                                    maxLength={6}
                                    value={otpValue}
                                    onChange={(value) => {
                                        setOtpValue(value);
                                        setData('code', value);
                                    }}
                                    disabled={processing}
                                    autoFocus
                                >
                                    <InputOTPGroup>
                                        <InputOTPSlot index={0} />
                                        <InputOTPSlot index={1} />
                                        <InputOTPSlot index={2} />
                                        <InputOTPSlot index={3} />
                                        <InputOTPSlot index={4} />
                                        <InputOTPSlot index={5} />
                                    </InputOTPGroup>
                                </InputOTP>
                            </div>
                            <InputError
                                message={errors.code}
                                className="text-center"
                            />
                        </div>
                    ) : (
                        <div className="grid gap-2">
                            <Label htmlFor="recovery_code">
                                Código de Recuperação
                            </Label>
                            <Input
                                id="recovery_code"
                                type="text"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="off"
                                value={data.recovery_code}
                                onChange={(e) =>
                                    setData('recovery_code', e.target.value)
                                }
                                placeholder="abcd-efgh-ijkl"
                                disabled={processing}
                            />
                            <InputError message={errors.recovery_code} />
                        </div>
                    )}

                    {recovery && (
                        <Button
                            type="submit"
                            className="mt-2 w-full"
                            tabIndex={2}
                            disabled={processing}
                            variant="gradient"
                        >
                            {processing ? (
                                <LoaderCircle className="h-4 w-4 animate-spin" />
                            ) : (
                                <ShieldCheck className="h-4 w-4" />
                            )}
                            Verificar
                        </Button>
                    )}
                </div>

                <div className="flex flex-col gap-4">
                    <div className="text-center">
                        <button
                            type="button"
                            className="text-sm text-muted-foreground underline-offset-4 hover:underline"
                            onClick={() => {
                                setRecovery(!recovery);
                                setData('code', '');
                                setData('recovery_code', '');
                                setOtpValue('');
                            }}
                            tabIndex={3}
                        >
                            {recovery
                                ? 'Usar código de autenticação'
                                : 'Usar código de recuperação'}
                        </button>
                    </div>

                    <Button
                        type="button"
                        variant="destructive"
                        className="w-full"
                        onClick={handleCancel}
                        disabled={processing || canceling}
                        tabIndex={4}
                    >
                        {canceling ? (
                            <LoaderCircle className="h-4 w-4 animate-spin" />
                        ) : (
                            <X className="h-4 w-4" />
                        )}
                        Cancelar e sair
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
