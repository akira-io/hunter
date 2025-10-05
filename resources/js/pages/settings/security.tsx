import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import HeadingSmall from '@/components/heading-small';
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
    AlertDialogTitle
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import password from '@/routes/password';
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, router, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import {
    AlertCircle,
    Check,
    Globe,
    KeyRound,
    LogOut,
    Monitor,
    MoreVertical,
    ShieldAlert,
    Smartphone,
    Unlink,
    X
} from 'lucide-react';
import { type FormEventHandler, useRef, useState } from 'react';
import { RiGoogleFill } from 'react-icons/ri';
import { RiGithubFill } from '@remixicon/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Definições de segurança',
        href: '/settings/security',
    },
];

interface Session {
    id: number;
    ip_address: string;
    user_agent: string;
    login_at: string;
    location: {
        city?: string;
        country?: string;
    } | null;
    is_current: boolean;
}

interface ConnectedAccounts {
    github: boolean;
    google: boolean;
}

interface Props {
    activeSessions: Session[];
    connectedAccounts: ConnectedAccounts;
    hasPassword: boolean;
}

export default function Security({ activeSessions, connectedAccounts, hasPassword }: Props) {
    const [sessionToRevoke, setSessionToRevoke] = useState<number | null>(null);
    const [accountToDisconnect, setAccountToDisconnect] = useState<string | null>(null);
    const [showLogoutAllDialog, setShowLogoutAllDialog] = useState(false);

    // Password form
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const {
        data: passwordData,
        setData: setPasswordData,
        errors: passwordErrors,
        put,
        reset: resetPassword,
        processing: updatingPassword,
        recentlySuccessful,
    } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword: FormEventHandler = (e) => {
        e.preventDefault();

        put(password.update().url, {
            preserveScroll: true,
            onSuccess: () => resetPassword(),
            onError: (errors) => {
                if (errors.password) {
                    resetPassword('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    resetPassword('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    const revokeSession = (sessionId: number) => {
        router.delete(SecurityController.revokeSession(sessionId).url, {
            preserveScroll: true,
            onSuccess: () => setSessionToRevoke(null),
        });
    };

    const logoutOtherDevices = () => {
        router.post('/settings/security/logout-other-devices', {
            preserveScroll: true,
            // onSuccess: () => setShowLogoutAllDialog(false),
        });
    };

    const disconnectAccount = (provider: string) => {
        router.delete(SecurityController.disconnectAccount(provider).url, {
            preserveScroll: true,
            onSuccess: () => setAccountToDisconnect(null),
        });
    };

    const getDeviceIcon = (userAgent: string) => {
        if (userAgent.includes('Mobile') || userAgent.includes('Android') || userAgent.includes('iPhone')) {
            return <Smartphone className="size-4" />;
        }
        return <Monitor className="size-4" />;
    };

    const parseUserAgent = (userAgent: string) => {
        let browser = 'Desconhecido';
        let os = 'Desconhecido';

        // Browser detection
        if (userAgent.includes('Chrome')) browser = 'Chrome';
        else if (userAgent.includes('Firefox')) browser = 'Firefox';
        else if (userAgent.includes('Safari')) browser = 'Safari';
        else if (userAgent.includes('Edge')) browser = 'Edge';

        // OS detection
        if (userAgent.includes('Windows')) os = 'Windows';
        else if (userAgent.includes('Mac')) os = 'macOS';
        else if (userAgent.includes('Linux')) os = 'Linux';
        else if (userAgent.includes('Android')) os = 'Android';
        else if (userAgent.includes('iOS') || userAgent.includes('iPhone')) os = 'iOS';

        return { browser, os };
    };

    const otherSessionsCount = activeSessions.filter((s) => !s.is_current).length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Segurança" />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Segurança" description="Gerencie a segurança da sua conta, senha, sessões ativas e contas conectadas" />

                    {/* Password Change Section */}
                    <Card className="gradient">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <KeyRound className="size-5" />
                                Alterar Senha
                            </CardTitle>
                            <CardDescription>Mantenha sua conta segura com uma senha forte</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={updatePassword} className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">Senha Atual</Label>
                                    <Input
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        value={passwordData.current_password}
                                        onChange={(e) => setPasswordData('current_password', e.target.value)}
                                        type="password"
                                        autoComplete="current-password"
                                        placeholder="Senha atual"
                                    />
                                    <InputError message={passwordErrors.current_password} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password">Nova Senha</Label>
                                    <Input
                                        id="password"
                                        ref={passwordInput}
                                        value={passwordData.password}
                                        onChange={(e) => setPasswordData('password', e.target.value)}
                                        type="password"
                                        autoComplete="new-password"
                                        placeholder="Nova senha"
                                    />
                                    <InputError message={passwordErrors.password} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">Confirmar Senha</Label>
                                    <Input
                                        id="password_confirmation"
                                        value={passwordData.password_confirmation}
                                        onChange={(e) => setPasswordData('password_confirmation', e.target.value)}
                                        type="password"
                                        autoComplete="new-password"
                                        placeholder="Confirmar senha"
                                    />
                                    <InputError message={passwordErrors.password_confirmation} />
                                </div>
                                <div className="flex items-center justify-end gap-4">
                                    <Button disabled={updatingPassword} variant="gradient">
                                        Guardar Senha
                                    </Button>
                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600 dark:text-neutral-400">Guardado</p>
                                    </Transition>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Active Sessions Section */}
                    <Card className="gradient">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2 text-lg">
                                        <Monitor className="size-5" />
                                        Sessões Ativas
                                    </CardTitle>
                                    <CardDescription>Dispositivos atualmente conectados à sua conta</CardDescription>
                                </div>
                                {otherSessionsCount > 0 && (
                                    <Button variant="destructive" size="sm" onClick={() => setShowLogoutAllDialog(true)}>
                                        <LogOut className="mr-2 size-4" />
                                        Sair de Todos
                                    </Button>
                                )}
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {activeSessions.length === 0 ? (
                                <Alert>
                                    <AlertCircle className="size-4" />
                                    <AlertDescription>Nenhuma sessão ativa encontrada.</AlertDescription>
                                </Alert>
                            ) : (
                                activeSessions.map((session) => {
                                    const { browser, os } = parseUserAgent(session.user_agent);
                                    return (
                                        <div
                                            key={session.id}
                                            className="flex items-start justify-start gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-800"
                                        >
                                            <div className="rounded-full bg-zinc-100 p-3 dark:bg-zinc-800">{getDeviceIcon(session.user_agent)}</div>
                                            <div className="flex w-full gap-4">
                                                <div className="w-full space-y-1">
                                                    <div className="flex items-center justify-between gap-2">
                                                        <p className="font-medium">
                                                            {browser} no {os}
                                                        </p>
                                                        {session.is_current && (
                                                            <Badge variant="default" className="text-xs">
                                                                <Check className="mr-1 size-3" />
                                                                Atual
                                                            </Badge>
                                                        )}
                                                    </div>
                                                    <div className="flex flex-col gap-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                        <div className="flex items-center gap-2">
                                                            <Globe className="size-3" />
                                                            <span>{session.ip_address}</span>
                                                            {session.location?.city && (
                                                                <span>
                                                                    • {session.location.city}
                                                                    {session.location.country && `, ${session.location.country}`}
                                                                </span>
                                                            )}
                                                        </div>
                                                        <span className="text-xs">
                                                            Login{' '}
                                                            {formatDistanceToNow(new Date(session.login_at), {
                                                                addSuffix: true,
                                                                locale: ptBR,
                                                            })}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            {!session.is_current && (
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm">
                                                            <MoreVertical className="size-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem
                                                            className="text-red-600 dark:text-red-400"
                                                            onClick={() => setSessionToRevoke(session.id)}
                                                        >
                                                            <LogOut className="mr-2 size-4" />
                                                            Revogar Sessão
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            )}
                                        </div>
                                    );
                                })
                            )}
                        </CardContent>
                    </Card>

                    {/* Connected Accounts Section */}
                    <Card className="gradient">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Unlink className="size-5" />
                                Contas Conectadas
                            </CardTitle>
                            <CardDescription>Gerencie suas contas OAuth conectadas</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {!hasPassword && (
                                <Alert variant="destructive">
                                    <ShieldAlert className="size-4" />
                                    <AlertDescription>Você precisa definir uma senha antes de desconectar contas OAuth.</AlertDescription>
                                </Alert>
                            )}

                            {/* GitHub */}
                            <div className="flex flex-col gap-4 rounded-lg border border-zinc-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                                <div className="flex items-center gap-4">
                                    <div className="shrink-0 rounded-full bg-black p-3 dark:bg-white">
                                        <RiGithubFill className="size-4 text-white dark:text-black" />
                                    </div>
                                    <div>
                                        <p className="font-medium">
                                            GitHub{' '}
                                            <p className="text-sm text-zinc-600 dark:text-zinc-400">
                                                {connectedAccounts.github ? 'Conta conectada' : 'Não conectado'}
                                            </p>
                                        </p>
                                    </div>
                                </div>
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    {connectedAccounts.github ? (
                                        <>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                disabled={!hasPassword}
                                                onClick={() => setAccountToDisconnect('github')}
                                                className="w-full sm:w-auto"
                                            >
                                                <X className="mr-2 size-4" />
                                                Desconectar
                                            </Button>
                                        </>
                                    ) : (
                                        <Button variant="outline" size="sm" asChild className="w-full sm:w-auto">
                                            <a href="/auth/github">
                                                <RiGithubFill className="mr-2 size-4" />
                                                Conectar
                                            </a>
                                        </Button>
                                    )}
                                </div>
                            </div>

                            {/* Google */}
                            <div className="flex flex-col gap-4 rounded-lg border border-zinc-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                                <div className="flex items-center gap-4">
                                    <div className="shrink-0 rounded-full bg-white p-3 shadow-sm dark:bg-zinc-800">
                                        <svg className="size-4" viewBox="0 0 24 24">
                                            <path
                                                fill="#4285F4"
                                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                            />
                                            <path
                                                fill="#34A853"
                                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                            />
                                            <path
                                                fill="#FBBC05"
                                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                            />
                                            <path
                                                fill="#EA4335"
                                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p className="font-medium">Google</p>
                                        <p className="text-sm text-zinc-600 dark:text-zinc-400">
                                            {connectedAccounts.google ? 'Conta conectada' : 'Não conectado'}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    {connectedAccounts.google ? (
                                        <>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                disabled={!hasPassword}
                                                onClick={() => setAccountToDisconnect('google')}
                                                className="w-full sm:w-auto"
                                            >
                                                <X className="mr-2 size-4" />
                                                Desconectar
                                            </Button>
                                        </>
                                    ) : (
                                        <Button variant="outline" size="sm" asChild className="w-full sm:w-auto">
                                            <a href="/auth/google">
                                                <RiGoogleFill />
                                                Conectar
                                            </a>
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Revoke Session Dialog */}
                <AlertDialog open={sessionToRevoke !== null} onOpenChange={() => setSessionToRevoke(null)}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Revogar Sessão</AlertDialogTitle>
                            <AlertDialogDescription>
                                Tem certeza de que deseja revogar esta sessão? O dispositivo será desconectado imediatamente.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            <AlertDialogAction
                                onClick={() => sessionToRevoke && revokeSession(sessionToRevoke)}
                                className="bg-red-600 hover:bg-red-700"
                            >
                                Revogar
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>

                {/* Logout All Devices Dialog */}
                <AlertDialog open={showLogoutAllDialog} onOpenChange={setShowLogoutAllDialog}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Sair de Todos os Dispositivos</AlertDialogTitle>
                            <AlertDialogDescription>
                                Tem certeza de que deseja sair de todos os outros dispositivos? Você permanecerá conectado neste dispositivo.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            <AlertDialogAction onClick={logoutOtherDevices} className="bg-red-600 hover:bg-red-700">
                                Sair de Todos
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>

                {/* Disconnect Account Dialog */}
                <AlertDialog open={accountToDisconnect !== null} onOpenChange={() => setAccountToDisconnect(null)}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Desconectar Conta</AlertDialogTitle>
                            <AlertDialogDescription>
                                Tem certeza de que deseja desconectar sua conta do {accountToDisconnect === 'github' ? 'GitHub' : 'Google'}? Você
                                poderá reconectar a qualquer momento.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            <AlertDialogAction
                                onClick={() => accountToDisconnect && disconnectAccount(accountToDisconnect)}
                                className="bg-red-600 hover:bg-red-700"
                            >
                                Desconectar
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </SettingsLayout>
        </AppLayout>
    );
}
