import CodeEditor from '@uiw/react-textarea-code-editor';
import ReactMarkdown from 'react-markdown';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';

interface MarkdownRendererProps {
    content: string;
    className?: string;
}

export function MarkdownRenderer({ content, className = '' }: MarkdownRendererProps) {
    return (
        <div className={`prose prose-sm dark:prose-invert max-w-none ${className}`}>
            <ReactMarkdown
                remarkPlugins={[remarkGfm]}
                rehypePlugins={[rehypeSanitize]}
                components={{
                    // Customize heading styles
                    h1: ({ node, ...props }) => <h1 className="text-foreground mt-6 mb-4 text-2xl font-bold" {...props} />,
                    h2: ({ node, ...props }) => <h2 className="text-foreground mt-5 mb-3 text-xl font-bold" {...props} />,
                    h3: ({ node, ...props }) => <h3 className="text-foreground mt-4 mb-2 text-lg font-semibold" {...props} />,

                    // Customize paragraph spacing
                    p: ({ node, ...props }) => <p className="text-foreground mb-4 leading-relaxed" {...props} />,

                    // Customize links
                    a: ({ node, href, ...props }) => (
                        <a
                            href={href}
                            className="text-primary transition-colors hover:underline"
                            target="_blank"
                            rel="noopener noreferrer"
                            {...props}
                        />
                    ),

                    // Customize code blocks
                    code: ({ node, inline, className, children, ...props }) => {
                        if (inline) {
                            return <code className="bg-muted text-foreground rounded px-1.5 py-0.5 font-mono text-sm" {...props}>{children}</code>;
                        }

                        // Extract language from className (format: language-xxx)
                        const match = /language-(\w+)/.exec(className || '');
                        const language = match ? match[1] : '';
                        const codeString = String(children).replace(/\n$/, '');

                        return (
                            <div className="my-4 overflow-x-auto rounded-lg border border-border">
                                <CodeEditor
                                    value={codeString}
                                    language={language}
                                    readOnly
                                    padding={15}
                                    style={{
                                        fontSize: 14,
                                        fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace',
                                        backgroundColor: 'var(--color-muted)',
                                        color: 'var(--color-foreground)',
                                        pointerEvents: 'none',
                                        whiteSpace: 'nowrap',
                                        wordWrap: 'normal',
                                        wordBreak: 'normal',
                                    }}
                                />
                            </div>
                        );
                    },

                    // Customize lists
                    ul: ({ node, ...props }) => <ul className="text-foreground mb-4 list-inside list-disc space-y-1" {...props} />,
                    ol: ({ node, ...props }) => <ol className="text-foreground mb-4 list-inside list-decimal space-y-1" {...props} />,

                    // Customize blockquotes
                    blockquote: ({ node, ...props }) => (
                        <blockquote className="border-border text-muted-foreground my-4 border-l-4 pl-4 italic" {...props} />
                    ),

                    // Customize images
                    img: ({ node, src, alt, ...props }) => <img src={src} alt={alt} className="my-4 h-auto max-w-full rounded-lg" {...props} />,

                    // Customize horizontal rules
                    hr: ({ node, ...props }) => <hr className="border-border my-6" {...props} />,

                    // Customize tables
                    table: ({ node, ...props }) => (
                        <div className="my-4 overflow-x-auto">
                            <table className="border-border min-w-full border-collapse border" {...props} />
                        </div>
                    ),
                    thead: ({ node, ...props }) => <thead className="bg-muted" {...props} />,
                    th: ({ node, ...props }) => <th className="border-border text-foreground border px-4 py-2 text-left font-semibold" {...props} />,
                    td: ({ node, ...props }) => <td className="border-border text-foreground border px-4 py-2" {...props} />,
                }}
            >
                {content}
            </ReactMarkdown>
        </div>
    );
}
