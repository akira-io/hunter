import { ArrowUpRightIcon, CircleFadingPlusIcon, FileInputIcon, FolderPlusIcon, SearchIcon } from 'lucide-react';
import * as React from 'react';

import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
    CommandShortcut,
} from '@/components/ui/command';
import { home } from '@/routes';
import hunts from '@/routes/hunts';
import profile from '@/routes/profile';
import { Link } from '@inertiajs/react';

export default function SearchHunt() {
    const [open, setOpen] = React.useState(false);

    React.useEffect(() => {
        const down = (e: KeyboardEvent) => {
            if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                setOpen((open) => !open);
            }
        };

        document.addEventListener('keydown', down);
        return () => document.removeEventListener('keydown', down);
    }, []);

    return (
        <>
            {/* Desktop search bar */}
            <button
                className="border-input bg-background text-foreground placeholder:text-muted-foreground/70 focus-visible:border-ring focus-visible:ring-ring/50 hover:bg-accent/50 hidden w-90 max-w-md rounded-lg border px-3 py-2 text-sm shadow-sm transition-[color,box-shadow] outline-none focus-visible:ring-[3px] md:mr-2 md:inline-flex"
                onClick={() => setOpen(true)}
            >
                <span className="flex grow items-center">
                    <SearchIcon className="text-muted-foreground/80 mr-3" size={16} aria-hidden="true" />
                    <span className="text-muted-foreground/70 font-normal">Pesquisar...</span>
                </span>
                <kbd className="bg-muted text-muted-foreground ml-auto inline-flex h-5 max-h-full items-center rounded border px-1.5 font-[inherit] text-[0.625rem] font-medium">
                    ⌘K
                </kbd>
            </button>
            {/* Mobile search icon */}
            <button
                className="text-muted-foreground/80 hover:text-foreground hover:bg-accent/50 rounded-lg p-2 transition-colors md:hidden"
                onClick={() => setOpen(true)}
                aria-label="Pesquisar"
            >
                <SearchIcon size={20} aria-hidden="true" />
            </button>
            <CommandDialog open={open} onOpenChange={setOpen}>
                <CommandInput placeholder="Type a command or search..." />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup heading="Quick start">
                        <CommandItem>
                            <FolderPlusIcon size={16} className="opacity-60" aria-hidden="true" />
                            <span>Adicionar Story</span>
                            <CommandShortcut className="justify-center">⌘N</CommandShortcut>
                        </CommandItem>
                        <CommandItem>
                            <FileInputIcon size={16} className="opacity-60" aria-hidden="true" />
                            <span>Novo Post</span>
                            <CommandShortcut className="justify-center">⌘I</CommandShortcut>
                        </CommandItem>
                        <CommandItem>
                            <CircleFadingPlusIcon size={16} className="opacity-60" aria-hidden="true" />
                            <Link href={profile.about()}>Perfil</Link>
                            <CommandShortcut className="justify-center">⌘B</CommandShortcut>
                        </CommandItem>
                    </CommandGroup>
                    <CommandSeparator />
                    <CommandGroup heading="Navigation">
                        <CommandItem>
                            <ArrowUpRightIcon size={16} className="opacity-60" aria-hidden="true" />
                            <Link href={home()}>Pagina Inicial</Link>
                        </CommandItem>
                        <CommandItem>
                            <ArrowUpRightIcon size={16} className="opacity-60" aria-hidden="true" />
                            <Link href={hunts.index()}>Feed</Link>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </CommandDialog>
        </>
    );
}
