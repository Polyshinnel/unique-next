'use client';

import { useMemo, useState } from 'react';
import Link from 'next/link';
import { IconChevronDown, IconChevronUp } from '@tabler/icons-react';

type CatalogTreeSearchParams = {
    page?: number | string | null;
    region?: number | string | null;
    availability?: number | string | null;
    state?: number | string | null;
    sort?: string | null;
    search?: string | null;
};

type CatalogTreeParamKey = keyof CatalogTreeSearchParams;

type CatalogTreeOption = {
    id: number;
    name: string;
    count: number;
    href: string | null;
    slug?: string;
    children?: CatalogTreeOption[];
};

type CatalogCategoryTreeProps = {
    categories: CatalogTreeOption[];
    currentHref?: string;
    searchParams?: CatalogTreeSearchParams;
};

function getParamValue(value: CatalogTreeSearchParams[CatalogTreeParamKey] | undefined): string | undefined {
    if (value === null || value === undefined) {
        return undefined;
    }

    const normalizedValue = String(value).trim();

    return normalizedValue === '' ? undefined : normalizedValue;
}

function setCatalogParam(params: URLSearchParams, key: CatalogTreeParamKey, value: CatalogTreeSearchParams[CatalogTreeParamKey] | undefined) {
    const normalizedValue = getParamValue(value);

    params.delete(key);

    if (!normalizedValue) {
        return;
    }

    if (key === 'page' && Number(normalizedValue) === 1) {
        return;
    }

    if (key === 'sort' && normalizedValue === 'default') {
        return;
    }

    params.set(key, normalizedValue);
}

function getCatalogHref(
    baseHref: string,
    searchParams: CatalogTreeSearchParams | undefined,
    changes: Partial<CatalogTreeSearchParams> = {},
) {
    const params = new URLSearchParams();
    const keys: CatalogTreeParamKey[] = ['page', 'region', 'availability', 'state', 'sort', 'search'];

    keys.forEach((key) => {
        setCatalogParam(params, key, Object.prototype.hasOwnProperty.call(changes, key) ? changes[key] : searchParams?.[key]);
    });

    const query = params.toString();

    return query ? `${baseHref}?${query}` : baseHref;
}

function getCategoryHref(categoryHref: string | null, searchParams?: CatalogTreeSearchParams) {
    return getCatalogHref(getHrefPathname(categoryHref) ?? '/catalog', searchParams, { page: null });
}

function getOptionValue(option: CatalogTreeOption): string {
    return option.slug ?? String(option.id);
}

function getHrefPathname(href?: string | null): string | null {
    if (!href) {
        return null;
    }

    return href.split('?')[0] || null;
}

function isOptionInCurrentPath(option: CatalogTreeOption, currentPathname: string | null): boolean {
    const optionPathname = getHrefPathname(option.href);

    if (!optionPathname || !currentPathname) {
        return false;
    }

    return currentPathname === optionPathname || currentPathname.startsWith(`${optionPathname}/`);
}

function getDefaultOpenCategories(categories: CatalogTreeOption[], currentPathname: string | null): string[] {
    return categories.flatMap((category) => {
        const children = category.children ?? [];

        if (!children.length || !isOptionInCurrentPath(category, currentPathname)) {
            return [];
        }

        return [
            getOptionValue(category),
            ...getDefaultOpenCategories(children, currentPathname),
        ];
    });
}

function CategoryFilterOption({
    option,
    currentPathname,
    openedCategories,
    onToggle,
    searchParams,
    level = 0,
}: {
    option: CatalogTreeOption;
    currentPathname: string | null;
    openedCategories: Set<string>;
    onToggle: (value: string) => void;
    searchParams?: CatalogTreeSearchParams;
    level?: number;
}) {
    const optionValue = getOptionValue(option);
    const hasChildren = Boolean(option.children?.length);
    const isOpen = hasChildren && openedCategories.has(optionValue);
    const isActive = getHrefPathname(option.href) === currentPathname;
    const isDisabled = option.count === 0;
    const className = `catalog-category-link${isActive ? ' is-active' : ''}${isDisabled ? ' is-disabled' : ''}${isOpen ? ' is-open' : ''}`;
    const linkStyle = {
        paddingLeft: `${12 + level * 18}px`,
        paddingRight: `${level > 0 ? 14 : 12}px`,
    };
    const content = <span>{option.name}</span>;

    return (
        <div className="catalog-category-tree__item">
            <div className="catalog-category-tree__row">
                {isDisabled ? (
                    <span className={className} aria-disabled="true" style={linkStyle}>
                        {content}
                    </span>
                ) : (
                    <Link
                        href={getCategoryHref(option.href, searchParams)}
                        className={className}
                        aria-current={isActive ? 'page' : undefined}
                        style={linkStyle}
                    >
                        {content}
                    </Link>
                )}
                {hasChildren ? (
                    <button
                        className="catalog-category-tree__toggle"
                        type="button"
                        aria-label={isOpen ? `Свернуть ${option.name}` : `Раскрыть ${option.name}`}
                        aria-expanded={isOpen}
                        onClick={() => onToggle(optionValue)}
                    >
                        {isOpen ? <IconChevronUp size={17} /> : <IconChevronDown size={17} />}
                    </button>
                ) : null}
            </div>
            {hasChildren && isOpen ? (
                <div className="catalog-category-tree__children">
                    {option.children?.map((child) => (
                        <CategoryFilterOption
                            key={getOptionValue(child)}
                            option={child}
                            currentPathname={currentPathname}
                            openedCategories={openedCategories}
                            onToggle={onToggle}
                            searchParams={searchParams}
                            level={level + 1}
                        />
                    ))}
                </div>
            ) : null}
        </div>
    );
}

export function CatalogCategoryTree({ categories, currentHref, searchParams }: CatalogCategoryTreeProps) {
    const currentPathname = getHrefPathname(currentHref);
    const defaultOpenCategories = useMemo(
        () => new Set(getDefaultOpenCategories(categories, currentPathname)),
        [categories, currentPathname],
    );
    const [openedCategories, setOpenedCategories] = useState(defaultOpenCategories);

    function toggleCategory(value: string) {
        setOpenedCategories((current) => {
            const next = new Set(current);

            if (next.has(value)) {
                next.delete(value);
            } else {
                next.add(value);
            }

            return next;
        });
    }

    return (
        <div className="catalog-category-tree">
            {categories.map((category) => (
                <CategoryFilterOption
                    key={getOptionValue(category)}
                    option={category}
                    currentPathname={currentPathname}
                    openedCategories={openedCategories}
                    onToggle={toggleCategory}
                    searchParams={searchParams}
                />
            ))}
        </div>
    );
}
