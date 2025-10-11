/* eslint-disable @typescript-eslint/no-unused-vars */
import CodeEditor from '@uiw/react-textarea-code-editor';
import ReactMarkdown from 'react-markdown';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';

interface MarkdownRendererProps {
    content: string;
    className?: string;
}

export function MarkdownRenderer({
    content,
    className = '',
}: MarkdownRendererProps) {
    return (
        <div
            className={`prose prose-sm dark:prose-invert max-w-none ${className}`}
        >
            <ReactMarkdown
                remarkPlugins={[remarkGfm]}
                rehypePlugins={[rehypeSanitize]}
                components={{
                    h1: ({ node, ...props }) => (
                        <h1
                            className="mt-6 mb-4 text-2xl font-bold text-foreground"
                            {...props}
                        />
                    ),
                    h2: ({ node, ...props }) => (
                        <h2
                            className="mt-5 mb-3 text-xl font-bold text-foreground"
                            {...props}
                        />
                    ),
                    h3: ({ node, ...props }) => (
                        <h3
                            className="mt-4 mb-2 text-lg font-semibold text-foreground"
                            {...props}
                        />
                    ),
                    h4: ({ node, ...props }) => (
                        <h4
                            className="mt-3 mb-2 text-base font-semibold text-foreground"
                            {...props}
                        />
                    ),
                    h5: ({ node, ...props }) => (
                        <h5
                            className="mt-2 mb-1 text-sm font-semibold text-foreground"
                            {...props}
                        />
                    ),
                    h6: ({ node, ...props }) => (
                        <h6
                            className="mt-2 mb-1 text-xs font-semibold text-foreground"
                            {...props}
                        />
                    ),

                    p: ({ node, ...props }) => (
                        <p
                            className="mb-4 leading-relaxed text-foreground"
                            {...props}
                        />
                    ),

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
                                <code
                                    className="rounded bg-muted px-1.5 py-0.5 font-mono text-sm text-foreground"
                                    {...props}
                                >
                                    {children}
                                </code>
                            );
                        }

                        const match = /language-(\w+)/.exec(className || '');
                        const language = match ? match[1] : '';
                        const codeString = String(children).replace(/\n$/, '');

                        return (
                            <div className="my-4 overflow-hidden rounded-lg border border-border">
                                <CodeEditor
                                    value={codeString}
                                    language={language}
                                    readOnly
                                    padding={15}
                                    style={{
                                        fontSize: 14,
                                        fontFamily:
                                            'ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace',
                                        backgroundColor: 'var(--color-muted)',
                                        color: 'var(--color-foreground)',
                                        pointerEvents: 'none',
                                    }}
                                />
                            </div>
                        );
                    },

                    ul: ({ node, ...props }) => (
                        <ul
                            className="mb-4 list-inside list-disc space-y-1 text-foreground"
                            {...props}
                        />
                    ),
                    ol: ({ node, ...props }) => (
                        <ol
                            className="mb-4 list-inside list-decimal space-y-1 text-foreground"
                            {...props}
                        />
                    ),
                    li: ({ node, children, ...props }) => {
                        const content = String(children);
                        if (
                            content.includes('[ ]') ||
                            content.includes('[x]')
                        ) {
                            const isChecked = content.includes('[x]');
                            const text = content.replace(/\[([ x])\]\s*/, '');
                            return (
                                <li
                                    className="flex items-start gap-2"
                                    {...props}
                                >
                                    <input
                                        type="checkbox"
                                        checked={isChecked}
                                        readOnly
                                        className="mt-1"
                                    />
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
                        <blockquote
                            className="my-4 border-l-4 border-border pl-4 text-muted-foreground italic"
                            {...props}
                        />
                    ),

                    img: ({ node, src, alt, ...props }) => (
                        <img
                            src={src}
                            alt={alt}
                            className="my-4 h-auto max-w-full rounded-lg border border-border"
                            {...props}
                        />
                    ),

                    hr: ({ node, ...props }) => (
                        <hr className="my-6 border-border" {...props} />
                    ),

                    table: ({ node, ...props }) => (
                        <div className="my-4 overflow-x-auto rounded-lg border border-border">
                            <table
                                className="min-w-full border-collapse"
                                {...props}
                            />
                        </div>
                    ),
                    thead: ({ node, ...props }) => (
                        <thead className="bg-muted" {...props} />
                    ),
                    tbody: ({ node, ...props }) => (
                        <tbody className="divide-y divide-border" {...props} />
                    ),
                    tr: ({ node, ...props }) => (
                        <tr
                            className="transition-colors hover:bg-muted/50"
                            {...props}
                        />
                    ),
                    th: ({ node, ...props }) => (
                        <th
                            className="border-r border-border px-4 py-2 text-left font-semibold text-foreground last:border-r-0"
                            {...props}
                        />
                    ),
                    td: ({ node, ...props }) => (
                        <td
                            className="border-r border-border px-4 py-2 text-foreground last:border-r-0"
                            {...props}
                        />
                    ),

                    strong: ({ node, ...props }) => (
                        <strong
                            className="font-bold text-foreground"
                            {...props}
                        />
                    ),
                    em: ({ node, ...props }) => (
                        <em className="text-foreground italic" {...props} />
                    ),
                    del: ({ node, ...props }) => (
                        <del
                            className="text-muted-foreground line-through"
                            {...props}
                        />
                    ),
                }}
            >
                {content}
            </ReactMarkdown>
        </div>
    );
}
