import ChangelogController from '@/actions/App/Http/Controllers/ChangelogController';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    Calendar,
    CheckCircle2,
    ChevronRight,
    GitBranch,
    Sparkles,
} from 'lucide-react';

interface ChangelogEntry {
    version: string;
    formatted_version: string;
    date: string | null;
    formatted_date: string | null;
    human_date: string | null;
    is_latest: boolean;
    sections: Record<string, string[]>;
    total_changes: number;
}

interface Props {
    entries: ChangelogEntry[];
}

export default function ChangelogIndex({ entries }: Props) {
    const getSectionIcon = (sectionName: string) => {
        const name = sectionName.toLowerCase();
        if (name.includes('feature')) return <Sparkles className="h-4 w-4" />;
        if (name.includes('bug') || name.includes('fix'))
            return <CheckCircle2 className="h-4 w-4" />;
        return <GitBranch className="h-4 w-4" />;
    };

    const getSectionColor = (sectionName: string) => {
        const name = sectionName.toLowerCase();
        if (name.includes('feature'))
            return 'text-purple-600 bg-purple-50 dark:text-purple-400 dark:bg-purple-950';
        if (name.includes('bug') || name.includes('fix'))
            return 'text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-950';
        return 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-950';
    };

    return (
        <AppLayout>
            <Head title="Changelog" />

            <div className="mx-auto max-w-4xl px-3 py-6 sm:px-6 sm:py-8 lg:px-8">
                {/* Header */}
                <div className="mb-6 text-center sm:mb-8">
                    <h1 className="text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-zinc-100">
                        Changelog
                    </h1>
                    <p className="mt-2 text-base text-zinc-600 sm:text-lg dark:text-zinc-400">
                        Todas as mudanças notáveis do Hunter são documentadas
                        aqui
                    </p>
                </div>

                {/* Changelog Entries */}
                <div className="space-y-4 sm:space-y-8">
                    {entries.map((entry) => (
                        <Card
                            key={entry.version}
                            className="group overflow-hidden transition-all hover:shadow-md"
                        >
                            <CardHeader className="space-y-3 p-4 sm:p-6">
                                {/* Version Header */}
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h2 className="text-xl font-bold sm:text-2xl">
                                                {entry.formatted_version}
                                            </h2>
                                            {entry.is_latest && (
                                                <span className="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700 sm:px-2 sm:py-1 dark:bg-purple-900 dark:text-purple-300">
                                                    Mais Recente
                                                </span>
                                            )}
                                        </div>
                                        {entry.formatted_date && (
                                            <div className="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-muted-foreground sm:text-sm">
                                                <div className="flex items-center gap-1.5">
                                                    <Calendar className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                                    <span>
                                                        {entry.formatted_date}
                                                    </span>
                                                </div>
                                                {entry.human_date && (
                                                    <span className="text-zinc-400 dark:text-zinc-500">
                                                        ({entry.human_date})
                                                    </span>
                                                )}
                                            </div>
                                        )}
                                    </div>

                                    {/* Details Button - Desktop */}
                                    <Link
                                        href={
                                            ChangelogController.show(
                                                entry.version,
                                            ).url
                                        }
                                        className="hidden items-center gap-2 rounded-lg bg-zinc-100 px-4 py-2.5 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-200 active:bg-zinc-300 sm:flex dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700 dark:active:bg-zinc-600"
                                    >
                                        Ver Detalhes Completos
                                        <ChevronRight className="h-4 w-4" />
                                    </Link>
                                </div>

                                {/* Stats */}
                                <div className="flex flex-wrap items-center gap-3 text-xs sm:gap-4 sm:text-sm">
                                    <span className="flex items-center gap-1.5 text-muted-foreground">
                                        <CheckCircle2 className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                        {entry.total_changes} mudanças
                                    </span>
                                    <span className="flex items-center gap-1.5 text-muted-foreground">
                                        <GitBranch className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                        {Object.keys(entry.sections).length}{' '}
                                        seções
                                    </span>
                                </div>

                                {/* Details Button - Mobile (Full Width) */}
                                <Link
                                    href={
                                        ChangelogController.show(entry.version)
                                            .url
                                    }
                                    className="flex items-center justify-center gap-2 rounded-lg bg-zinc-100 px-4 py-2.5 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-200 active:bg-zinc-300 sm:hidden dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700 dark:active:bg-zinc-600"
                                >
                                    Ver Detalhes Completos
                                    <ChevronRight className="h-4 w-4" />
                                </Link>
                            </CardHeader>

                            <CardContent className="p-4 pt-0 sm:p-6 sm:pt-0">
                                {/* Sections */}
                                <div className="space-y-3 sm:space-y-4">
                                    {Object.entries(entry.sections).map(
                                        ([sectionName, items]) => (
                                            <div key={sectionName}>
                                                <div
                                                    className={`mb-2 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold sm:gap-2 sm:px-3 sm:text-sm ${getSectionColor(sectionName)}`}
                                                >
                                                    {getSectionIcon(
                                                        sectionName,
                                                    )}
                                                    {sectionName}
                                                </div>
                                                <ul className="ml-3 space-y-1.5 text-xs sm:ml-4 sm:space-y-1 sm:text-sm">
                                                    {items
                                                        .slice(0, 3)
                                                        .map((item, idx) => (
                                                            <li
                                                                key={idx}
                                                                className="flex items-start gap-2"
                                                            >
                                                                <span className="mt-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-zinc-400 dark:bg-zinc-500" />
                                                                <span className="flex-1 break-words">
                                                                    {item}
                                                                </span>
                                                            </li>
                                                        ))}
                                                    {items.length > 3 && (
                                                        <li className="text-xs text-muted-foreground italic">
                                                            +{items.length - 3}{' '}
                                                            mais mudanças
                                                        </li>
                                                    )}
                                                </ul>
                                            </div>
                                        ),
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Footer */}
                {entries.length === 0 && (
                    <Card className="p-8 text-center sm:p-12">
                        <GitBranch className="mx-auto h-10 w-10 text-zinc-400 sm:h-12 sm:w-12" />
                        <h3 className="mt-4 text-base font-medium sm:text-lg">
                            Nenhuma entrada encontrada
                        </h3>
                        <p className="mt-2 text-xs text-muted-foreground sm:text-sm">
                            Volte mais tarde para ver atualizações e melhorias.
                        </p>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
