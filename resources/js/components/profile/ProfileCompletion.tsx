import { Checkbox } from '@/components/ui/checkbox';
import { Option } from '@/components/ui/multiselect';
import { useAboutStore } from '@/stores/about';
import { useAcademicBackground } from '@/stores/academicBackground';
import { useHighlightedSkills } from '@/stores/highlightedSkills';
import { useLinkStore } from '@/stores/link';
import { AcademicBackground, SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

interface ProfileCompletionProps {
    academicBackgrounds: AcademicBackground[];
    skills: Option[];
}

export function ProfileCompletion({
    skills,
    academicBackgrounds,
}: ProfileCompletionProps) {
    const { auth } = usePage<SharedData>().props;

    const { open: openAbout } = useAboutStore();
    const { open: openLink } = useLinkStore();
    const { open: openHighLightSkills } = useHighlightedSkills();
    const { open: openAcademicBackground } = useAcademicBackground();

    const profileCompletion = [
        {
            label: 'Informações básicas',
            done: !!(auth.user.name && auth.user.email),
        },
        {
            label: 'Foto de perfil',
            done: !!auth.user.avatar_url,
        },
        {
            label: 'Links',
            done: !!(
                auth.user.website_url ||
                auth.user.linkedin_url ||
                auth.user.github_url ||
                auth.user.twitter_url ||
                auth.user.bluesky_url ||
                auth.user.youtube_url
            ),
            onclick: () => openLink(),
        },
        {
            label: 'Sobre você',
            done: !!auth.user.bio,
            onclick: () => openAbout(),
        },
        {
            label: 'Formação Acadêmica',
            done: academicBackgrounds.length > 0,
            onclick: () => openAcademicBackground(),
        },
        // {
        //     label: 'Destaques Profissionais',
        //     done: academicBackgrounds.length > 0, // ou outro campo de destaque
        // },
        {
            label: 'Destacar Tecnolodias',
            done: skills && skills.length > 0,
            onclick: () => openHighLightSkills(),
        },
    ];

    function calculateProfileCompletion(
        profileCompletion: { done: boolean }[],
    ): number {
        const completedItems = profileCompletion.filter(
            (item) => item.done,
        ).length;
        const totalItems = profileCompletion.length;
        return (completedItems / totalItems) * 100;
    }

    const profileCompletionPercentage =
        calculateProfileCompletion(profileCompletion);

    return (
        <section className="gradient max-w-4xl rounded-lg bg-card p-6">
            <div className="mb-4 flex w-full items-center justify-between">
                <div>
                    <h3 className="text-lg font-semibold text-foreground">
                        {profileCompletionPercentage < 100
                            ? 'Complete seu perfil'
                            : 'Perfil Completo'}
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        Perfis completos atraem mais oportunidades!
                    </p>
                </div>
                {profileCompletionPercentage < 100 && (
                    <span className="text-sm text-muted-foreground">
                        {Math.round(
                            (profileCompletion.filter((item) => item.done)
                                .length /
                                profileCompletion.length) *
                                100,
                        )}
                        % completo
                    </span>
                )}
            </div>
            <div className="mb-6 h-2 overflow-hidden rounded-full bg-secondary">
                <div
                    className="h-full bg-primary transition-all"
                    style={{ width: `${profileCompletionPercentage}%` }}
                />
            </div>
            {profileCompletionPercentage < 100 && (
                <div className="grid grid-cols-2 gap-4">
                    {profileCompletion.map((item) => (
                        <label
                            key={item.label}
                            className="flex cursor-pointer items-center space-x-2"
                            onClick={() => !item.done && item.onclick?.()}
                        >
                            <Checkbox
                                checked={item.done}
                                className="h-4 w-4 rounded border-input focus:ring-0 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                            />
                            <span
                                className={`text-sm ${item.done ? 'text-muted-foreground line-through' : 'text-foreground'}`}
                            >
                                {item.label}
                            </span>
                        </label>
                    ))}
                </div>
            )}
        </section>
    );
}
