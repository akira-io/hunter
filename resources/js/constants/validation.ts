/**
 * Validation Constants
 * Shared validation limits and rules across the application
 * These values should match backend validation rules
 */

/**
 * Character limits for content fields
 */
export const CONTENT_LIMITS = {
    /** Hunt content max length (matches CreateHuntRequest max:500) */
    HUNT: 500,
    /** Comment content max length (matches StoreCommentRequest max:200) */
    COMMENT: 200,
    /** Message content max length */
    MESSAGE: 1000,
    /** Bio/Apresentação max length (profile about section) */
    BIO: 200,
    /** Project description max length */
    PROJECT_DESCRIPTION: 1000,
} as const;

/**
 * Warning thresholds for character counters
 */
export const CHARACTER_WARNING_THRESHOLDS = {
    /** Show warning when 90% of limit is reached */
    NEAR_LIMIT: 0.9,
    /** Show error when limit is exceeded */
    OVER_LIMIT: 1.0,
} as const;

/**
 * Image upload limits
 */
export const IMAGE_LIMITS = {
    /** Max file size in KB (2MB) */
    MAX_SIZE_KB: 2048,
    /** Allowed image formats */
    ALLOWED_FORMATS: ['image/jpeg', 'image/jpg', 'image/png'] as const,
} as const;
