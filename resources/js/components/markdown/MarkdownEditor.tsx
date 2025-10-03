import { EmojiPicker } from '@/components/markdown/EmojiPicker';
import { MarkdownHelp } from '@/components/markdown/MarkdownHelp';
import { MarkdownRenderer } from '@/components/markdown/MarkdownRenderer';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useMarkdownEditor } from '@/hooks/useMarkdownEditor';
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
}

export function MarkdownEditor({ value, onChange, maxLength = 500, placeholder = 'Escreva algo interessante…', rows = 3, name }: MarkdownEditorProps) {
    const { editorRef, activeTab, setActiveTab, handlePaste, insertText, insertEmoji } = useMarkdownEditor({ value, onChange, name });

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
                        <EmojiPicker onInsert={insertEmoji} />
                        <MarkdownHelp onInsert={insertText} />
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
