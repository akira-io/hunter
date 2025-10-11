import { Fragment, JSX } from 'react';

export function highlightText(text: string, query: string): JSX.Element {
    if (!query || !text) {
        return <>{text}</>;
    }

    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    const regex = new RegExp(`(${escapedQuery})`, 'gi');

    const parts = text.split(regex);

    return (
        <>
            {parts.map((part, index) => {
                const isMatch = regex.test(part);
                regex.lastIndex = 0; // Reset regex index for next test

                return isMatch ? (
                    <mark
                        key={index}
                        className="rounded bg-yellow-200 px-0.5 text-yellow-900 dark:bg-yellow-500/30 dark:text-yellow-200"
                    >
                        {part}
                    </mark>
                ) : (
                    <Fragment key={index}>{part}</Fragment>
                );
            })}
        </>
    );
}

export function highlightMultiple(
    text: string,
    queries: string[],
): JSX.Element {
    if (!queries.length || !text) {
        return <>{text}</>;
    }

    const escapedQueries = queries
        .filter((q) => q.trim().length > 0)
        .map((q) => q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));

    if (escapedQueries.length === 0) {
        return <>{text}</>;
    }

    const pattern = escapedQueries.join('|');
    const regex = new RegExp(`(${pattern})`, 'gi');

    const parts = text.split(regex);

    return (
        <>
            {parts.map((part, index) => {
                // Check if this part matches any query (case-insensitive)
                const isMatch = regex.test(part);
                regex.lastIndex = 0; // Reset regex index for next test

                return isMatch ? (
                    <mark
                        key={index}
                        className="rounded bg-yellow-200 px-0.5 text-yellow-900 dark:bg-yellow-500/30 dark:text-yellow-200"
                    >
                        {part}
                    </mark>
                ) : (
                    <Fragment key={index}>{part}</Fragment>
                );
            })}
        </>
    );
}
