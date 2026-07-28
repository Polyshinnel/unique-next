import type { Metadata } from 'next';

import { api } from '@/lib/api';
import { siteConfig } from '@/lib/site-config';

export interface PageSeo {
    key: string;
    path: string;
    title: string | null;
    description: string | null;
    og_image: string | null;
}

const defaultOgImage = '/assets/img/og-image-home.png';

function metadataTitleToString(title: Metadata['title']): string | undefined {
    if (typeof title === 'string') {
        return title;
    }

    if (title && typeof title === 'object') {
        return 'absolute' in title ? title.absolute : title.default;
    }

    return undefined;
}

export function withSocialMetadata(metadata: Metadata): Metadata {
    const title = metadataTitleToString(metadata.title);
    const description = metadata.description ?? undefined;
    const canonicalValue = metadata.alternates?.canonical;
    const canonical = canonicalValue && typeof canonicalValue === 'object' && !(canonicalValue instanceof URL)
        ? canonicalValue.url
        : canonicalValue ?? undefined;

    return {
        ...metadata,
        openGraph: {
            type: 'website',
            locale: 'ru_RU',
            siteName: 'ЮНИК С',
            title,
            description,
            url: canonical,
            images: [{ url: defaultOgImage }],
        },
        twitter: {
            card: 'summary_large_image',
            title,
            description,
            images: [defaultOgImage],
        },
    };
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

export function canonicalUrl(path: string): string {
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }

    return `${siteConfig.appUrl}/${path.replace(/^\/+/, '')}`;
}

export function toMetadata(seo: PageSeo | null, fallback: Metadata): Metadata {
    if (!seo) {
        return withSocialMetadata(fallback);
    }

    return withSocialMetadata({
        title: seo.title ?? fallback.title,
        description: seo.description ?? fallback.description,
        alternates: fallback.alternates,
    });
}
