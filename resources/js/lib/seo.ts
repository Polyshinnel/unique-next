import type { Metadata } from 'next';

import { api } from '@/lib/api';

export interface PageSeo {
    key: string;
    path: string;
    title: string | null;
    description: string | null;
    og_image: string | null;
}

function normalizeOgImagePath(path: string | null): string | null {
    if (!path) {
        return null;
    }

    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) {
        return path;
    }

    return `/storage/${path.replace(/^\/+/, '')}`;
}

export async function getPageSeo(key: string): Promise<PageSeo | null> {
    try {
        const seo = await api.server.get<PageSeo>(`/seo/by-key/${key}`, {
            next: { revalidate: 3600, tags: [`seo:${key}`] },
        } as never);

        return {
            ...seo,
            og_image: normalizeOgImagePath(seo.og_image),
        };
    } catch {
        return null;
    }
}

export function toMetadata(seo: PageSeo | null, fallback: Metadata): Metadata {
    if (!seo) {
        return fallback;
    }

    return {
        title: seo.title ?? fallback.title,
        description: seo.description ?? fallback.description,
        openGraph: seo.og_image
            ? { images: [{ url: seo.og_image }] }
            : fallback.openGraph,
    };
}
