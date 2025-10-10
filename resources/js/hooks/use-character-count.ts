import { CHARACTER_WARNING_THRESHOLDS } from '@/constants/validation';
import { useMemo } from 'react';

interface UseCharacterCountOptions {
    content: string;
    maxLength: number;
    trim?: boolean;
}

interface UseCharacterCountReturn {

    count: number;

    max: number;

    progressPercentage: number;

    isNearLimit: boolean;

    isOverLimit: boolean;

    hasContent: boolean;

    canSubmit: boolean;

    remaining: number;
}

export function useCharacterCount({
    content,
    maxLength,
    trim = true,
}: UseCharacterCountOptions): UseCharacterCountReturn {
    return useMemo(() => {
        const processedContent = trim ? content.trim() : content;
        const count = processedContent.length;
        const progressPercentage = (count / maxLength) * 100;
        const isNearLimit = count > maxLength * CHARACTER_WARNING_THRESHOLDS.NEAR_LIMIT;
        const isOverLimit = count > maxLength;
        const hasContent = count > 0;
        const canSubmit = hasContent && !isOverLimit;
        const remaining = maxLength - count;

        return {
            count,
            max: maxLength,
            progressPercentage,
            isNearLimit,
            isOverLimit,
            hasContent,
            canSubmit,
            remaining,
        };
    }, [content, maxLength, trim]);
}
