import { EmojiPicker } from '@/components/markdown/EmojiPicker';
import { MarkdownHelp } from '@/components/markdown/MarkdownHelp';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { CharacterCounter } from '@/components/ui/character-counter';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useCharacterCount } from '@/hooks/use-character-count';
import { useMarkdownEditor } from '@/hooks/use-markdown-editor';
import CodeEditor from '@uiw/react-textarea-code-editor';
import { Eye, Pencil } from 'lucide-react';
import { ChangeEvent } from 'react';

interface MarkdownEditorProps {
    value: string;
    onChange: (e: ChangeEvent<HTMLTextAreaElement>) => void;
    maxLength?: number;
    placeholder?: string;
    rows?: number;
    name?: string;
    /** Hide the built-in character counter (useful when using external counter) */
    hideCounter?: boolean;
    renderMobileControls?: (controls: {
        emojiPicker: React.ReactNode;
        markdownHelp: React.ReactNode;
        editButton: React.ReactNode;
        previewButton: React.ReactNode;
    }) => React.ReactNode;
}

export function MarkdownEditor({
    value,
    onChange,
    maxLength = 500,
    placeholder = 'Escreva algo interessante…',
    rows = 3,
    name,
    hideCounter = false,
    renderMobileControls,
}: MarkdownEditorProps) {
    const {
        editorRef,
        activeTab,
        setActiveTab,
        handlePaste,
        insertText,
        insertEmoji,
    } = useMarkdownEditor({ value, onChange, name });

    // Use shared character count logic (trim: false for markdown to preserve formatting)
    const characterCount = useCharacterCount({
        content: value,
        maxLength,
        trim: false,
    });

    const mobileControls = {
        emojiPicker: <EmojiPicker onInsert={insertEmoji} />,
        markdownHelp: <MarkdownHelp onInsert={insertText} />,
        editButton: (
            <button
                type="button"
                onClick={() => setActiveTab('edit')}
                className={`rounded-lg p-2 transition-colors hover:bg-muted active:bg-muted/80 ${activeTab === 'edit' ? 'bg-muted text-primary' : 'text-muted-foreground'}`}
                aria-label="Editar"
            >
                <Pencil className="h-5 w-5" />
            </button>
        ),
        previewButton: (
            <button
                type="button"
                onClick={() => setActiveTab('preview')}
                className={`rounded-lg p-2 transition-colors hover:bg-muted active:bg-muted/80 ${activeTab === 'preview' ? 'bg-muted text-primary' : 'text-muted-foreground'}`}
                aria-label="Preview"
            >
                <Eye className="h-5 w-5" />
            </button>
        ),
    };

    return (
        <div className="w-full">
            <Tabs
                value={activeTab}
                onValueChange={setActiveTab}
                className="w-full"
            >
                <div className="mb-2 hidden sm:flex sm:flex-row sm:items-center sm:justify-between">
                    <TabsList className="w-full sm:w-auto">
                        <TabsTrigger
                            value="edit"
                            className="flex-1 sm:flex-initial"
                        >
                            <Pencil className="h-4 w-4" />
                            <span className="ml-1.5">Editar</span>
                        </TabsTrigger>
                        <TabsTrigger
                            value="preview"
                            className="flex-1 sm:flex-initial"
                        >
                            <Eye className="h-4 w-4" />
                            <span className="ml-1.5">Preview</span>
                        </TabsTrigger>
                    </TabsList>
                    <div className="flex items-center justify-end gap-1 sm:gap-2">
                        <EmojiPicker onInsert={insertEmoji} />
                        <MarkdownHelp onInsert={insertText} />
                    </div>
                </div>
                {renderMobileControls && (
                    <div className="sm:hidden">
                        {renderMobileControls(mobileControls)}
                    </div>
                )}

                <TabsContent value="edit" className="mt-0">
                    <div
                        className="overflow-hidden rounded-lg border border-border bg-muted transition-colors focus-within:border-zinc-400 focus-within:ring-1 focus-within:ring-zinc-300 dark:focus-within:border-zinc-600 dark:focus-within:ring-zinc-700"
                        onPaste={handlePaste}
                        ref={(node) => {
                            if (node) {
                                const textarea = node.querySelector('textarea');
                                if (textarea) {
                                    editorRef.current = textarea;
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
                                fontSize: 16,
                                fontFamily:
                                    'ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace',
                                minHeight: `${rows * 1.5}rem`,
                                maxHeight: '400px',
                                overflowY: 'auto',
                                backgroundColor: 'var(--color-muted)',
                                color: 'var(--color-foreground)',
                                resize: 'vertical',
                            }}
                        />
                    </div>
                </TabsContent>
                <TabsContent value="preview" className="mt-0">
                    <div className="min-h-[200px] max-h-[400px] overflow-y-auto rounded-lg border border-border bg-muted p-4 transition-colors">
                        <MarkdownRenderer
                            content={value || '*Nada para visualizar ainda...*'}
                        />
                    </div>
                </TabsContent>
                {!hideCounter && (
                    <div className="mt-2 flex items-center justify-between text-xs sm:text-sm">
                        <span className="text-muted-foreground">
                            {/*Suporte para <span className="font-medium">Markdown</span>*/}
                        </span>
                        <CharacterCounter
                            count={characterCount.count}
                            max={characterCount.max}
                            isNearLimit={characterCount.isNearLimit}
                            isOverLimit={characterCount.isOverLimit}
                        />
                    </div>
                )}
            </Tabs>
        </div>
    );
}
