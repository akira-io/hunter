import React, { ChangeEvent, useRef, useState } from 'react';

interface UseMarkdownEditorOptions {
    value: string;
    onChange: (e: ChangeEvent<HTMLTextAreaElement>) => void;
    name?: string;
}

export function useMarkdownEditor({ value, onChange, name }: UseMarkdownEditorOptions) {
    const editorRef = useRef<HTMLTextAreaElement>(null);
    const [activeTab, setActiveTab] = useState('edit');

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

    const handlePaste = (e: React.ClipboardEvent | ClipboardEvent) => {
        const clipboardData = 'clipboardData' in e ? e.clipboardData : (e as ClipboardEvent).clipboardData;
        if (!clipboardData) return;

        const pastedText = clipboardData.getData('text');

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
    };

    const insertEmoji = (emoji: string) => {
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

    return {
        editorRef,
        activeTab,
        setActiveTab,
        handlePaste,
        insertText,
        insertEmoji,
    };
}
