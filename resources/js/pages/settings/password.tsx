import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useRef } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import password from '@/routes/password';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Definições de password',
        href: '/settings/password',
    },
];

export default function Password() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword: FormEventHandler = (e) => {
        e.preventDefault();

        put(password.update().url, {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Definições de Perfil" />
            <SettingsLayout>
                <div className="space-y-4 sm:space-y-6">
                    <HeadingSmall title="Dados de acesso" description="Gerencie a senha da sua conta" />

                    <Card className="gradient">
                        {/*<CardHeader className="-mb-10 space-y-1 sm:p-6">*/}
                        {/*    <CardTitle className="flex items-center gap-2 text-base sm:text-lg">*/}
                        {/*        <KeyRound className="h-5 w-5 flex-shrink-0" />*/}
                        {/*        Atualizar password*/}
                        {/*    </CardTitle>*/}
                        {/*    <CardDescription className="text-sm">Tenha a certeza que a sua conta tenha uma password segura</CardDescription>*/}
                        {/*</CardHeader>*/}
                        <CardContent className="sm:p-6">
                            <form onSubmit={updatePassword} className="space-y-4 sm:space-y-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">Password Atual</Label>
                                    <Input
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        value={data.current_password}
                                        onChange={(e) => setData('current_password', e.target.value)}
                                        type="password"
                                        className="mt-1 block w-full"
                                        autoComplete="current-password"
                                        placeholder="Password atual"
                                    />
                                    <InputError message={errors.current_password} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password">Nova password</Label>
                                    <Input
                                        id="password"
                                        ref={passwordInput}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        type="password"
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        placeholder="Nova password"
                                    />
                                    <InputError message={errors.password} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">Confirmar password</Label>
                                    <Input
                                        id="password_confirmation"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        type="password"
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        placeholder="Confirmar password"
                                    />
                                    <InputError message={errors.password_confirmation} />
                                </div>
                                <div className="flex items-center justify-end gap-4">
                                    <Button disabled={processing} variant="gradient">
                                        Guardar password
                                    </Button>
                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">Guardado</p>
                                    </Transition>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
