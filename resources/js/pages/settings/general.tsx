import { Head, useForm } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { BookOpen, Loader2 } from 'lucide-react';

import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController';
import { useToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Definições gerais',
        href: '/settings/general',
    },
];

export default function General() {
    const { post, processing } = useForm({});
    const { toast } = useToast();

    const handleReplayTutorial = () => {
        post(OnboardingController.destroy().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: 'Tutorial reiniciado',
                    description: 'O tutorial será aberto em alguns instantes...',
                    duration: 3000,
                });
            },
            onError: () => {
                toast({
                    title: 'Erro',
                    description: 'Não foi possível reiniciar o tutorial. Tente novamente.',
                    variant: 'destructive',
                    duration: 3000,
                });
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Definições gerais" />
            <SettingsLayout>
                <div className="space-y-4 sm:space-y-6">
                    <HeadingSmall title="Definições gerais" description="Gerencie as configurações gerais da sua conta" />

                    {/* Replay Tutorial Section */}
                    <Card className="gradient">
                        <CardHeader className="space-y-1 p-4 sm:p-6">
                            <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
                                <BookOpen className="h-5 w-5 flex-shrink-0" />
                                Tutorial de Boas-Vindas
                            </CardTitle>
                            <CardDescription className="text-sm">
                                Reveja o tutorial para relembrar as principais funcionalidades do DevHunter
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex justify-end p-4 pt-0 sm:p-6 sm:pt-0">
                            <Button onClick={handleReplayTutorial} className="w-full cursor-pointer sm:w-auto" disabled={processing}>
                                {processing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <BookOpen className="mr-2 h-4 w-4" />}
                                {processing ? 'A reiniciar tutorial...' : 'Repetir Tutorial'}
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
