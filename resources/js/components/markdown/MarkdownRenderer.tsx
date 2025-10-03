/* eslint-disable @typescript-eslint/no-unused-vars */
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
                    h1: ({ node, ...props }) => <h1 className="text-foreground mt-6 mb-4 text-2xl font-bold" {...props} />,
                    h2: ({ node, ...props }) => <h2 className="text-foreground mt-5 mb-3 text-xl font-bold" {...props} />,
                    h3: ({ node, ...props }) => <h3 className="text-foreground mt-4 mb-2 text-lg font-semibold" {...props} />,
                    h4: ({ node, ...props }) => <h4 className="text-foreground mt-3 mb-2 text-base font-semibold" {...props} />,
                    h5: ({ node, ...props }) => <h5 className="text-foreground mt-2 mb-1 text-sm font-semibold" {...props} />,
                    h6: ({ node, ...props }) => <h6 className="text-foreground mt-2 mb-1 text-xs font-semibold" {...props} />,

                    p: ({ node, ...props }) => <p className="text-foreground mb-4 leading-relaxed" {...props} />,

                    a: ({ node, href, ...props }) => (
                        <a
                            href={href}
                            className="text-primary underline-offset-4 transition-colors hover:underline"
                            target="_blank"
                            rel="noopener noreferrer"
                            {...props}
                        />
                    ),

                    code: ({ node, className, children, ...props }) => {
                        const isInline = !className;

                        if (isInline) {
                            return (
                                <code className="bg-muted text-foreground rounded px-1.5 py-0.5 font-mono text-sm" {...props}>
                                    {children}
                                </code>
                            );
                        }

                        const match = /language-(\w+)/.exec(className || '');
                        const language = match ? match[1] : '';
                        const codeString = String(children).replace(/\n$/, '');

                        return (
                            <div className="border-border my-4 overflow-hidden rounded-lg border">
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
                                    }}
                                />
                            </div>
                        );
                    },

                    ul: ({ node, ...props }) => <ul className="text-foreground mb-4 list-inside list-disc space-y-1" {...props} />,
                    ol: ({ node, ...props }) => <ol className="text-foreground mb-4 list-inside list-decimal space-y-1" {...props} />,
                    li: ({ node, children, ...props }) => {
                        const content = String(children);
                        if (content.includes('[ ]') || content.includes('[x]')) {
                            const isChecked = content.includes('[x]');
                            const text = content.replace(/\[([ x])\]\s*/, '');
                            return (
                                <li className="flex items-start gap-2" {...props}>
                                    <input type="checkbox" checked={isChecked} readOnly className="mt-1" />
                                    <span>{text}</span>
                                </li>
                            );
                        }
                        return (
                            <li className="text-foreground" {...props}>
                                {children}
                            </li>
                        );
                    },

                    blockquote: ({ node, ...props }) => (
                        <blockquote className="border-border text-muted-foreground my-4 border-l-4 pl-4 italic" {...props} />
                    ),

                    img: ({ node, src, alt, ...props }) => (
                        <img src={src} alt={alt} className="border-border my-4 h-auto max-w-full rounded-lg border" {...props} />
                    ),

                    hr: ({ node, ...props }) => <hr className="border-border my-6" {...props} />,

                    table: ({ node, ...props }) => (
                        <div className="border-border my-4 overflow-x-auto rounded-lg border">
                            <table className="min-w-full border-collapse" {...props} />
                        </div>
                    ),
                    thead: ({ node, ...props }) => <thead className="bg-muted" {...props} />,
                    tbody: ({ node, ...props }) => <tbody className="divide-border divide-y" {...props} />,
                    tr: ({ node, ...props }) => <tr className="hover:bg-muted/50 transition-colors" {...props} />,
                    th: ({ node, ...props }) => (
                        <th className="border-border text-foreground border-r px-4 py-2 text-left font-semibold last:border-r-0" {...props} />
                    ),
                    td: ({ node, ...props }) => <td className="border-border text-foreground border-r px-4 py-2 last:border-r-0" {...props} />,

                    strong: ({ node, ...props }) => <strong className="text-foreground font-bold" {...props} />,
                    em: ({ node, ...props }) => <em className="text-foreground italic" {...props} />,
                    del: ({ node, ...props }) => <del className="text-muted-foreground line-through" {...props} />,
                }}
            >
                {content}
            </ReactMarkdown>
        </div>
    );
}
