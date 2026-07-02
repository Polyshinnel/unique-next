import 'server-only';

import { api } from '@/lib/api';
import type { CatalogPrice } from '@/lib/catalog-format';

export type CatalogSortValue = 'default' | 'price_desc' | 'price_asc';

export type CatalogSearchParams = {
    page?: number | string | null;
    region?: number | string | null;
    availability?: number | string | null;
    state?: number | string | null;
    sort?: CatalogSortValue | null;
    search?: string | null;
    category_path?: string | null;
};

export type CatalogPagination = {
    currentPage: number;
    perPage: number;
    total: number;
    totalPages: number;
};

export type CatalogCategoryBreadcrumb = {
    id: number;
    name: string;
    slug: string;
    path: string[];
    pathString: string;
    href: string;
    level: number;
};

export type CatalogCategoryNode = {
    id: number;
    name: string;
    title: string;
    description: string | null;
    slug: string;
    path: string[];
    href: string;
    breadcrumbs: CatalogCategoryBreadcrumb[];
};

export type CatalogFilterOption = {
    id: number;
    name: string;
    count: number;
    href: string | null;
    slug?: string;
    level?: number;
    children?: CatalogFilterOption[];
};

export type CatalogProductReference = {
    id: number;
    name: string;
};

export type CatalogProductCategory = CatalogProductReference & {
    slug: string;
    path: string[];
    pathString: string;
    href: string;
    level: number;
    breadcrumbs: CatalogCategoryBreadcrumb[];
};

export type CatalogProductAvailability = CatalogProductReference & {
    color: string | null;
};

export type CatalogProductCard = {
    id: number;
    title: string;
    sku: string | null;
    category: CatalogProductCategory | null;
    region: CatalogProductReference | null;
    price: CatalogPrice;
    availability: CatalogProductAvailability | null;
    state: CatalogProductReference | null;
    imageUrl: string;
    href: string;
};

export type CatalogProductManager = CatalogProductReference & {
    phone: string | null;
    email: string | null;
    socialLinks: {
        vk: string | null;
        max: string | null;
        telegram: string | null;
    };
};

export type CatalogProductCharacteristicBlock = {
    title: string;
    contentHtml: string;
};

export type CatalogProductDetail = CatalogProductCard & {
    description: string | null;
    summary: string | null;
    canonicalHref: string;
    manager: CatalogProductManager | null;
    images: string[];
    tags: string[];
    characteristicBlocks: CatalogProductCharacteristicBlock[];
};

export type CatalogPageResponse = {
    category: CatalogCategoryNode | null;
    filters: {
        regions: CatalogFilterOption[];
        categories: CatalogFilterOption[];
        availabilities: CatalogFilterOption[];
        states: CatalogFilterOption[];
    };
    sorting: {
        active: CatalogSortValue;
        options: Array<{
            value: CatalogSortValue;
            label: string;
        }>;
    };
    products: CatalogProductCard[];
    pagination: CatalogPagination;
};

type CatalogQueryParams = Record<string, string | number | boolean>;

function isFilledParam(value: CatalogSearchParams[keyof CatalogSearchParams]): value is string | number {
    return value !== null && value !== undefined && String(value).trim() !== '';
}

function normalizeCategoryPath(path: string | string[]): string {
    return (Array.isArray(path) ? path.join('/') : path).trim().replace(/^\/+|\/+$/g, '');
}

export function buildCatalogQueryParams(params: CatalogSearchParams = {}): CatalogQueryParams {
    const query: CatalogQueryParams = {};

    Object.entries(params).forEach(([key, value]) => {
        if (!isFilledParam(value)) {
            return;
        }

        if (key === 'page' && Number(value) === 1) {
            return;
        }

        if (key === 'sort' && value === 'default') {
            return;
        }

        query[key] = value;
    });

    return query;
}

export function getCatalogPage(params: CatalogSearchParams = {}): Promise<CatalogPageResponse> {
    return api.server.get<CatalogPageResponse>('/catalog/page', {
        cache: 'no-store',
        params: buildCatalogQueryParams(params),
    });
}

export function getCatalogCategoryByPath(path: string | string[]): Promise<CatalogCategoryNode> {
    return api.server.get<CatalogCategoryNode>('/catalog/categories/by-path', {
        cache: 'no-store',
        params: {
            path: normalizeCategoryPath(path),
        },
    });
}

export function getCatalogProduct(id: number | string): Promise<CatalogProductDetail> {
    return api.server.get<CatalogProductDetail>(`/catalog/products/${id}`, {
        cache: 'no-store',
    });
}
