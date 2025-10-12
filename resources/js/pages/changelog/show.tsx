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

            <div className="mx-auto max-w-4xl px-3 py-6 sm:px-6 sm:py-8 lg:px-8">
                {/* Back Button */}
                <Link
                    href={ChangelogController.index().url}
                    className="mb-4 inline-flex items-center gap-2 text-sm font-medium text-zinc-600 transition-colors hover:text-zinc-900 active:text-zinc-900 sm:mb-6 dark:text-zinc-400 dark:hover:text-zinc-100 dark:active:text-zinc-100"
                    preserveScroll
                >
                    <ArrowLeft className="h-4 w-4" />
                    Voltar ao Changelog
                </Link>

                {/* Header */}
                <Card className="mb-6 overflow-hidden sm:mb-8">
                    <CardHeader className="p-4 sm:p-6">
                        <div className="flex flex-wrap items-center gap-2 sm:gap-3">
                            <h1 className="text-2xl font-bold sm:text-4xl">
                                {entry.formatted_version}
                            </h1>
                            {entry.is_latest && (
                                <span className="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 sm:px-3 sm:text-sm dark:bg-purple-900 dark:text-purple-300">
                                    Mais Recente
                                </span>
                            )}
                        </div>
                        {entry.formatted_date && (
                            <div className="mt-2 flex flex-wrap items-center gap-2 text-sm text-muted-foreground sm:text-base">
                                <div className="flex items-center gap-1.5 sm:gap-2">
                                    <Calendar className="h-4 w-4 sm:h-5 sm:w-5" />
                                    <span className="text-base sm:text-lg">
                                        {entry.formatted_date}
                                    </span>
                                </div>
                                {entry.human_date && (
                                    <span className="text-xs sm:text-sm">
                                        ({entry.human_date})
                                    </span>
                                )}
                            </div>
                        )}
                        <p className="mt-3 text-base text-muted-foreground sm:mt-4 sm:text-lg">
                            {entry.total_changes} mudanças em{' '}
                            {Object.keys(entry.sections).length} categorias
                        </p>
                    </CardHeader>
                </Card>

                {/* Sections */}
                <div className="space-y-4 sm:space-y-6">
                    {Object.entries(entry.sections).map(
                        ([sectionName, items]) => (
                            <Card key={sectionName} className="overflow-hidden">
                                <CardHeader className="p-4 sm:p-6">
                                    <div
                                        className={`inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold sm:gap-2 sm:px-3 sm:text-sm ${getSectionColor(sectionName)}`}
                                    >
                                        {getSectionIcon(sectionName)}
                                        {sectionName}
                                        <span className="ml-1.5 rounded-full bg-white/60 px-1.5 py-0.5 text-xs sm:ml-2 sm:px-2 dark:bg-black/20">
                                            {items.length}
                                        </span>
                                    </div>
                                </CardHeader>
                                <CardContent className="p-4 pt-0 sm:p-6 sm:pt-0">
                                    <ul className="space-y-2.5 sm:space-y-3">
                                        {items.map((item, idx) => (
                                            <li
                                                key={idx}
                                                className="flex items-start gap-2.5 sm:gap-3"
                                            >
                                                <span className="mt-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-zinc-400 sm:mt-2 sm:h-2 sm:w-2 dark:bg-zinc-500" />
                                                <span className="flex-1 text-sm break-words sm:text-base">
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
                <div className="mt-6 flex justify-center sm:mt-8">
                    <Link
                        href={ChangelogController.index().url}
                        className="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-50 active:bg-zinc-100 sm:px-6 sm:py-3 sm:text-base dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700 dark:active:bg-zinc-600"
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
