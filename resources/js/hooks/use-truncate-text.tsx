import { useMemo } from 'react';

export function useTruncate() {
    const truncate = useMemo(() => {
        return (text: string, maxLength = 50) => {
            return text.length > maxLength
                ? text.substring(0, maxLength) + '...'
                : text;
        };
    }, []);
    return { truncate };
}
