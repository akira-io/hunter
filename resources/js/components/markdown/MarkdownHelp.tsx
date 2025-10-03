import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { HelpCircle } from 'lucide-react';

interface MarkdownHelpProps {
    onInsert: (text: string, formatType?: 'wrap' | 'prefix') => void;
}

export function MarkdownHelp({ onInsert }: MarkdownHelpProps) {
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
            { text: '- Item 1', value: '- ', type: 'prefix' as const },
            { text: '- Item 2', value: '- ', type: 'prefix' as const },
            { text: '1. Item numerado', value: '1. ', type: 'prefix' as const },
        ],
        links: [
            { text: '[texto do link](https://exemplo.com)', value: '[texto](url)', type: 'wrap' as const },
            { text: '`código inline`', value: '`código`', type: 'wrap' as const },
            { text: '```código em bloco```', value: '```\ncódigo\n```', type: 'wrap' as const },
        ],
        quotes: [{ text: '> Citação', value: '> ', type: 'prefix' as const }],
    };

    const handleInsert = (value: string, type?: 'wrap' | 'prefix') => {
        onInsert(value, type);
    };

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button type="button" variant="ghost" size="sm" className="text-muted-foreground">
                    <HelpCircle size={16} />
                    <span className="hidden md:inline">Ajuda</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-84 mr-4">
                <div className="space-y-3">
                    <h4 className="text-foreground text-sm font-semibold">Guia Rápido de Markdown</h4>
                    <p className="text-muted-foreground text-xs">Clique em um exemplo para inseri-lo no editor</p>

                    <div className="space-y-4 text-xs">
                        <div>
                            <p className="text-foreground mb-1 font-medium">Títulos</p>
                            {examples.headings.map((example, i) => (
                                <code
                                    key={i}
                                    onClick={() => handleInsert(example.value, example.type)}
                                    className="bg-muted text-foreground hover:bg-muted/80 mt-1 block cursor-pointer rounded p-1 transition-colors"
                                >
                                    {example.text}
                                </code>
                            ))}
                        </div>

                        <div>
                            <p className="text-foreground mb-1 font-medium">Formatação de texto</p>
                            {examples.formatting.map((example, i) => (
                                <code
                                    key={i}
                                    onClick={() => handleInsert(example.value, example.type)}
                                    className="bg-muted text-foreground hover:bg-muted/80 mt-1 block cursor-pointer rounded p-1 transition-colors"
                                >
                                    {example.text}
                                </code>
                            ))}
                        </div>

                        <div>
                            <p className="text-foreground mb-1 font-medium">Listas</p>
                            {examples.lists.map((example, i) => (
                                <code
                                    key={i}
                                    onClick={() => handleInsert(example.value, example.type)}
                                    className="bg-muted text-foreground hover:bg-muted/80 mt-1 block cursor-pointer rounded p-1 transition-colors"
                                >
                                    {example.text}
                                </code>
                            ))}
                        </div>

                        <div>
                            <p className="text-foreground mb-1 font-medium">Links e código</p>
                            {examples.links.map((example, i) => (
                                <code
                                    key={i}
                                    onClick={() => handleInsert(example.value, example.type)}
                                    className="bg-muted text-foreground hover:bg-muted/80 mt-1 block cursor-pointer rounded p-1 transition-colors"
                                >
                                    {example.text}
                                </code>
                            ))}
                        </div>

                        <div>
                            <p className="text-foreground mb-1 font-medium">Citações</p>
                            {examples.quotes.map((example, i) => (
                                <code
                                    key={i}
                                    onClick={() => handleInsert(example.value, example.type)}
                                    className="bg-muted text-foreground hover:bg-muted/80 mt-1 block cursor-pointer rounded p-1 transition-colors"
                                >
                                    {example.text}
                                </code>
                            ))}
                        </div>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}
