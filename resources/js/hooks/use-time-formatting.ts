import { useCallback } from 'react';

export interface TimeFormattingOptions {
    showSeconds?: boolean;
    use24Hour?: boolean;
    locale?: string;
}

export interface UseTimeFormattingReturn {
    formatRelativeTime: (dateString: string | undefined) => string;

    formatTime: (dateString: string) => string;

    formatFullDate: (dateString: string) => string;

    getRelativeTimeInWords: (dateString: string) => string;
}

export const useTimeFormatting = (options: TimeFormattingOptions = {}): UseTimeFormattingReturn => {
    const { showSeconds = false, use24Hour = false, locale } = options;

    const formatRelativeTime = useCallback(
        (dateString: string | undefined): string => {
            if (!dateString) return '';

            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now.getTime() - date.getTime());
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays === 0) {
                // Today - show time
                return date.toLocaleTimeString(locale, {
                    hour: '2-digit',
                    minute: '2-digit',
                    ...(showSeconds && { second: '2-digit' }),
                    hour12: !use24Hour,
                });
            }

            if (diffDays === 1) {
                return 'Yesterday';
            }

            if (diffDays < 7) {
                // This week - show day name
                return date.toLocaleDateString(locale, { weekday: 'short' });
            }

            // Older - show date
            return date.toLocaleDateString(locale, {
                month: 'short',
                day: 'numeric',
            });
        },
        [showSeconds, use24Hour, locale],
    );

    const formatTime = useCallback(
        (dateString: string): string => {
            const date = new Date(dateString);
            return date.toLocaleTimeString(locale, {
                hour: '2-digit',
                minute: '2-digit',
                ...(showSeconds && { second: '2-digit' }),
                hour12: !use24Hour,
            });
        },
        [showSeconds, use24Hour, locale],
    );

    const formatFullDate = useCallback(
        (dateString: string): string => {
            const date = new Date(dateString);
            return date.toLocaleDateString(locale, {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },
        [locale],
    );

    const getRelativeTimeInWords = useCallback((dateString: string): string => {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = now.getTime() - date.getTime();
        const diffSeconds = Math.floor(diffTime / 1000);
        const diffMinutes = Math.floor(diffSeconds / 60);
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffSeconds < 60) {
            return 'agora mesmo';
        }

        if (diffMinutes < 60) {
            return diffMinutes === 1 ? 'há 1 minuto' : `há ${diffMinutes} minutos`;
        }

        if (diffHours < 24) {
            return diffHours === 1 ? 'há 1 hora' : `há ${diffHours} horas`;
        }

        if (diffDays < 7) {
            return diffDays === 1 ? 'há 1 dia' : `há ${diffDays} dias`;
        }

        if (diffDays < 30) {
            const weeks = Math.floor(diffDays / 7);
            return weeks === 1 ? 'há 1 semana' : `há ${weeks} semanas`;
        }

        if (diffDays < 365) {
            const months = Math.floor(diffDays / 30);
            return months === 1 ? 'há 1 mês' : `há ${months} meses`;
        }

        const years = Math.floor(diffDays / 365);
        return years === 1 ? 'há 1 ano' : `há ${years} anos`;
    }, []);

    return {
        formatRelativeTime,
        formatTime,
        formatFullDate,
        getRelativeTimeInWords,
    };
};
