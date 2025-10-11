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

            <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="mb-8 text-center">
                    <h1 className="text-4xl font-bold text-zinc-900 dark:text-zinc-100">
                        Changelog
                    </h1>
                    <p className="mt-2 text-lg text-zinc-600 dark:text-zinc-400">
                        Todas as mudanças notáveis do Hunter são documentadas
                        aqui
                    </p>
                </div>

                {/* Changelog Entries */}
                <div className="space-y-8">
                    {entries.map((entry) => (
                        <Card
                            key={entry.version}
                            className="group transition-all hover:shadow-md"
                        >
                            <CardHeader>
                                {/* Version Header */}
                                <div className="flex items-start justify-between">
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <h2 className="text-2xl font-bold">
                                                {entry.formatted_version}
                                            </h2>
                                            {entry.is_latest && (
                                                <span className="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-700 dark:bg-purple-900 dark:text-purple-300">
                                                    Mais Recente
                                                </span>
                                            )}
                                        </div>
                                        {entry.formatted_date && (
                                            <div className="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                                <Calendar className="h-4 w-4" />
                                                <span>
                                                    {entry.formatted_date}
                                                </span>
                                                {entry.human_date && (
                                                    <span className="text-zinc-400 dark:text-zinc-500">
                                                        ({entry.human_date})
                                                    </span>
                                                )}
                                            </div>
                                        )}
                                    </div>

                                    <Link
                                        href={
                                            ChangelogController.show(
                                                entry.version,
                                            ).url
                                        }
                                        className="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                    >
                                        Ver Detalhes
                                        <ChevronRight className="h-4 w-4" />
                                    </Link>
                                </div>

                                {/* Stats */}
                                <div className="flex items-center gap-4 text-sm">
                                    <span className="flex items-center gap-1 text-muted-foreground">
                                        <CheckCircle2 className="h-4 w-4" />
                                        {entry.total_changes} mudanças
                                    </span>
                                    <span className="flex items-center gap-1 text-muted-foreground">
                                        <GitBranch className="h-4 w-4" />
                                        {
                                            Object.keys(entry.sections).length
                                        }{' '}
                                        seções
                                    </span>
                                </div>
                            </CardHeader>

                            <CardContent>
                                {/* Sections */}
                                <div className="space-y-4">
                                    {Object.entries(entry.sections).map(
                                        ([sectionName, items]) => (
                                            <div key={sectionName}>
                                                <div
                                                    className={`mb-2 inline-flex items-center gap-2 rounded-lg px-3 py-1 text-sm font-semibold ${getSectionColor(sectionName)}`}
                                                >
                                                    {getSectionIcon(
                                                        sectionName,
                                                    )}
                                                    {sectionName}
                                                </div>
                                                <ul className="ml-4 space-y-1 text-sm">
                                                    {items
                                                        .slice(0, 3)
                                                        .map((item, idx) => (
                                                            <li
                                                                key={idx}
                                                                className="flex items-start gap-2"
                                                            >
                                                                <span className="mt-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-zinc-400 dark:bg-zinc-500" />
                                                                <span>
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
                    <Card className="p-12 text-center">
                        <GitBranch className="mx-auto h-12 w-12 text-zinc-400" />
                        <h3 className="mt-4 text-lg font-medium">
                            Nenhuma entrada encontrada
                        </h3>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Volte mais tarde para ver atualizações e melhorias.
                        </p>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
