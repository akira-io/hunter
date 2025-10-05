import PrivacyController from '@/actions/App/Http/Controllers/Settings/PrivacyController';
import HeadingSmall from '@/components/heading-small';
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { CheckCircle, Eye, Lock, MessageSquare, Search, User, UserX, XCircle } from 'lucide-react';
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

    const { data, setData, post, errors, processing } = useForm<PrivacySettings>(privacySettings);

    const updatePrivacySettings: FormEventHandler = (e) => {
        e.preventDefault();
        post(PrivacyController.update().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    icon: <CheckCircle className="text-green-400" />,
                    title: 'Definições atualizadas',
                    description: 'As suas definições de privacidade foram atualizadas com sucesso.',
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    icon: <XCircle className="text-red-400" />,
                    title: 'Erro',
                    description: 'Erro ao atualizar as definições de privacidade. Tente novamente.',
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
                    title: 'Utilizador desbloqueado',
                    description: 'O utilizador foi desbloqueado com sucesso.',
                });
            },
            onError: () => {
                toast({
                    variant: 'destructive',
                    icon: <XCircle className="text-red-400" />,
                    title: 'Erro',
                    description: 'Erro ao desbloquear utilizador. Tente novamente.',
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
                    <div>
                        <HeadingSmall title="Privacidade" />
                        <p className="text-sm text-zinc-600 dark:text-zinc-400">Controle quem pode ver o seu perfil, publicações e atividade</p>
                    </div>

                    {/* Privacy Settings Form */}
                    <form onSubmit={updatePrivacySettings}>
                        <Card className="gradient">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Eye className="size-5" />
                                    Visibilidade e Permissões
                                </CardTitle>
                                <CardDescription>Gerencie quem pode interagir consigo</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                {/* Profile Visibility */}
                                <div className="space-y-2">
                                    <Label htmlFor="profile_visibility" className="flex items-center gap-2">
                                        <User className="size-4" />
                                        Visibilidade do perfil
                                    </Label>
                                    <Select
                                        value={data.profile_visibility}
                                        onValueChange={(value) => setData('profile_visibility', value as PrivacySettings['profile_visibility'])}
                                    >
                                        <SelectTrigger id="profile_visibility">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="public">Público - Todos podem ver</SelectItem>
                                            <SelectItem value="followers">Seguidores - Apenas seguidores podem ver</SelectItem>
                                            <SelectItem value="private">Privado - Apenas eu posso ver</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.profile_visibility && (
                                        <p className="text-sm text-red-600 dark:text-red-400">{errors.profile_visibility}</p>
                                    )}
                                    <p className="text-xs text-zinc-500 dark:text-zinc-400">Controle quem pode visualizar o seu perfil completo</p>
                                </div>

                                {/* Who Can Message */}
                                <div className="space-y-2">
                                    <Label htmlFor="who_can_message" className="flex items-center gap-2">
                                        <MessageSquare className="size-4" />
                                        Quem pode enviar mensagens
                                    </Label>
                                    <Select
                                        value={data.who_can_message}
                                        onValueChange={(value) => setData('who_can_message', value as PrivacySettings['who_can_message'])}
                                    >
                                        <SelectTrigger id="who_can_message">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="everyone">Todos</SelectItem>
                                            <SelectItem value="followers">Apenas seguidores</SelectItem>
                                            <SelectItem value="none">Ninguém</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.who_can_message && <p className="text-sm text-red-600 dark:text-red-400">{errors.who_can_message}</p>}
                                    <p className="text-xs text-zinc-500 dark:text-zinc-400">Defina quem pode iniciar conversas consigo</p>
                                </div>

                                {/* Who Can Comment */}
                                <div className="space-y-2">
                                    <Label htmlFor="who_can_comment" className="flex items-center gap-2">
                                        <MessageSquare className="size-4" />
                                        Quem pode comentar nas suas publicações
                                    </Label>
                                    <Select
                                        value={data.who_can_comment}
                                        onValueChange={(value) => setData('who_can_comment', value as PrivacySettings['who_can_comment'])}
                                    >
                                        <SelectTrigger id="who_can_comment">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="everyone">Todos</SelectItem>
                                            <SelectItem value="followers">Apenas seguidores</SelectItem>
                                            <SelectItem value="disabled">Desativado</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.who_can_comment && <p className="text-sm text-red-600 dark:text-red-400">{errors.who_can_comment}</p>}
                                    <p className="text-xs text-zinc-500 dark:text-zinc-400">Controle quem pode comentar nos seus hunts</p>
                                </div>

                                {/* Searchable */}
                                <div className="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                                    <div className="space-y-0.5">
                                        <Label htmlFor="searchable" className="flex items-center gap-2">
                                            <Search className="size-4" />
                                            Aparecer nas pesquisas
                                        </Label>
                                        <p className="text-xs text-zinc-500 dark:text-zinc-400">
                                            Permite que outros utilizadores encontrem o seu perfil através de pesquisas
                                        </p>
                                    </div>
                                    <Switch id="searchable" checked={data.searchable} onCheckedChange={(checked) => setData('searchable', checked)} />
                                </div>

                                {/* Show Activity Status */}
                                <div className="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                                    <div className="space-y-0.5">
                                        <Label htmlFor="show_activity_status" className="flex items-center gap-2">
                                            <Lock className="size-4" />
                                            Mostrar status de atividade
                                        </Label>
                                        <p className="text-xs text-zinc-500 dark:text-zinc-400">Permite que outros vejam quando está online</p>
                                    </div>
                                    <Switch
                                        id="show_activity_status"
                                        checked={data.show_activity_status}
                                        onCheckedChange={(checked) => setData('show_activity_status', checked)}
                                    />
                                </div>

                                <div className="flex justify-end border-t border-zinc-200 pt-4 dark:border-zinc-800">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'A guardar...' : 'Guardar alterações'}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </form>

                    {/* Blocked Users */}
                    <Card className="gradient">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <UserX className="size-5" />
                                Utilizadores bloqueados
                            </CardTitle>
                            <CardDescription>Gerencie os utilizadores que bloqueou</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {blockedUsers.length === 0 ? (
                                <div className="py-8 text-center">
                                    <UserX className="mx-auto mb-3 size-12 text-zinc-400 dark:text-zinc-600" />
                                    <p className="text-sm text-zinc-600 dark:text-zinc-400">Não bloqueou nenhum utilizador</p>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {blockedUsers.map((user) => (
                                        <div
                                            key={user.id}
                                            className="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-800"
                                        >
                                            <div className="flex items-center gap-3">
                                                <img
                                                    src={user.avatar_url || '/images/default-avatar.png'}
                                                    alt={user.name}
                                                    className="size-10 rounded-full"
                                                />
                                                <div>
                                                    <p className="font-medium text-zinc-900 dark:text-zinc-100">{user.name}</p>
                                                    <p className="text-sm text-zinc-600 dark:text-zinc-400">@{user.user_name}</p>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <Badge variant="secondary" className="hidden sm:inline-flex">
                                                    Bloqueado em {user.blocked_at}
                                                </Badge>
                                                <Button size="sm" variant="outline" onClick={() => setUserToUnblock(user.id)}>
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
                    <AlertDialog open={userToUnblock !== null} onOpenChange={(open) => !open && setUserToUnblock(null)}>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Desbloquear utilizador?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Após desbloquear, este utilizador poderá voltar a interagir consigo de acordo com as suas definições de
                                    privacidade.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                <AlertDialogAction onClick={() => userToUnblock && unblockUser(userToUnblock)}>Desbloquear</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
