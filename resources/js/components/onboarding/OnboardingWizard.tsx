import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Progress } from '@/components/ui/progress';
import { router } from '@inertiajs/react';
import { BookOpen, Heart, Rocket, Users } from 'lucide-react';
import { useState } from 'react';

interface OnboardingWizardProps {
    isOpen: boolean;
    onClose: () => void;
    onComplete?: () => void;
}

interface Step {
    id: number;
    title: string;
    description: string;
    icon: React.ReactNode;
    content: React.ReactNode;
}

export function OnboardingWizard({
    isOpen,
    onClose,
    onComplete,
}: OnboardingWizardProps) {
    const [currentStep, setCurrentStep] = useState(0);

    const steps: Step[] = [
        {
            id: 1,
            title: 'Bem-vindo ao DevHunter! 🎉',
            description:
                'Vamos fazer um tour rápido pelas principais funcionalidades',
            icon: <Rocket className="h-8 w-8 text-primary" />,
            content: (
                <div className="space-y-4 text-center">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                        <Rocket className="h-8 w-8 text-primary" />
                    </div>
                    <h3 className="text-xl font-bold">
                        Bem-vindo ao DevHunter!
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        O DevHunter é a plataforma que conecta desenvolvedores
                        em Cabo Verde. Aqui você pode compartilhar projetos,
                        aprender com outros desenvolvedores e construir sua rede
                        profissional.
                    </p>
                    <div className="grid grid-cols-3 gap-4 rounded-lg bg-secondary p-4">
                        <div className="flex flex-col items-center gap-2">
                            <Users className="h-6 w-6 text-primary" />
                            <span className="text-xs font-medium">
                                Conectar
                            </span>
                        </div>
                        <div className="flex flex-col items-center gap-2">
                            <BookOpen className="h-6 w-6 text-primary" />
                            <span className="text-xs font-medium">
                                Aprender
                            </span>
                        </div>
                        <div className="flex flex-col items-center gap-2">
                            <Heart className="h-6 w-6 text-primary" />
                            <span className="text-xs font-medium">
                                Colaborar
                            </span>
                        </div>
                    </div>
                </div>
            ),
        },
        {
            id: 2,
            title: 'Complete seu Perfil',
            description:
                'Adicione suas informações básicas e mostre quem você é',
            icon: <Users className="h-8 w-8 text-primary" />,
            content: (
                <div className="space-y-4">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                        <Users className="h-8 w-8 text-primary" />
                    </div>
                    <h3 className="text-center text-xl font-bold">
                        Complete seu Perfil
                    </h3>
                    <p className="text-center text-sm text-muted-foreground">
                        Um perfil completo ajuda outros desenvolvedores a
                        conhecerem você melhor!
                    </p>
                    <Card className="gradient bg-card">
                        <CardContent className="space-y-3 p-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm font-bold text-primary">
                                        1
                                    </span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Foto de perfil
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Adicione uma foto para se destacar
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm font-bold text-primary">
                                        2
                                    </span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Bio e localização
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Conte um pouco sobre você
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm font-bold text-primary">
                                        3
                                    </span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Skills e tecnologias
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Mostre suas habilidades
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            ),
        },
        {
            id: 3,
            title: 'Compartilhe um Projeto',
            description: 'Publique seus projetos e ideias para a comunidade',
            icon: <Rocket className="h-8 w-8 text-primary" />,
            content: (
                <div className="space-y-4">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                        <Rocket className="h-8 w-8 text-primary" />
                    </div>
                    <h3 className="text-center text-xl font-bold">
                        Compartilhe um Projeto
                    </h3>
                    <p className="text-center text-sm text-muted-foreground">
                        Mostre seus projetos para outros desenvolvedores e
                        receba feedback!
                    </p>
                    <Card className="gradient bg-card">
                        <CardContent className="space-y-3 p-4">
                            <div className="flex items-start gap-3">
                                <div className="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm">💡</span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Compartilhe ideias
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Publique seus pensamentos e projetos
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm">💬</span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Receba feedback
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Outros desenvolvedores podem comentar e
                                        curtir
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="mt-1 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm">🚀</span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Ganhe visibilidade
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Projetos interessantes aparecem no feed
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            ),
        },
        {
            id: 4,
            title: 'Conecte-se com Desenvolvedores',
            description: 'Siga outros hunters e expanda sua rede',
            icon: <Users className="h-8 w-8 text-primary" />,
            content: (
                <div className="space-y-4">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                        <Users className="h-8 w-8 text-primary" />
                    </div>
                    <h3 className="text-center text-xl font-bold">
                        Conecte-se com Desenvolvedores
                    </h3>
                    <p className="text-center text-sm text-muted-foreground">
                        Descubra e siga outros desenvolvedores talentosos de
                        Cabo Verde!
                    </p>
                    <Card className="gradient bg-card">
                        <CardContent className="space-y-3 p-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm">🔍</span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Explore desenvolvedores
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Encontre hunters com skills
                                        interessantes
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm">➕</span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Siga e seja seguido
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Mantenha-se atualizado com novos
                                        projetos
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                    <span className="text-sm">💬</span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        Colabore e aprenda
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Troque experiências e conhecimento
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            ),
        },
    ];

    const progress = ((currentStep + 1) / steps.length) * 100;

    const handleNext = () => {
        if (currentStep < steps.length - 1) {
            setCurrentStep(currentStep + 1);
        }
    };

    const handleBack = () => {
        if (currentStep > 0) {
            setCurrentStep(currentStep - 1);
        }
    };

    const handleSkip = () => {
        handleComplete();
    };

    const handleComplete = () => {
        router.post(
            '/onboarding/complete',
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    onComplete?.();
                    onClose();
                },
            },
        );
    };

    const currentStepData = steps[currentStep];

    return (
        <Dialog open={isOpen} onOpenChange={(open) => !open && handleSkip()}>
            <DialogContent className="flex h-full flex-col overflow-hidden rounded-lg p-0 shadow-lg md:h-[600px] md:max-w-2xl">
                {/* Header - Fixo */}
                <div className="flex-shrink-0 border-b p-6">
                    <div className="mb-3 flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-semibold">
                                {currentStepData.title}
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                {currentStepData.description}
                            </p>
                        </div>
                        {/*<Button variant="ghost" size="icon" onClick={handleSkip}>*/}
                        {/*    <X className="h-4 w-4" />*/}
                        {/*</Button>*/}
                    </div>
                    <div className="space-y-2">
                        <div className="flex items-center justify-between text-sm">
                            <span className="text-muted-foreground">
                                Passo {currentStep + 1} de {steps.length}
                            </span>
                            <span className="text-muted-foreground">
                                {Math.round(progress)}%
                            </span>
                        </div>
                        <Progress value={progress} className="h-2" />
                    </div>
                </div>

                {/* Content - Scrollável */}
                <div className="flex-1 overflow-y-auto px-6 py-6">
                    {currentStepData.content}
                </div>

                {/* Footer - Fixo */}
                <div className="flex-shrink-0 border-t p-6">
                    <div className="flex items-center justify-between">
                        <Button
                            variant="ghost"
                            onClick={handleBack}
                            disabled={currentStep === 0}
                        >
                            Voltar
                        </Button>
                        <div className="flex gap-2">
                            {currentStep < steps.length - 1 ? (
                                <Button onClick={handleNext} variant="gradient">
                                    Próximo
                                </Button>
                            ) : (
                                <Button
                                    onClick={handleComplete}
                                    variant="gradient"
                                >
                                    Concluir
                                </Button>
                            )}
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
