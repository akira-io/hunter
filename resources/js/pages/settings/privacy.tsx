import PrivacyController from '@/actions/App/Http/Controllers/Settings/PrivacyController';
import { SaveButton } from '@/components/core/SaveButton';
import HeadingSmall from '@/components/heading-small';
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
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import {
    CheckCircle,
    Eye,
    Globe,
    Lock,
    MessageSquare,
    Search,
    Shield,
    User,
    UserCheck,
    Users,
    UserX,
    XCircle
} from 'lucide-react';
import { type FormEventHandler, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Definições de privacidade',
        href: '/settings/privacy',
    },
];

interface PrivacySettings {
    profile_visibility: 'public' | 'followers' | 'private';
    who_can_message: 'everyone' | 'followers' | 'none';
    who_can_comment: 'everyone' | 'followers' | 'disabled';
    searchable: boolean;
    show_activity_status: boolean;
}

interface BlockedUser {
    id: number;
    name: string;
    user_name: string;
    avatar_url: string | null;
    blocked_at: string;
}

interface Props {
    privacySettings: PrivacySettings;
    blockedUsers: BlockedUser[];
}

export default function Privacy({ privacySettings, blockedUsers }: Props) {
    const { toast } = useToast();
    const [userToUnblock, setUserToUnblock] = useState<number | null>(null);

    const { data, setData, post, errors, processing } =
        useForm<PrivacySettings>(privacySettings);

    const updatePrivacySettings: FormEventHandler = (e) => {
        e.preventDefault();
        post(PrivacyController.update().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    icon: <CheckCircle className="text-green-400" />,
                    title: 'Definições atualizadas',
                    description:
                        'As suas definições de privacidade foram atualizadas com sucesso.',
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    icon: <XCircle className="text-red-400" />,
                    title: 'Erro',
                    description:
                        'Erro ao atualizar as definições de privacidade. Tente novamente.',
                });
            },
        });
    };

    const unblockUser = (userId: number) => {
        router.delete(PrivacyController.unblockUser({ userId }).url, {
            preserveScroll: true,
            onSuccess: () => {
                setUserToUnblock(null);
                toast({
                    icon: <CheckCircle className="text-green-400" />,
                    title: 'Hunter desbloqueado',
                    description: 'O Hunter foi desbloqueado com sucesso.',
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    icon: <XCircle className="text-red-400" />,
                    title: 'Erro',
                    description: 'Erro ao desbloquear Hunter. Tente novamente.',
                });
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Privacidade" />
            <SettingsLayout>
                <div className="space-y-6">
                    {/* Header */}

                    <HeadingSmall
                        title="Privacidade"
                        description="Controle quem pode ver o seu perfil, publicações e atividade"
                    />

                    {/* Privacy Settings Form */}
                    <form onSubmit={updatePrivacySettings}>
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="flex items-center gap-2 text-lg">
                                            <Eye className="size-5" />
                                            Visibilidade e Permissões
                                        </CardTitle>
                                        <CardDescription>
                                            Gerencie quem pode interagir consigo
                                        </CardDescription>
                                    </div>
                                    <Badge
                                        variant="secondary"
                                        className="hidden w-fit items-center gap-1.5 md:flex"
                                    >
                                        <Shield className="size-3" />
                                        Privacidade
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-8 pt-6">
                                {/* Profile Visibility Section */}
                                <div className="space-y-4">
                                    <div className="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        <User className="size-4" />
                                        <span>Visibilidade do Perfil</span>
                                    </div>
                                    <div className="space-y-3 rounded-xl bg-zinc-50/50 p-4 dark:bg-zinc-900/30">
                                        <Label
                                            htmlFor="profile_visibility"
                                            className="text-sm font-medium"
                                        >
                                            Quem pode ver o seu perfil
                                        </Label>
                                        <Select
                                            value={data.profile_visibility}
                                            onValueChange={(value) =>
                                                setData(
                                                    'profile_visibility',
                                                    value as PrivacySettings['profile_visibility'],
                                                )
                                            }
                                        >
                                            <SelectTrigger
                                                id="profile_visibility"
                                                className="h-11"
                                            >
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="public">
                                                    <div className="flex items-start gap-3">
                                                        <Globe className="size-4 shrink-0 text-green-600 dark:text-green-400" />
                                                        <div className="flex flex-col gap-0.5">
                                                            <span className="leading-tight font-medium">
                                                                Público
                                                            </span>
                                                        </div>
                                                    </div>
                                                </SelectItem>
                                                <SelectItem value="followers">
                                                    <div className="flex items-start gap-3">
                                                        <Users className="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400" />
                                                        <div className="flex flex-col gap-0.5">
                                                            <span className="leading-tight font-medium">
                                                                Seguidores
                                                            </span>
                                                        </div>
                                                    </div>
                                                </SelectItem>
                                                <SelectItem value="private">
                                                    <div className="flex items-start gap-3">
                                                        <Lock className="mt-0.5 size-4 shrink-0 text-red-600 dark:text-red-400" />
                                                        <div className="flex flex-col gap-0.5">
                                                            <span className="leading-tight font-medium">
                                                                Privado
                                                            </span>
                                                        </div>
                                                    </div>
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.profile_visibility && (
                                            <p className="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400">
                                                <XCircle className="size-4" />
                                                {errors.profile_visibility}
                                            </p>
                                        )}
                                        <p className="flex items-start gap-2 text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">
                                            <span className="mt-0.5 text-violet-500">
                                                •
                                            </span>
                                            <span>
                                                Controle quem pode visualizar o
                                                seu perfil completo e atividade
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                {/* Communication Settings */}
                                <div className="space-y-4">
                                    <div className="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        <MessageSquare className="size-4" />
                                        <span>Comunicação</span>
                                    </div>
                                    <div className="space-y-4">
                                        {/* Who Can Message */}
                                        <div className="space-y-3 rounded-xl bg-zinc-50/50 p-4 dark:bg-zinc-900/30">
                                            <Label
                                                htmlFor="who_can_message"
                                                className="text-sm font-medium"
                                            >
                                                Quem pode enviar mensagens
                                            </Label>
                                            <Select
                                                value={data.who_can_message}
                                                onValueChange={(value) =>
                                                    setData(
                                                        'who_can_message',
                                                        value as PrivacySettings['who_can_message'],
                                                    )
                                                }
                                            >
                                                <SelectTrigger
                                                    id="who_can_message"
                                                    className="h-11"
                                                >
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="everyone">
                                                        <div className="flex items-center gap-2.5">
                                                            <UserCheck className="size-4 shrink-0 text-green-600 dark:text-green-400" />
                                                            <span>Todos</span>
                                                        </div>
                                                    </SelectItem>
                                                    <SelectItem value="followers">
                                                        <div className="flex items-center gap-2.5">
                                                            <Users className="size-4 shrink-0 text-blue-600 dark:text-blue-400" />
                                                            <span>
                                                                Apenas
                                                                seguidores
                                                            </span>
                                                        </div>
                                                    </SelectItem>
                                                    <SelectItem value="none">
                                                        <div className="flex items-center gap-2.5">
                                                            <UserX className="size-4 shrink-0 text-red-600 dark:text-red-400" />
                                                            <span>Ninguém</span>
                                                        </div>
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            {errors.who_can_message && (
                                                <p className="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400">
                                                    <XCircle className="size-4" />
                                                    {errors.who_can_message}
                                                </p>
                                            )}
                                            <p className="flex items-start gap-2 text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">
                                                <span className="mt-0.5 text-violet-500">
                                                    •
                                                </span>
                                                <span>
                                                    Defina quem pode iniciar
                                                    conversas diretas consigo
                                                </span>
                                            </p>
                                        </div>
                                        {/* Who Can Comment */}
                                        <div className="space-y-3 rounded-xl bg-zinc-50/50 p-4 dark:bg-zinc-900/30">
                                            <Label
                                                htmlFor="who_can_comment"
                                                className="text-sm font-medium"
                                            >
                                                Quem pode comentar nas suas
                                                publicações
                                            </Label>
                                            <Select
                                                value={data.who_can_comment}
                                                onValueChange={(value) =>
                                                    setData(
                                                        'who_can_comment',
                                                        value as PrivacySettings['who_can_comment'],
                                                    )
                                                }
                                            >
                                                <SelectTrigger
                                                    id="who_can_comment"
                                                    className="h-11"
                                                >
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="everyone">
                                                        <div className="flex items-center gap-2.5">
                                                            <UserCheck className="size-4 shrink-0 text-green-600 dark:text-green-400" />
                                                            <span>Todos</span>
                                                        </div>
                                                    </SelectItem>
                                                    <SelectItem value="followers">
                                                        <div className="flex items-center gap-2.5">
                                                            <Users className="size-4 shrink-0 text-blue-600 dark:text-blue-400" />
                                                            <span>
                                                                Apenas
                                                                seguidores
                                                            </span>
                                                        </div>
                                                    </SelectItem>
                                                    <SelectItem value="disabled">
                                                        <div className="flex items-center gap-2.5">
                                                            <Lock className="size-4 shrink-0 text-red-600 dark:text-red-400" />
                                                            <span>
                                                                Desativado
                                                            </span>
                                                        </div>
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            {errors.who_can_comment && (
                                                <p className="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400">
                                                    <XCircle className="size-4" />
                                                    {errors.who_can_comment}
                                                </p>
                                            )}
                                            <p className="flex items-start gap-2 text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">
                                                <span className="mt-0.5 text-violet-500">
                                                    •
                                                </span>
                                                <span>
                                                    Controle quem pode comentar
                                                    nos seus hunts e publicações
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                {/* Additional Privacy Settings */}
                                <div className="space-y-4">
                                    <div className="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        <Shield className="size-4" />
                                        <span>Privacidade Adicional</span>
                                    </div>
                                    <div className="space-y-3">
                                        {/* Searchable */}
                                        <div
                                            className={cn(
                                                'group flex items-start justify-between gap-4 rounded-xl border p-4 transition-all duration-200',
                                                data.searchable
                                                    ? 'border-violet-200 bg-violet-50/50 dark:border-violet-900/50 dark:bg-violet-950/20'
                                                    : 'border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/30',
                                            )}
                                        >
                                            <div className="flex-1 space-y-1.5">
                                                <Label
                                                    htmlFor="searchable"
                                                    className="flex cursor-pointer items-center gap-2 text-sm font-medium"
                                                >
                                                    <Search
                                                        className={cn(
                                                            'size-4',
                                                            data.searchable
                                                                ? 'text-violet-600 dark:text-violet-400'
                                                                : '',
                                                        )}
                                                    />
                                                    Aparecer nas pesquisas
                                                </Label>
                                                <p className="text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">
                                                    Permite que outros Hunters
                                                    encontrem o seu perfil
                                                    através de pesquisas
                                                </p>
                                            </div>
                                            <Switch
                                                id="searchable"
                                                checked={data.searchable}
                                                onCheckedChange={(checked) =>
                                                    setData(
                                                        'searchable',
                                                        checked,
                                                    )
                                                }
                                            />
                                        </div>
                                        {/* Show Activity Status */}
                                        <div
                                            className={cn(
                                                'group flex items-start justify-between gap-4 rounded-xl border p-4 transition-all duration-200',
                                                data.show_activity_status
                                                    ? 'border-violet-200 bg-violet-50/50 dark:border-violet-900/50 dark:bg-violet-950/20'
                                                    : 'border-zinc-200 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/30',
                                            )}
                                        >
                                            <div className="flex-1 space-y-1.5">
                                                <Label
                                                    htmlFor="show_activity_status"
                                                    className="flex cursor-pointer items-center gap-2 text-sm font-medium"
                                                >
                                                    <Lock
                                                        className={cn(
                                                            'size-4',
                                                            data.show_activity_status
                                                                ? 'text-violet-600 dark:text-violet-400'
                                                                : '',
                                                        )}
                                                    />
                                                    Mostrar status de atividade
                                                </Label>
                                                <p className="text-xs leading-relaxed text-zinc-600 dark:text-zinc-400">
                                                    Permite que outros vejam
                                                    quando está online. Se
                                                    desativar, também não poderá
                                                    ver o status de outros
                                                    Hunters
                                                </p>
                                            </div>
                                            <Switch
                                                id="show_activity_status"
                                                checked={
                                                    data.show_activity_status
                                                }
                                                onCheckedChange={(checked) =>
                                                    setData(
                                                        'show_activity_status',
                                                        checked,
                                                    )
                                                }
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div className="flex items-center justify-end dark:border-zinc-800">
                                    <SaveButton disabled={processing} />
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                    {/* Blocked Users */}
                    <Card className="overflow-hidden">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2 text-lg">
                                        <UserX className="size-5" />
                                        Hunters bloqueados
                                    </CardTitle>
                                    <CardDescription>
                                        Gerencie os Hunters que bloqueou
                                    </CardDescription>
                                </div>
                                {blockedUsers.length > 0 && (
                                    <Badge
                                        variant="secondary"
                                        className="flex w-fit items-center gap-1.5"
                                    >
                                        {blockedUsers.length}{' '}
                                        {blockedUsers.length === 1
                                            ? 'bloqueado'
                                            : 'bloqueados'}
                                    </Badge>
                                )}
                            </div>
                        </CardHeader>
                        {/*<CardHeader>*/}
                        {/*    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">*/}
                        {/*        <div className="space-y-1.5">*/}
                        {/*            <CardTitle className="flex items-center gap-2.5 text-xl">*/}
                        {/*                <div className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-500/10 dark:bg-red-500/20">*/}
                        {/*                    <UserX className="size-5 text-red-600 dark:text-red-400" />*/}
                        {/*                </div>*/}
                        {/*                <span className="leading-tight">*/}
                        {/*                    Hunters bloqueados*/}
                        {/*                </span>*/}
                        {/*            </CardTitle>*/}
                        {/*            <CardDescription className="text-base">*/}
                        {/*                Gerencie os Hunters que bloqueou*/}
                        {/*            </CardDescription>*/}
                        {/*        </div>*/}
                        {/*        {blockedUsers.length > 0 && (*/}
                        {/*            <Badge*/}
                        {/*                variant="secondary"*/}
                        {/*                className="flex w-fit items-center gap-1.5"*/}
                        {/*            >*/}
                        {/*                {blockedUsers.length}{' '}*/}
                        {/*                {blockedUsers.length === 1*/}
                        {/*                    ? 'bloqueado'*/}
                        {/*                    : 'bloqueados'}*/}
                        {/*            </Badge>*/}
                        {/*        )}*/}
                        {/*    </div>*/}
                        {/*</CardHeader>*/}
                        <CardContent className="pt-6">
                            {blockedUsers.length === 0 ? (
                                <div className="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 py-16 dark:border-zinc-800 dark:bg-zinc-900/30">
                                    <div className="mb-4 flex size-16 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                                        <UserX className="size-8 text-zinc-400 dark:text-zinc-600" />
                                    </div>
                                    <p className="text-base font-medium text-zinc-900 dark:text-zinc-100">
                                        Nenhum Hunter bloqueado
                                    </p>
                                    <p className="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        Quando bloquear alguém, aparecerá aqui
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {blockedUsers.map((user) => (
                                        <div
                                            key={user.id}
                                            className="group flex items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-100/50 dark:border-zinc-800 dark:bg-zinc-900/30 dark:hover:border-zinc-700 dark:hover:bg-zinc-800/50"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="relative">
                                                    <img
                                                        src={
                                                            user.avatar_url ||
                                                            '/images/default-avatar.png'
                                                        }
                                                        alt={user.name}
                                                        className="size-12 rounded-xl border-2 border-zinc-200 object-cover dark:border-zinc-700"
                                                    />
                                                    <div className="absolute -right-1 -bottom-1 flex size-5 items-center justify-center rounded-full border-2 border-white bg-red-500 dark:border-zinc-900">
                                                        <Lock className="size-3 text-white" />
                                                    </div>
                                                </div>
                                                <div className="flex flex-col">
                                                    <p className="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        {user.name}
                                                    </p>
                                                    <p className="text-sm text-zinc-600 dark:text-zinc-400">
                                                        @{user.user_name}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <Badge
                                                    variant="outline"
                                                    className="hidden items-center gap-1.5 sm:flex"
                                                >
                                                    <div className="size-1.5 rounded-full bg-red-500" />
                                                    Bloqueado em{' '}
                                                    {user.blocked_at}
                                                </Badge>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        setUserToUnblock(
                                                            user.id,
                                                        )
                                                    }
                                                    className="gap-1.5 hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 dark:hover:border-violet-700 dark:hover:bg-violet-950/50 dark:hover:text-violet-400"
                                                >
                                                    <UserCheck className="size-4" />
                                                    Desbloquear
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                    {/* Unblock User Dialog */}
                    <AlertDialog
                        open={userToUnblock !== null}
                        onOpenChange={(open) => !open && setUserToUnblock(null)}
                    >
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    Desbloquear Hunter?
                                </AlertDialogTitle>
                                <AlertDialogDescription>
                                    Após desbloquear, este Hunter poderá voltar
                                    a interagir consigo de acordo com as suas
                                    definições de privacidade.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                <AlertDialogAction
                                    onClick={() =>
                                        userToUnblock &&
                                        unblockUser(userToUnblock)
                                    }
                                >
                                    Desbloquear
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
