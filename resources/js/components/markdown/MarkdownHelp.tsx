import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { HelpCircle } from 'lucide-react';
import { useState } from 'react';

interface MarkdownHelpProps {
    onInsert: (text: string, formatType?: 'wrap' | 'prefix') => void;
}

export function MarkdownHelp({ onInsert }: MarkdownHelpProps) {
    const [open, setOpen] = useState(false);

    const examples = {
        headings: [
            { text: '# Título 1', value: '# ', type: 'prefix' as const },
            { text: '## Título 2', value: '## ', type: 'prefix' as const },
            { text: '### Título 3', value: '### ', type: 'prefix' as const },
        ],
        formatting: [
            { text: '**negrito**', value: '**texto**', type: 'wrap' as const },
            { text: '*itálico*', value: '*texto*', type: 'wrap' as const },
            { text: '~~riscado~~', value: '~~texto~~', type: 'wrap' as const },
        ],
        lists: [
            { text: '- Item não ordenado', value: '- ', type: 'prefix' as const },
            { text: '1. Item numerado', value: '1. ', type: 'prefix' as const },
            { text: '- [ ] Tarefa', value: '- [ ] ', type: 'prefix' as const },
        ],
        links: [
            { text: '[texto do link](url)', value: '[texto](url)', type: 'wrap' as const },
            { text: '`código inline`', value: '`código`', type: 'wrap' as const },
            { text: '```\ncódigo bloco\n```', value: '```\ncódigo\n```', type: 'wrap' as const },
        ],
        quotes: [
            { text: '> Citação', value: '> ', type: 'prefix' as const },
            { text: '---', value: '---\n', type: 'prefix' as const },
        ],
    };

    const handleInsert = (value: string, type?: 'wrap' | 'prefix') => {
        onInsert(value, type);
        setOpen(false);
    };

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button type="button" variant="ghost" size="sm" className="text-muted-foreground hover:text-foreground">
                    <HelpCircle className="h-4 w-4" />
                    <span className="ml-1 hidden sm:inline">Ajuda</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-[320px] sm:w-[380px]" align="end" sideOffset={8}>
                <div className="space-y-4">
                    <div>
                        <h4 className="text-foreground text-sm font-semibold">Guia Rápido de Markdown</h4>
                        <p className="text-muted-foreground mt-1 text-xs">Clique em um exemplo para inseri-lo no editor</p>
                    </div>

                    <div className="max-h-[60vh] space-y-4 overflow-y-auto pr-1 text-xs">
                        <div>
                            <p className="text-foreground mb-2 font-medium">Títulos</p>
                            <div className="space-y-1">
                                {examples.headings.map((example, i) => (
                                    <code
                                        key={i}
                                        onClick={() => handleInsert(example.value, example.type)}
                                        className="bg-muted text-foreground hover:bg-accent hover:text-accent-foreground block cursor-pointer rounded-md px-2 py-1.5 transition-all"
                                    >
                                        {example.text}
                                    </code>
                                ))}
                            </div>
                        </div>

                        <div>
                            <p className="text-foreground mb-2 font-medium">Formatação de texto</p>
                            <div className="space-y-1">
                                {examples.formatting.map((example, i) => (
                                    <code
                                        key={i}
                                        onClick={() => handleInsert(example.value, example.type)}
                                        className="bg-muted text-foreground hover:bg-accent hover:text-accent-foreground block cursor-pointer rounded-md px-2 py-1.5 transition-all"
                                    >
                                        {example.text}
                                    </code>
                                ))}
                            </div>
                        </div>

                        <div>
                            <p className="text-foreground mb-2 font-medium">Listas</p>
                            <div className="space-y-1">
                                {examples.lists.map((example, i) => (
                                    <code
                                        key={i}
                                        onClick={() => handleInsert(example.value, example.type)}
                                        className="bg-muted text-foreground hover:bg-accent hover:text-accent-foreground block cursor-pointer rounded-md px-2 py-1.5 transition-all"
                                    >
                                        {example.text}
                                    </code>
                                ))}
                            </div>
                        </div>

                        <div>
                            <p className="text-foreground mb-2 font-medium">Links e código</p>
                            <div className="space-y-1">
                                {examples.links.map((example, i) => (
                                    <code
                                        key={i}
                                        onClick={() => handleInsert(example.value, example.type)}
                                        className="bg-muted text-foreground hover:bg-accent hover:text-accent-foreground block cursor-pointer rounded-md px-2 py-1.5 transition-all"
                                    >
                                        {example.text}
                                    </code>
                                ))}
                            </div>
                        </div>

                        <div>
                            <p className="text-foreground mb-2 font-medium">Citações e separadores</p>
                            <div className="space-y-1">
                                {examples.quotes.map((example, i) => (
                                    <code
                                        key={i}
                                        onClick={() => handleInsert(example.value, example.type)}
                                        className="bg-muted text-foreground hover:bg-accent hover:text-accent-foreground block cursor-pointer rounded-md px-2 py-1.5 transition-all"
                                    >
                                        {example.text}
                                    </code>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}
