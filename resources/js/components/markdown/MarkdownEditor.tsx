import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import CodeEditor from '@uiw/react-textarea-code-editor';
import { Eye, HelpCircle, Pencil, Smile } from 'lucide-react';
import { ChangeEvent, useEffect, useRef, useState } from 'react';

interface MarkdownEditorProps {
    value: string;
    onChange: (e: ChangeEvent<HTMLTextAreaElement>) => void;
    maxLength?: number;
    placeholder?: string;
    rows?: number;
    name?: string;
}

export function MarkdownEditor({
    value,
    onChange,
    maxLength = 500,
    placeholder = 'Escreva algo interessante…',
    rows = 3,
    name,
}: MarkdownEditorProps) {
    const editorRef = useRef<HTMLTextAreaElement>(null);
    const [open, setOpen] = useState(false);
    const [emojiDialogOpen, setEmojiDialogOpen] = useState(false);
    const [activeTab, setActiveTab] = useState('edit');

    // Cleanup event listener on unmount
    useEffect(() => {
        return () => {
            if (editorRef.current) {
                editorRef.current.removeEventListener('paste', handlePaste as any);
            }
        };
    }, []);

    const formatIndentation = (code: string): string => {
        const lines = code.split('\n');

        // Find minimum indentation (excluding empty lines)
        let minIndent = Infinity;
        lines.forEach((line) => {
            if (line.trim().length > 0) {
                const indent = line.match(/^\s*/)?.[0].length || 0;
                minIndent = Math.min(minIndent, indent);
            }
        });

        // Remove minimum indentation from all lines
        if (minIndent > 0 && minIndent !== Infinity) {
            return lines
                .map((line) => (line.trim().length > 0 ? line.substring(minIndent) : line))
                .join('\n')
                .trim();
        }

        return code.trim();
    };

    const detectCodeLanguage = (code: string): string => {
        const trimmed = code.trim();

        // Detect PHP (highest priority for PHP tags)
        if (trimmed.includes('<?php') || trimmed.includes('<?=') || trimmed.includes('namespace ') || trimmed.includes('use ')) {
            return 'php';
        }

        // Detect JSX/TSX (React components)
        if (
            trimmed.includes('<') &&
            trimmed.includes('>') &&
            (trimmed.includes('className') || trimmed.includes('onClick') || trimmed.includes('useState') || trimmed.includes('useEffect'))
        ) {
            return 'jsx';
        }

        // Detect HTML
        if (
            trimmed.includes('<!DOCTYPE') ||
            trimmed.includes('<html') ||
            (trimmed.includes('<') && trimmed.includes('</') && !trimmed.includes('function'))
        ) {
            return 'html';
        }

        // Detect TypeScript (interface, type, etc)
        if (trimmed.includes('interface ') || trimmed.includes('type ') || trimmed.includes(': string') || trimmed.includes(': number')) {
            return 'typescript';
        }

        // Detect Python
        if (trimmed.includes('def ') || trimmed.includes('import ') || trimmed.includes('from ') || trimmed.includes('print(')) {
            return 'python';
        }

        // Detect JSON
        if ((trimmed.startsWith('{') || trimmed.startsWith('[')) && trimmed.includes(':') && trimmed.includes('"')) {
            try {
                JSON.parse(trimmed);
                return 'json';
            } catch {
                // Not valid JSON, continue
            }
        }

        // Detect CSS/SCSS
        if (trimmed.match(/[.#][\w-]+\s*\{/) || (trimmed.includes('{') && trimmed.includes('}') && trimmed.includes(':') && trimmed.includes(';'))) {
            return 'css';
        }

        // Detect SQL
        if (trimmed.match(/\b(SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP)\b/i)) {
            return 'sql';
        }

        // Detect Bash/Shell
        if (trimmed.startsWith('#!') || trimmed.includes('#!/bin/bash') || trimmed.match(/\b(echo|cd|ls|mkdir|rm)\b/)) {
            return 'bash';
        }

        // Detect JavaScript (fallback for JS-like syntax)
        if (
            trimmed.includes('function') ||
            trimmed.includes('const ') ||
            trimmed.includes('let ') ||
            trimmed.includes('var ') ||
            trimmed.includes('=>') ||
            trimmed.includes('console.log')
        ) {
            return 'javascript';
        }

        return '';
    };

    const handlePaste = (e: React.ClipboardEvent) => {
        const pastedText = e.clipboardData.getData('text');

        // Detect if pasted text looks like code (contains < > and multiple lines or special characters)
        const looksLikeCode =
            (pastedText.includes('<') && pastedText.includes('>')) ||
            pastedText.split('\n').length > 3 ||
            (pastedText.includes('{') && pastedText.includes('}') && pastedText.includes(';'));

        if (looksLikeCode && !pastedText.startsWith('```')) {
            e.preventDefault();

            const formattedText = formatIndentation(pastedText);
            const language = detectCodeLanguage(formattedText);
            const formattedCode = language ? `\`\`\`${language}\n${formattedText}\n\`\`\`` : `\`\`\`\n${formattedText}\n\`\`\``;

            // Try to get textarea from ref or from the event target
            const textarea = editorRef.current || (e.target as HTMLTextAreaElement);
            if (!textarea) return;

            const start = textarea.selectionStart || 0;
            const end = textarea.selectionEnd || 0;
            const newValue = value.substring(0, start) + formattedCode + value.substring(end);

            const event = {
                target: {
                    name: name || '',
                    value: newValue,
                },
            } as ChangeEvent<HTMLTextAreaElement>;

            onChange(event);

            setTimeout(() => {
                const finalTextarea = editorRef.current || textarea;
                finalTextarea.focus();
                const newPosition = start + formattedCode.length;
                finalTextarea.setSelectionRange(newPosition, newPosition);
            }, 0);
        }
    };

    const insertText = (template: string, formatType?: 'wrap' | 'prefix') => {
        if (!editorRef.current) return;

        const textarea = editorRef.current;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = value.substring(start, end);

        let newText = '';
        let cursorOffset = 0;

        if (selectedText && formatType === 'wrap') {
            // Wrap selected text (for bold, italic, strikethrough, links, code)
            if (template.includes('**texto**')) {
                newText = `**${selectedText}**`;
                cursorOffset = newText.length;
            } else if (template.includes('*texto*')) {
                newText = `*${selectedText}*`;
                cursorOffset = newText.length;
            } else if (template.includes('~~texto~~')) {
                newText = `~~${selectedText}~~`;
                cursorOffset = newText.length;
            } else if (template.includes('[texto](url)')) {
                newText = `[${selectedText}](url)`;
                cursorOffset = newText.length - 4; // Position cursor at "url"
            } else if (template.includes('`código`')) {
                newText = `\`${selectedText}\``;
                cursorOffset = newText.length;
            } else if (template.includes('```\ncódigo\n```')) {
                newText = `\`\`\`\n${selectedText}\n\`\`\``;
                cursorOffset = newText.length;
            } else {
                newText = template;
                cursorOffset = newText.length;
            }
        } else if (selectedText && formatType === 'prefix') {
            // Add prefix to each line (for headings, lists, quotes)
            const lines = selectedText.split('\n');
            const prefix = template; // Keep the space from template
            newText = lines.map((line) => `${prefix}${line}`).join('\n');
            cursorOffset = newText.length;
        } else {
            // No selection, just insert template
            newText = template;
            cursorOffset = template.length;
        }

        const newValue = value.substring(0, start) + newText + value.substring(end);

        // Create a synthetic event
        const event = {
            target: {
                name: name || '',
                value: newValue,
            },
        } as ChangeEvent<HTMLTextAreaElement>;

        onChange(event);

        // Set cursor position after the inserted text
        setTimeout(() => {
            textarea.focus();
            const newPosition = start + cursorOffset;
            textarea.setSelectionRange(newPosition, newPosition);
        }, 0);

        setOpen(false);
    };

    const handleEmojiInsert = (emoji: string) => {
        if (!editorRef.current) return;

        const textarea = editorRef.current;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        const newValue = value.substring(0, start) + emoji + value.substring(end);

        const event = {
            target: {
                name: name || '',
                value: newValue,
            },
        } as ChangeEvent<HTMLTextAreaElement>;

        onChange(event);

        setTimeout(() => {
            textarea.focus();
            const newPosition = start + emoji.length;
            textarea.setSelectionRange(newPosition, newPosition);
        }, 0);
    };

    return (
        <div className="w-full">
            <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                <div className="mb-2 flex items-center justify-between gap-2">
                    <TabsList>
                        <TabsTrigger value="edit">
                            <Pencil size={16} />
                            <span className="ml-1">Editar</span>
                        </TabsTrigger>
                        <TabsTrigger value="preview">
                            <Eye size={16} />
                            <span className="ml-1">Preview</span>
                        </TabsTrigger>
                    </TabsList>
                    <div className="flex items-center gap-2">
                        <EmojiPicker open={emojiDialogOpen} onOpenChange={setEmojiDialogOpen} onInsert={handleEmojiInsert} />
                        <MarkdownHelp open={open} onOpenChange={setOpen} onInsert={insertText} />
                    </div>
                </div>

                <TabsContent value="edit">
                    <div
                        className="border-border overflow-x-auto overflow-y-auto rounded-md border"
                        onPaste={handlePaste}
                        ref={(node) => {
                            if (node) {
                                const textarea = node.querySelector('textarea');
                                if (textarea) {
                                    editorRef.current = textarea;
                                    // Add paste event listener directly to textarea
                                    textarea.addEventListener('paste', handlePaste as any);
                                }
                            }
                        }}
                    >
                        <CodeEditor
                            value={value}
                            language="markdown"
                            placeholder={placeholder}
                            onChange={onChange}
                            padding={15}
                            data-color-mode="dark"
                            style={{
                                fontSize: 14,
                                fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace',
                                minHeight: `${rows * 1.5}rem`,
                                maxHeight: '400px',
                                backgroundColor: 'var(--color-muted)',
                                color: 'var(--color-foreground)',
                            }}
                        />
                    </div>
                </TabsContent>
                <TabsContent value="preview">
                    <div className="border-border bg-muted min-h-[200px] rounded-md border p-4">
                        <MarkdownRenderer content={value || '*Nada para visualizar ainda...*'} />
                    </div>
                </TabsContent>
                <div className="text-right text-sm text-gray-500">
                    {value.length}/{maxLength}
                </div>
            </Tabs>
        </div>
    );
}

interface MarkdownHelpProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onInsert: (text: string, formatType?: 'wrap' | 'prefix') => void;
}

function MarkdownHelp({ open, onOpenChange, onInsert }: MarkdownHelpProps) {
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
        <Popover open={open} onOpenChange={onOpenChange}>
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

interface EmojiPickerProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onInsert: (emoji: string) => void;
}

function EmojiPicker({ open, onOpenChange, onInsert }: EmojiPickerProps) {
    const [search, setSearch] = useState('');

    const emojis = [
        { emoji: '😀', name: 'sorriso feliz' },
        { emoji: '😃', name: 'sorriso grande' },
        { emoji: '😄', name: 'sorriso olhos' },
        { emoji: '😁', name: 'sorriso dentes' },
        { emoji: '😆', name: 'rindo' },
        { emoji: '😅', name: 'suor' },
        { emoji: '🤣', name: 'gargalhada' },
        { emoji: '😂', name: 'chorando rindo' },
        { emoji: '🙂', name: 'leve sorriso' },
        { emoji: '🙃', name: 'invertido' },
        { emoji: '😉', name: 'piscada' },
        { emoji: '😊', name: 'feliz' },
        { emoji: '😇', name: 'anjo' },
        { emoji: '🥰', name: 'apaixonado' },
        { emoji: '😍', name: 'amor' },
        { emoji: '🤩', name: 'estrelas' },
        { emoji: '😘', name: 'beijo' },
        { emoji: '😗', name: 'beijinho' },
        { emoji: '😚', name: 'beijo olhos' },
        { emoji: '😙', name: 'beijo sorriso' },
        { emoji: '😋', name: 'delicia' },
        { emoji: '😛', name: 'lingua' },
        { emoji: '😜', name: 'lingua piscada' },
        { emoji: '🤪', name: 'louco' },
        { emoji: '😝', name: 'lingua olhos' },
        { emoji: '🤑', name: 'dinheiro' },
        { emoji: '🤗', name: 'abraco' },
        { emoji: '🤭', name: 'riso' },
        { emoji: '🤫', name: 'silencio' },
        { emoji: '🤔', name: 'pensando' },
        { emoji: '🤐', name: 'boca fechada' },
        { emoji: '🤨', name: 'sobrancelha' },
        { emoji: '😐', name: 'neutro' },
        { emoji: '😑', name: 'sem expressao' },
        { emoji: '😶', name: 'sem boca' },
        { emoji: '😏', name: 'maroto' },
        { emoji: '😒', name: 'entediado' },
        { emoji: '🙄', name: 'revirando olhos' },
        { emoji: '😬', name: 'careta' },
        { emoji: '🤥', name: 'mentira' },
        { emoji: '😌', name: 'aliviado' },
        { emoji: '😔', name: 'pensativo' },
        { emoji: '😪', name: 'sonolento' },
        { emoji: '🤤', name: 'babando' },
        { emoji: '😴', name: 'dormindo' },
        { emoji: '😷', name: 'mascara' },
        { emoji: '🤒', name: 'termometro' },
        { emoji: '🤕', name: 'machucado' },
        { emoji: '🤢', name: 'enjoado' },
        { emoji: '🤮', name: 'vomitando' },
        { emoji: '🤧', name: 'espirrando' },
        { emoji: '🥵', name: 'calor' },
        { emoji: '🥶', name: 'frio' },
        { emoji: '🥴', name: 'tonto' },
        { emoji: '😵', name: 'zonzo' },
        { emoji: '🤯', name: 'mente explodindo' },
        { emoji: '🤠', name: 'cowboy' },
        { emoji: '🥳', name: 'festa' },
        { emoji: '😎', name: 'legal' },
        { emoji: '🤓', name: 'nerd' },
        { emoji: '🧐', name: 'monoculo' },
        { emoji: '😕', name: 'confuso' },
        { emoji: '😟', name: 'preocupado' },
        { emoji: '🙁', name: 'triste leve' },
        { emoji: '☹️', name: 'muito triste' },
        { emoji: '😮', name: 'surpreso' },
        { emoji: '😯', name: 'boca aberta' },
        { emoji: '😲', name: 'chocado' },
        { emoji: '😳', name: 'corado' },
        { emoji: '🥺', name: 'implorando' },
        { emoji: '😦', name: 'boca aberta triste' },
        { emoji: '😧', name: 'angustiado' },
        { emoji: '😨', name: 'com medo' },
        { emoji: '😰', name: 'ansioso suor' },
        { emoji: '😥', name: 'triste suor' },
        { emoji: '😢', name: 'chorando' },
        { emoji: '😭', name: 'chorando muito' },
        { emoji: '😱', name: 'gritando medo' },
        { emoji: '😖', name: 'confuso triste' },
        { emoji: '😣', name: 'perseverante' },
        { emoji: '😞', name: 'desapontado' },
        { emoji: '😓', name: 'suor frio' },
        { emoji: '😩', name: 'cansado' },
        { emoji: '😫', name: 'exausto' },
        { emoji: '🥱', name: 'bocejando' },
        { emoji: '😤', name: 'fumegando' },
        { emoji: '😡', name: 'bravo' },
        { emoji: '😠', name: 'zangado' },
        { emoji: '🤬', name: 'xingando' },
        { emoji: '😈', name: 'diabo sorrindo' },
        { emoji: '👋', name: 'acenando' },
        { emoji: '🤚', name: 'mao levantada' },
        { emoji: '🖐', name: 'mao aberta' },
        { emoji: '✋', name: 'mao' },
        { emoji: '🖖', name: 'vulcano' },
        { emoji: '👌', name: 'ok' },
        { emoji: '🤌', name: 'dedos juntos' },
        { emoji: '🤏', name: 'pouquinho' },
        { emoji: '✌️', name: 'vitoria' },
        { emoji: '🤞', name: 'dedos cruzados' },
        { emoji: '🤟', name: 'amo voce' },
        { emoji: '🤘', name: 'rock' },
        { emoji: '🤙', name: 'me liga' },
        { emoji: '👈', name: 'apontando esquerda' },
        { emoji: '👉', name: 'apontando direita' },
        { emoji: '👆', name: 'apontando cima' },
        { emoji: '👇', name: 'apontando baixo' },
        { emoji: '☝️', name: 'dedo levantado' },
        { emoji: '👍', name: 'joinha' },
        { emoji: '👎', name: 'joinha baixo' },
        { emoji: '✊', name: 'punho' },
        { emoji: '👊', name: 'punho batendo' },
        { emoji: '🤛', name: 'punho esquerdo' },
        { emoji: '🤜', name: 'punho direito' },
        { emoji: '👏', name: 'aplaudindo' },
        { emoji: '🙌', name: 'maos levantadas' },
        { emoji: '👐', name: 'maos abertas' },
        { emoji: '🤲', name: 'palmas juntas' },
        { emoji: '🤝', name: 'aperto mao' },
        { emoji: '🙏', name: 'rezando' },
        { emoji: '✍️', name: 'escrevendo' },
        { emoji: '💅', name: 'unhas' },
        { emoji: '🤳', name: 'selfie' },
        { emoji: '💪', name: 'musculo' },
        { emoji: '❤️', name: 'coracao vermelho' },
        { emoji: '🧡', name: 'coracao laranja' },
        { emoji: '💛', name: 'coracao amarelo' },
        { emoji: '💚', name: 'coracao verde' },
        { emoji: '💙', name: 'coracao azul' },
        { emoji: '💜', name: 'coracao roxo' },
        { emoji: '🖤', name: 'coracao preto' },
        { emoji: '🤍', name: 'coracao branco' },
        { emoji: '🤎', name: 'coracao marrom' },
        { emoji: '💔', name: 'coracao partido' },
        { emoji: '❣️', name: 'coracao exclamacao' },
        { emoji: '💕', name: 'dois coracoes' },
        { emoji: '💞', name: 'coracoes girando' },
        { emoji: '💓', name: 'coracao batendo' },
        { emoji: '💗', name: 'coracao crescendo' },
        { emoji: '💖', name: 'coracao brilhante' },
        { emoji: '💘', name: 'coracao flecha' },
        { emoji: '💝', name: 'coracao fita' },
        { emoji: '🔥', name: 'fogo' },
        { emoji: '⭐', name: 'estrela' },
        { emoji: '🌟', name: 'estrela brilhante' },
        { emoji: '✨', name: 'brilhos' },
        { emoji: '💫', name: 'tontura' },
        { emoji: '⚡', name: 'raio' },
        { emoji: '💥', name: 'explosao' },
        { emoji: '🌈', name: 'arco iris' },
        { emoji: '☀️', name: 'sol' },
        { emoji: '🌙', name: 'lua' },
        { emoji: '🌊', name: 'onda' },
        { emoji: '💧', name: 'gota' },
    ];

    const filteredEmojis = search ? emojis.filter((e) => e.name.toLowerCase().includes(search.toLowerCase()) || e.emoji.includes(search)) : emojis;

    return (
        <Popover open={open} onOpenChange={onOpenChange}>
            <PopoverTrigger asChild>
                <Button type="button" variant="ghost" size="sm" className="text-muted-foreground -mr-5 md:mr-0">
                    <Smile size={16} />
                    <span className=" hidden md:inline">Emoji</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-84 mr-4">
                <div className="flex flex-col space-y-3">
                    <h4 className="text-foreground text-sm font-semibold">Escolha um Emoji</h4>
                    <input
                        type="text"
                        placeholder="Pesquisar emoji..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="bg-muted text-foreground placeholder:text-muted-foreground border-border focus:ring-ring w-full rounded-md border px-3 py-2 text-sm focus:ring-2 focus:outline-none"
                    />
                    <div className="max-h-[250px] overflow-y-auto">
                        <div className="grid grid-cols-10 gap-2">
                            {filteredEmojis.map((item, i) => (
                                <button
                                    key={i}
                                    type="button"
                                    onClick={() => {
                                        onInsert(item.emoji);
                                        onOpenChange(false);
                                        setSearch('');
                                    }}
                                    className="hover:bg-muted flex h-8 w-8 items-center justify-center rounded text-xl transition-colors"
                                    title={item.name}
                                >
                                    {item.emoji}
                                </button>
                            ))}
                        </div>
                        {filteredEmojis.length === 0 && <p className="text-muted-foreground py-8 text-center text-sm">Nenhum emoji encontrado</p>}
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}
