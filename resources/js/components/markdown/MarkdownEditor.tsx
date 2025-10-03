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

export function MarkdownEditor({
    value,
    onChange,
    maxLength = 500,
    placeholder = 'Escreva algo interessante…',
    rows = 3,
    name,
}: MarkdownEditorProps) {
    const { editorRef, activeTab, setActiveTab, handlePaste, insertText, insertEmoji } = useMarkdownEditor({ value, onChange, name });

    const isNearLimit = value.length > maxLength * 0.9;
    const isOverLimit = value.length > maxLength;

    return (
        <div className="w-full">
            <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                <div className="mb-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <TabsList className="w-full sm:w-auto">
                        <TabsTrigger value="edit" className="flex-1 sm:flex-initial">
                            <Pencil className="h-4 w-4" />
                            <span className="ml-1.5">Editar</span>
                        </TabsTrigger>
                        <TabsTrigger value="preview" className="flex-1 sm:flex-initial">
                            <Eye className="h-4 w-4" />
                            <span className="ml-1.5">Preview</span>
                        </TabsTrigger>
                    </TabsList>
                    <div className="flex items-center justify-end gap-1 sm:gap-2">
                        <EmojiPicker onInsert={insertEmoji} />
                        <MarkdownHelp onInsert={insertText} />
                    </div>
                </div>

                <TabsContent value="edit" className="mt-0">
                    <div
                        className="border-border bg-muted focus-within:border-primary focus-within:ring-primary/20 overflow-hidden rounded-lg border transition-colors focus-within:ring-2"
                        onPaste={handlePaste}
                        ref={(node) => {
                            if (node) {
                                const textarea = node.querySelector('textarea');
                                if (textarea) {
                                    editorRef.current = textarea;
                                    textarea.addEventListener('paste', handlePaste as never);
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
                                resize: 'vertical',
                            }}
                        />
                    </div>
                </TabsContent>
                <TabsContent value="preview" className="mt-0">
                    <div className="border-border bg-muted min-h-[200px] rounded-lg border p-4 transition-colors">
                        <MarkdownRenderer content={value || '*Nada para visualizar ainda...*'} />
                    </div>
                </TabsContent>
                <div className="mt-2 flex items-center justify-between text-xs sm:text-sm">
                    <span className="text-muted-foreground">
                        Suporte para <span className="font-medium">Markdown</span>
                    </span>
                    <span
                        className={`font-medium transition-colors ${
                            isOverLimit ? 'text-destructive' : isNearLimit ? 'text-primary' : 'text-muted-foreground'
                        }`}
                    >
                        {value.length}/{maxLength}
                    </span>
                </div>
            </Tabs>
        </div>
    );
}
