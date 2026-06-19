import type { ReactNode } from 'react';
import Link from 'next/link';

type PaginationProps = {
    currentPage: number;
    totalPages: number;
    getPageHref: (page: number) => string;
    ariaLabel: string;
    className?: string;
    maxVisiblePages?: number;
    previousControl?: ReactNode;
    nextControl?: ReactNode;
    firstControl?: ReactNode;
    lastControl?: ReactNode;
    previousLabel?: string;
    nextLabel?: string;
    firstLabel?: string;
    lastLabel?: string;
};

function getVisiblePages(currentPage: number, totalPages: number, maxVisiblePages: number) {
    if (totalPages <= maxVisiblePages) {
        return Array.from({ length: totalPages }, (_, index) => index + 1);
    }

    if (currentPage >= totalPages - (maxVisiblePages - 2)) {
        return Array.from({ length: maxVisiblePages }, (_, index) => totalPages - maxVisiblePages + 1 + index);
    }

    return Array.from({ length: maxVisiblePages }, (_, index) => currentPage + index);
}

export function Pagination({
    currentPage,
    totalPages,
    getPageHref,
    ariaLabel,
    className,
    maxVisiblePages = 5,
    previousControl = 'Назад',
    nextControl = 'Вперед',
    firstControl,
    lastControl,
    previousLabel = 'Предыдущая страница',
    nextLabel = 'Следующая страница',
    firstLabel = 'Первая страница',
    lastLabel = 'Последняя страница',
}: PaginationProps) {
    if (totalPages <= 1) {
        return null;
    }

    const visiblePages = getVisiblePages(currentPage, totalPages, maxVisiblePages);
    const paginationClassName = className ? `app-pagination ${className}` : 'app-pagination';

    return (
        <nav className={paginationClassName} aria-label={ariaLabel}>
            {currentPage > 1 ? (
                <>
                    {firstControl ? (
                        <Link href={getPageHref(1)} className="app-pagination__control" aria-label={firstLabel}>
                            {firstControl}
                        </Link>
                    ) : null}
                    <Link href={getPageHref(currentPage - 1)} className="app-pagination__control" aria-label={previousLabel}>
                        {previousControl}
                    </Link>
                </>
            ) : null}

            {visiblePages.map((pageNumber) => (
                <Link
                    key={pageNumber}
                    href={getPageHref(pageNumber)}
                    className={`app-pagination__page${pageNumber === currentPage ? ' is-active' : ''}`}
                    aria-current={pageNumber === currentPage ? 'page' : undefined}
                >
                    {pageNumber}
                </Link>
            ))}

            {currentPage < totalPages ? (
                <>
                    <Link href={getPageHref(currentPage + 1)} className="app-pagination__control" aria-label={nextLabel}>
                        {nextControl}
                    </Link>
                    {lastControl ? (
                        <Link href={getPageHref(totalPages)} className="app-pagination__control" aria-label={lastLabel}>
                            {lastControl}
                        </Link>
                    ) : null}
                </>
            ) : null}
        </nav>
    );
}
