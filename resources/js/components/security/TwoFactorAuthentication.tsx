import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import { useToast } from '@/hooks/use-toast';
import twoFactor from '@/routes/two-factor';
import { router } from '@inertiajs/react';
import {
    AlertCircle,
    Copy,
    Download,
    RefreshCw,
    Shield,
    ShieldCheck,
    X,
} from 'lucide-react';
import { useEffect, useState } from 'react';

interface TwoFactorAuthenticationProps {
    twoFactorEnabled: boolean;
    qrCodeSvg?: string;
    recoveryCodes?: string[];
    confirmed?: boolean;
}

export function TwoFactorAuthentication({
    twoFactorEnabled,
    qrCodeSvg,
    recoveryCodes,
    confirmed = false,
}: TwoFactorAuthenticationProps) {
    const { toast } = useToast();
    const [enabling, setEnabling] = useState(false);
    const [disabling, setDisabling] = useState(false);
    const [confirmingCode, setConfirmingCode] = useState('');
    const [confirming, setConfirming] = useState(false);
    const [, setShowRecoveryCodes] = useState(false);
    const [showDisableDialog, setShowDisableDialog] = useState(false);
    const [regenerating, setRegenerating] = useState(false);

    useEffect(() => {
        if (confirmingCode.length === 6 && !confirmed && !confirming) {
            confirm2FA();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [confirmingCode]);

    const enable2FA = () => {
        setEnabling(true);
        router.post(
            twoFactor.enable.url(),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Force page reload to get the QR code
                    router.reload({
                        only: ['twoFactorEnabled', 'qrCodeSvg', 'confirmed'],
                        onSuccess: () => {
                            setEnabling(false);
                            toast({
                                title: '2FA Ativado',
                                description:
                                    'Escaneie o código QR para completar a configuração.',
                            });
                        },
                        onError: () => {
                            setEnabling(false);
                        },
                    });
                },
                onError: () => {
                    setEnabling(false);
                    toast({
                        title: 'Erro',
                        description:
                            'Não foi possível ativar o 2FA. Tente novamente.',
                        variant: 'destructive',
                        duration: 5000,
                    });
                },
            },
        );
    };

    const confirm2FA = () => {
        setConfirming(true);
        router.post(
            twoFactor.confirm.url(),
            { code: confirmingCode },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setConfirmingCode('');
                    setConfirming(false);
                    toast({
                        title: '2FA Confirmado',
                        description:
                            'A autenticação de dois fatores está agora ativa e protegendo sua conta!',
                    });
                },
                onError: () => {
                    setConfirmingCode('');
                    setConfirming(false);
                    toast({
                        title: 'Código Inválido',
                        description:
                            'O código inserido não é válido. Verifique e tente novamente.',
                        variant: 'destructive',
                    });
                },
            },
        );
    };

    const disable2FA = () => {
        setDisabling(true);
        router.delete(twoFactor.disable.url(), {
            preserveScroll: true,
            onSuccess: () => {
                setDisabling(false);
                setShowDisableDialog(false);
                toast({
                    title: '2FA Desativado',
                    description:
                        'A autenticação de dois fatores foi desativada.',
                });
            },
            onError: () => {
                setDisabling(false);
                toast({
                    title: 'Erro',
                    description:
                        'Não foi possível desativar o 2FA. Tente novamente.',
                    variant: 'destructive',
                });
            },
        });
    };

    const regenerateRecoveryCodes = () => {
        setRegenerating(true);
        router.post(
            twoFactor.regenerateRecoveryCodes.url(),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setRegenerating(false);
                    setShowRecoveryCodes(true);
                    toast({
                        title: 'Códigos Regenerados',
                        description:
                            'Novos códigos de recuperação foram gerados. Guarde-os em local seguro.',
                        duration: 5000,
                    });
                },
                onError: () => {
                    setRegenerating(false);
                    toast({
                        title: 'Erro',
                        description:
                            'Não foi possível regenerar os códigos. Tente novamente.',
                        variant: 'destructive',
                    });
                },
            },
        );
    };

    // const showRecoveryCodesHandler = () => {
    //     router.get(
    //         twoFactor.recoveryCodes.url(),
    //         {},
    //         {
    //             preserveScroll: true,
    //             onSuccess: () => {
    //                 setShowRecoveryCodes(true);
    //             },
    //         },
    //     );
    // };

    const copyRecoveryCodes = () => {
        if (recoveryCodes) {
            navigator.clipboard.writeText(recoveryCodes.join('\n'));
            toast({
                title: 'Códigos Copiados',
                description:
                    'Os códigos de recuperação foram copiados para a área de transferência.',
            });
        }
    };

    const downloadRecoveryCodes = () => {
        if (recoveryCodes) {
            const element = document.createElement('a');
            const file = new Blob([recoveryCodes.join('\n')], {
                type: 'text/plain',
            });
            element.href = URL.createObjectURL(file);
            element.download = 'devhunter-recovery-codes.txt';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
            toast({
                title: 'Códigos Transferidos',
                description:
                    'Os códigos de recuperação foram baixados com sucesso.',
            });
        }
    };

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <Shield className="size-5" />
                            Autenticação de Dois Fatores (2FA)
                        </CardTitle>
                        <CardDescription>
                            Adicione uma camada extra de segurança à sua conta
                        </CardDescription>
                    </div>
                    {twoFactorEnabled && confirmed && (
                        <Badge variant="default" className="gap-1">
                            <ShieldCheck className="size-3" />
                            Ativado
                        </Badge>
                    )}
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                {!twoFactorEnabled ? (
                    <div className="space-y-4">
                        <p className="text-sm text-muted-foreground">
                            A autenticação de dois fatores adiciona uma camada
                            extra de segurança à sua conta, exigindo um código
                            de um aplicativo autenticador além da sua senha.
                        </p>
                        <div className="flex justify-end">
                            <Button
                                onClick={enable2FA}
                                disabled={enabling}
                                variant="gradient"
                                className="w-full sm:w-auto"
                            >
                                <Shield className="size-4" />
                                {enabling ? 'Ativando...' : 'Ativar 2FA'}
                            </Button>
                        </div>
                    </div>
                ) : (
                    <div className="space-y-6">
                        {!confirmed && qrCodeSvg && (
                            <div className="space-y-4">
                                <Alert>
                                    <AlertCircle className="size-4" />
                                    <AlertDescription>
                                        Para finalizar a ativação do 2FA,
                                        escaneie o código QR abaixo com um
                                        aplicativo autenticador como Google
                                        Authenticator ou Authy, e insira o
                                        código de 6 dígitos gerado.
                                    </AlertDescription>
                                </Alert>

                                <div className="flex justify-center rounded-lg border bg-white p-4 dark:bg-zinc-900">
                                    <div
                                        dangerouslySetInnerHTML={{
                                            __html: qrCodeSvg,
                                        }}
                                    />
                                </div>

                                <div className="flex flex-col items-center justify-center space-y-2">
                                    <Label htmlFor="confirm_code ">
                                        Código de Confirmação
                                    </Label>
                                    <div className="flex justify-center">
                                        <InputOTP
                                            maxLength={6}
                                            value={confirmingCode}
                                            onChange={(value) =>
                                                setConfirmingCode(value)
                                            }
                                            disabled={confirming}
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
                                    {confirming && (
                                        <p className="text-center text-sm text-muted-foreground">
                                            Verificando código...
                                        </p>
                                    )}
                                    <InputError message="" />
                                </div>
                            </div>
                        )}

                        {confirmed && (
                            <div className="space-y-4">
                                <Alert
                                    variant="default"
                                    className="border-green-200 dark:border-green-900"
                                >
                                    <ShieldCheck className="size-4" />
                                    <AlertDescription>
                                        A autenticação de dois fatores está
                                        ativada e protegendo sua conta.
                                    </AlertDescription>
                                </Alert>

                                {recoveryCodes && recoveryCodes.length > 0 && (
                                    <div className="space-y-2">
                                        <div className="flex items-center justify-between">
                                            <Label>
                                                Códigos de Recuperação
                                            </Label>
                                            <div className="flex gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={copyRecoveryCodes}
                                                >
                                                    <Copy className="size-3" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={
                                                        downloadRecoveryCodes
                                                    }
                                                >
                                                    <Download className="size-3" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div className="rounded-lg border bg-zinc-50 p-4 font-mono text-sm dark:bg-zinc-900">
                                            <div className="grid grid-cols-2 gap-2">
                                                {recoveryCodes.map(
                                                    (code, index) => (
                                                        <div key={index}>
                                                            {code}
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            Guarde estes códigos em um local
                                            seguro. Você pode usá-los para
                                            acessar sua conta caso perca acesso
                                            ao seu dispositivo de autenticação.
                                        </p>
                                    </div>
                                )}

                                <div className="flex flex-col gap-2 sm:flex-row">
                                    <Button
                                        variant="outline"
                                        onClick={regenerateRecoveryCodes}
                                        disabled={regenerating}
                                    >
                                        <RefreshCw className="size-4" />
                                        {regenerating
                                            ? 'Regenerando...'
                                            : 'Regenerar Códigos'}
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        onClick={() =>
                                            setShowDisableDialog(true)
                                        }
                                        disabled={disabling}
                                    >
                                        <X className="size-4" />
                                        Desativar 2FA
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                <AlertDialog
                    open={showDisableDialog}
                    onOpenChange={setShowDisableDialog}
                >
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Desativar Autenticação de Dois Fatores
                            </AlertDialogTitle>
                            <AlertDialogDescription>
                                Tem certeza de que deseja desativar a
                                autenticação de dois fatores? Isso tornará sua
                                conta menos segura.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            <AlertDialogAction
                                onClick={disable2FA}
                                className="bg-red-600 hover:bg-red-700"
                            >
                                Desativar
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </CardContent>
        </Card>
    );
}
