import { useMemo } from 'react';

/**
 * Custom hook to sanitize image URLs and prevent XSS attacks
 * Only allows safe protocols: blob:, https:, http:, data: (for base64 images)
 */
export function useSanitizeImageUrl(url: string | undefined | null): string {
    return useMemo(() => {
        if (!url) return '';

        try {
            const parsedUrl = new URL(url, window.location.origin);

            // Allow safe protocols
            const allowedProtocols = ['blob:', 'https:', 'http:', 'data:'];

            if (allowedProtocols.includes(parsedUrl.protocol)) {
                // Additional check for data: URLs
                if (parsedUrl.protocol === 'data:') {
                    // Only allow data URLs that start with image MIME types
                    // e.g. data:image/png;base64,...
                    if (/^data:image\/(?:png|jpeg|jpg|gif|webp|bmp);base64,/.test(url)) {
                        return url;
                    } else {
                        return '';
                    }
                }
                // For blob: URLs, optionally do extra validation if needed, but usually safe if only generated from file input
                // https: and http: are generally safe for images
                return url;
            }

            return '';
        } catch {
            // If URL parsing fails, return empty string for safety
            return '';
        }
    }, [url]);
}

/**
 * Hook to sanitize an array of image URLs
 */
export function useSanitizeImageUrls(urls: string[]): string[] {
    return useMemo(() => {
        return urls
            .map((url) => {
                try {
                    const parsedUrl = new URL(url);
                    const allowedProtocols = ['blob:', 'https:', 'http:', 'data:'];

                    if (allowedProtocols.includes(parsedUrl.protocol)) {
                        return url;
                    }

                    return '';
                } catch {
                    return '';
                }
            })
            .filter(Boolean); // Remove empty strings
    }, [urls]);
}

/**
 * Hook to sanitize external URLs and prevent XSS/open redirect attacks
 * Only allows safe protocols: https:, http:, mailto:, tel:
 */
export function useSanitizeExternalUrl(url: string | undefined | null): string {
    return useMemo(() => {
        if (!url) return '';

        try {
            const parsedUrl = new URL(url);

            // Allow safe protocols for external links
            const allowedProtocols = ['https:', 'http:', 'mailto:', 'tel:'];

            if (allowedProtocols.includes(parsedUrl.protocol)) {
                return url;
            }

            return '';
        } catch {
            // If URL parsing fails, return empty string for safety
            return '';
        }
    }, [url]);
}
