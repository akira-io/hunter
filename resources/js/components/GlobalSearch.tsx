import { UserAvatar } from '@/components/UserAvatar';
import { Button } from '@/components/ui/button';
import { useDebounce } from '@/hooks/useDebounce';
import api from '@/lib/api';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { Command } from 'cmdk';
import { FileText, Loader, Search, User as UserIcon } from 'lucide-react';
import { useEffect, useState } from 'react';

interface SearchResult {
    id: string;
    title: string;
    subtitle: string | null;
    description: string | null;
    image: string | null;
    url: string;
    metadata: Record<string, unknown>;
}

interface SearchGroup {
    type: string;
    label: string;
    icon: string;
    results: SearchResult[];
    priority: number;
}

interface SearchResponse {
    groups: SearchGroup[];
}

export function GlobalSearch() {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');
    const [groups, setGroups] = useState<SearchGroup[]>([]);
    const [loading, setLoading] = useState(false);

    const debouncedSearch = useDebounce(search, 300);

    // Keyboard shortcut (Cmd+K / Ctrl+K)
    useEffect(() => {
        const down = (e: KeyboardEvent) => {
            if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                setOpen((open) => !open);
            }
        };

        document.addEventListener('keydown', down);
        return () => document.removeEventListener('keydown', down);
    }, []);

    // Search when debounced value changes
    useEffect(() => {
        if (!debouncedSearch || debouncedSearch.length < 2) {
            setGroups([]);
            setLoading(false);
            return;
        }

        setLoading(true);

        api.get<SearchResponse>('/search', {
            params: { q: debouncedSearch },
        })
            .then((response) => {
                setGroups(response.data.groups);
                setLoading(false);
            })
            .catch((error) => {
                console.error('Search error:', error);
                setLoading(false);
            });
    }, [debouncedSearch]);

    // Reset on close
    useEffect(() => {
        if (!open) {
            setSearch('');
            setGroups([]);
        }
    }, [open]);

    const handleSelectResult = (url: string) => {
        setOpen(false);
        router.visit(url);
    };

    const totalResults = groups.reduce((sum, group) => sum + group.results.length, 0);
    const showEmpty = debouncedSearch.length >= 2 && !loading && totalResults === 0;

    // Icon mapping
    const getIconComponent = (iconName: string) => {
        const icons: Record<string, unknown> = {
            user: UserIcon,
            'file-text': FileText,
        };
        return icons[iconName] || FileText;
    };

    return (
        <>
            {/* Trigger Button */}
            <Button
                variant="outline"
                className={cn('text-muted-foreground relative w-full justify-start text-sm sm:pr-12 md:w-40 lg:w-64')}
                onClick={() => setOpen(true)}
            >
                <Search className="mr-2 h-4 w-4" />
                <span className="hidden lg:inline-flex">Buscar...</span>
                <span className="inline-flex lg:hidden">Buscar...</span>
                <kbd className="bg-muted pointer-events-none absolute top-1.5 right-1.5 hidden h-5 items-center gap-1 rounded border px-1.5 font-mono text-[10px] font-medium opacity-100 select-none sm:flex">
                    <span className="text-xs">⌘</span>K
                </kbd>
            </Button>

            {/* Command Dialog */}
            <Command.Dialog open={open} onOpenChange={setOpen} label="Global Search" className="fixed inset-0 z-50">
                {/* Backdrop */}
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm" onClick={() => setOpen(false)} />

                {/* Command Palette */}
                <div className="bg-popover text-popover-foreground fixed top-[20%] left-1/2 w-full max-w-2xl -translate-x-1/2 rounded-lg border shadow-2xl">
                    <Command.Input
                        value={search}
                        onValueChange={setSearch}
                        placeholder="Buscar hunters, projetos..."
                        className="placeholder:text-muted-foreground flex h-14 w-full rounded-t-lg border-b bg-transparent px-4 py-3 text-sm outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />

                    <Command.List className="max-h-[400px] overflow-y-auto p-2">
                        {/* Loading State */}
                        {loading && (
                            <div className="text-muted-foreground flex items-center justify-center py-6 text-sm">
                                <Loader className="mr-2 h-4 w-4 animate-spin" />A procurar...
                            </div>
                        )}

                        {/* Empty State */}
                        {showEmpty && (
                            <Command.Empty className="text-muted-foreground py-6 text-center text-sm">Nenhum resultado encontrado.</Command.Empty>
                        )}

                        {/* Dynamic Groups */}
                        {groups.map((group) => {
                            const IconComponent = getIconComponent(group.icon);

                            return (
                                <Command.Group key={group.type} heading={group.label} className="mb-2">
                                    {group.results.map((result) => (
                                        <Command.Item
                                            key={`${group.type}-${result.id}`}
                                            value={`${group.type}-${result.id}-${result.title}-${result.subtitle || ''}`}
                                            onSelect={() => handleSelectResult(result.url)}
                                            className="aria-selected:bg-accent flex cursor-pointer items-center gap-3 rounded-md px-3 py-2.5 text-sm"
                                        >
                                            {result.image ? (
                                                <UserAvatar avatarUrl={result.image} userName={result.title} className="h-8 w-8" />
                                            ) : (
                                                <div className="bg-muted flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full">
                                                    <IconComponent className="text-muted-foreground h-4 w-4" />
                                                </div>
                                            )}
                                            <div className="flex-1 overflow-hidden">
                                                <div className="line-clamp-1 font-medium">{result.title}</div>
                                                {result.subtitle && <div className="text-muted-foreground text-xs">{result.subtitle}</div>}
                                                {result.description && <div className="text-muted-foreground text-xs">{result.description}</div>}
                                            </div>
                                            <IconComponent className="text-muted-foreground h-4 w-4 flex-shrink-0" />
                                        </Command.Item>
                                    ))}
                                </Command.Group>
                            );
                        })}
                    </Command.List>

                    {/* Footer with keyboard hints */}
                    <div className="text-muted-foreground border-t px-4 py-2 text-xs">
                        <div className="flex items-center gap-4">
                            <span className="flex items-center gap-1">
                                <kbd className="bg-muted rounded border px-1.5 py-0.5">↑↓</kbd> Navegar
                            </span>
                            <span className="flex items-center gap-1">
                                <kbd className="bg-muted rounded border px-1.5 py-0.5">⏎</kbd> Selecionar
                            </span>
                            <span className="flex items-center gap-1">
                                <kbd className="bg-muted rounded border px-1.5 py-0.5">Esc</kbd> Fechar
                            </span>
                        </div>
                    </div>
                </div>
            </Command.Dialog>
        </>
    );
}
