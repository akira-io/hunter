import { useMemo } from 'react';

/**
 * Custom hook to sanitize image URLs and prevent XSS attacks
 * Only allows safe protocols: blob:, https:, http:, data: (for base64 images)
 */
export function useSanitizeImageUrl(url: string | undefined | null): string {
    return useMemo(() => {
        if (!url) return '';

        try {
            const parsedUrl = new URL(url);

            // Allow safe protocols
            const allowedProtocols = ['blob:', 'https:', 'http:', 'data:'];

            if (allowedProtocols.includes(parsedUrl.protocol)) {
                // For data: URLs, only allow safe image types (block SVG)
                if (parsedUrl.protocol === 'data:') {
                    // Example: data:image/jpeg;base64,...
                    // Accept both "data:image/png;base64,..." and "data:image/png,..."
                    const mimeMatch = url.match(/^data:([^;,]+)[;,]/);
                    if (!mimeMatch) {
                        return '';
                    }
                    const mimeType = mimeMatch[1].toLowerCase();
                    const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/apng'];
                    // Block SVG images and anything not in allowed list
                    if (!allowedMimeTypes.includes(mimeType)) {
                        return '';
                    }
                }
                // For blob: URLs, preview is limited and controlled by input file filtering
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
