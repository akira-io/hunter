import { UserAvatar } from '@/components/UserAvatar';
import { Button } from '@/components/ui/button';
import { useDebounce } from '@/hooks/use-debounce';
import api from '@/lib/api';
import { highlightText } from '@/lib/highlight';
import { cn } from '@/lib/utils';
import { useAddSearch, useClearAllSearches, useRecentSearches, useRemoveSearch } from '@/stores/recentSearchesStore';
import { router } from '@inertiajs/react';
import { Command } from 'cmdk';
import { Clock, FileText, Loader, Search, Trash2, User as UserIcon, X } from 'lucide-react';
import { ComponentType, useEffect, useState } from 'react';

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

    const recentSearches = useRecentSearches();
    const addSearch = useAddSearch();
    const removeSearch = useRemoveSearch();
    const clearAllSearches = useClearAllSearches();

    const debouncedSearch = useDebounce(search, 300);

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
        if (debouncedSearch && debouncedSearch.trim().length >= 2) {
            addSearch(debouncedSearch);
        }
        setOpen(false);
        router.visit(url);
    };

    const handleRecentSearchClick = (query: string) => {
        setSearch(query);
    };

    const handleRemoveRecentSearch = (e: React.MouseEvent, query: string) => {
        e.stopPropagation();
        removeSearch(query);
    };

    const totalResults = groups.reduce((sum, group) => sum + group.results.length, 0);
    const showEmpty = debouncedSearch.length >= 2 && !loading && totalResults === 0;

    const getIconComponent = (iconName: string) => {
        const icons: Record<string, ComponentType<{ className?: string }>> = {
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
                <span className="hidden lg:inline-flex">Procurar...</span>
                <span className="inline-flex lg:hidden">Procurar...</span>
                <kbd className="bg-muted pointer-events-none absolute top-1.5 right-1.5 hidden h-5 items-center gap-1 rounded border px-1.5 font-mono text-[10px] font-medium opacity-100 select-none sm:flex">
                    <span className="text-xs">⌘</span>K
                </kbd>
            </Button>

            {/* Command Dialog */}
            <Command.Dialog open={open} onOpenChange={setOpen} label="Global Search" className="fixed inset-0 z-50">
                {/* Backdrop */}
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm" onClick={() => setOpen(false)} />

                {/* Command Palette */}
                <div className="bg-popover text-popover-foreground fixed top-[20%] left-1/2 w-[calc(100%-2rem)] max-w-2xl -translate-x-1/2 rounded-lg border shadow-2xl sm:w-full">
                    <Command.Input
                        value={search}
                        onValueChange={setSearch}
                        placeholder="Procurar hunters, projetos..."
                        className="placeholder:text-muted-foreground flex h-14 w-full rounded-t-lg border-b bg-transparent px-4 py-3 text-sm outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />

                    <Command.List className="max-h-[400px] overflow-y-auto p-2">
                        {/* Recent Searches - Show when no search query */}
                        {!search && recentSearches.length > 0 && (
                            <>
                                <div className="flex items-center justify-between px-3 py-2">
                                    <Command.Group
                                        heading="Pesquisas Recentes"
                                        className="[&_[cmdk-group-heading]]:text-muted-foreground [&_[cmdk-group-heading]]:text-xs [&_[cmdk-group-heading]]:font-medium"
                                    />
                                    <button
                                        onClick={clearAllSearches}
                                        className="text-muted-foreground hover:text-foreground flex items-center gap-1 text-xs transition-colors"
                                    >
                                        <Trash2 className="h-3 w-3" />
                                        Limpar tudo
                                    </button>
                                </div>
                                <Command.Group>
                                    {recentSearches.map((recentSearch) => (
                                        <Command.Item
                                            key={recentSearch.query}
                                            value={recentSearch.query}
                                            onSelect={() => handleRecentSearchClick(recentSearch.query)}
                                            className="aria-selected:bg-accent group flex cursor-pointer items-center gap-3 rounded-md px-3 py-2.5 text-sm"
                                        >
                                            <div className="bg-muted flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full">
                                                <Clock className="text-muted-foreground h-4 w-4" />
                                            </div>
                                            <div className="flex-1 overflow-hidden">
                                                <div className="line-clamp-1">{recentSearch.query}</div>
                                            </div>
                                            <button
                                                onClick={(e) => handleRemoveRecentSearch(e, recentSearch.query)}
                                                className="text-muted-foreground hover:text-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                            >
                                                <X className="h-4 w-4" />
                                            </button>
                                        </Command.Item>
                                    ))}
                                </Command.Group>
                            </>
                        )}

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
                                                <div className="line-clamp-1 font-medium">{highlightText(result.title, debouncedSearch)}</div>
                                                {result.subtitle && (
                                                    <div className="text-muted-foreground text-xs">
                                                        {highlightText(result.subtitle, debouncedSearch)}
                                                    </div>
                                                )}
                                                {result.description && (
                                                    <div className="text-muted-foreground line-clamp-2 text-xs">
                                                        {highlightText(result.description, debouncedSearch)}
                                                    </div>
                                                )}
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
