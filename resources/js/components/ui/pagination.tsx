import { PaginationInfo } from '@/types';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import React from 'react';

interface PaginationProps {
    pagination: PaginationInfo;
    baseUrl: string;
    showInfo?: boolean;
    preserveState?: boolean;
    preserveScroll?: boolean;
    queryParams?: Record<string, string | number>;
}

export const Pagination: React.FC<PaginationProps> = ({
    pagination,
    baseUrl,
    showInfo = true,
    preserveState = true,
    preserveScroll = true,
    queryParams = {},
}) => {
    if (pagination.total_pages <= 1) {
        return null;
    }

    const getPageNumbers = () => {
        const maxVisible = 5;
        const { current_page, total_pages } = pagination;

        if (total_pages <= maxVisible) {
            return Array.from({ length: total_pages }, (_, i) => i + 1);
        }

        if (current_page <= 3) {
            return Array.from({ length: maxVisible }, (_, i) => i + 1);
        }

        if (current_page >= total_pages - 2) {
            return Array.from({ length: maxVisible }, (_, i) => total_pages - maxVisible + 1 + i);
        }

        return Array.from({ length: maxVisible }, (_, i) => current_page - 2 + i);
    };

    const pageNumbers = getPageNumbers();

    const buildUrl = (page: number) => {
        const params = new URLSearchParams({ page: page.toString(), ...queryParams });
        return `${baseUrl}?${params.toString()}`;
    };

    return (
        <div className="flex items-center justify-between">
            {showInfo && (
                <div className="text-sm text-zinc-500">
                    Mostrando {((pagination.current_page - 1) * pagination.per_page) + 1} - {Math.min(pagination.current_page * pagination.per_page, pagination.total)} de {pagination.total} resultados
                </div>
            )}

            <div className="flex items-center gap-2">
                {/* Previous Button */}
                <Link
                    href={buildUrl(pagination.current_page - 1)}
                    preserveState={preserveState}
                    preserveScroll={preserveScroll}
                    className={`${
                        !pagination.has_prev_page
                            ? 'pointer-events-none opacity-50'
                            : 'hover:bg-zinc-100 dark:hover:bg-zinc-800'
                    } inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 transition-colors`}
                >
                    <ChevronLeft size={16} />
                    Anterior
                </Link>

                {/* Page Numbers */}
                <div className="flex items-center gap-1">
                    {pageNumbers.map((pageNum) => {
                        const isActive = pageNum === pagination.current_page;

                        return (
                            <Link
                                key={pageNum}
                                href={buildUrl(pageNum)}
                                preserveState={preserveState}
                                preserveScroll={preserveScroll}
                                className={`${
                                    isActive
                                        ? 'bg-blue-500 text-white'
                                        : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800'
                                } inline-flex h-8 w-8 items-center justify-center rounded-md text-sm font-medium transition-colors`}
                            >
                                {pageNum}
                            </Link>
                        );
                    })}
                </div>

                {/* Next Button */}
                <Link
                    href={buildUrl(pagination.current_page + 1)}
                    preserveState={preserveState}
                    preserveScroll={preserveScroll}
                    className={`${
                        !pagination.has_next_page
                            ? 'pointer-events-none opacity-50'
                            : 'hover:bg-zinc-100 dark:hover:bg-zinc-800'
                    } inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 transition-colors`}
                >
                    Próxima
                    <ChevronRight size={16} />
                </Link>
            </div>
        </div>
    );
};