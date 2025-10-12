import ChangelogController from '@/actions/App/Http/Controllers/ChangelogController';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    CheckCircle2,
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
    entry: ChangelogEntry;
}

export default function ChangelogShow({ entry }: Props) {
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
            <Head title={`Changelog - ${entry.formatted_version}`} />

            <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                {/* Back Button */}
                <Link
                    href={ChangelogController.index().url}
                    className="mb-6 inline-flex items-center gap-2 text-sm font-medium text-zinc-600 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    preserveScroll
                >
                    <ArrowLeft className="h-4 w-4" />
                    Voltar ao Changelog
                </Link>

                {/* Header */}
                <Card className="mb-8">
                    <CardHeader>
                        <div className="flex items-center gap-3">
                            <h1 className="text-4xl font-bold">
                                {entry.formatted_version}
                            </h1>
                            {entry.is_latest && (
                                <span className="rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-700 dark:bg-purple-900 dark:text-purple-300">
                                    Mais Recente
                                </span>
                            )}
                        </div>
                        {entry.formatted_date && (
                            <div className="mt-2 flex items-center gap-2 text-muted-foreground">
                                <Calendar className="h-5 w-5" />
                                <span className="text-lg">
                                    {entry.formatted_date}
                                </span>
                                {entry.human_date && (
                                    <span className="text-sm">
                                        ({entry.human_date})
                                    </span>
                                )}
                            </div>
                        )}
                        <p className="mt-4 text-lg text-muted-foreground">
                            {entry.total_changes} mudanças em{' '}
                            {Object.keys(entry.sections).length} categorias
                        </p>
                    </CardHeader>
                </Card>

                {/* Sections */}
                <div className="space-y-6">
                    {Object.entries(entry.sections).map(
                        ([sectionName, items]) => (
                            <Card key={sectionName}>
                                <CardHeader>
                                    <div
                                        className={`inline-flex items-center gap-2 rounded-lg px-3 py-1 text-sm font-semibold ${getSectionColor(sectionName)}`}
                                    >
                                        {getSectionIcon(sectionName)}
                                        {sectionName}
                                        <span className="ml-2 rounded-full bg-white/60 px-2 py-0.5 text-xs dark:bg-black/20">
                                            {items.length}
                                        </span>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <ul className="space-y-3">
                                        {items.map((item, idx) => (
                                            <li
                                                key={idx}
                                                className="flex items-start gap-3"
                                            >
                                                <span className="mt-2 h-2 w-2 flex-shrink-0 rounded-full bg-zinc-400 dark:bg-zinc-500" />
                                                <span className="flex-1">
                                                    {item}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                </CardContent>
                            </Card>
                        ),
                    )}
                </div>

                {/* Footer Navigation */}
                <div className="mt-8 flex justify-center">
                    <Link
                        href={ChangelogController.index().url}
                        className="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-6 py-3 font-medium text-zinc-900 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700"
                        preserveScroll
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Ver Todas as Versões
                    </Link>
                </div>
            </div>
        </AppLayout>
    );
}
