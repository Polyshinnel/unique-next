import type { MetadataRoute } from 'next';

const metadataBaseUrl = process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:28080';

export default function robots(): MetadataRoute.Robots {
    const baseUrl = new URL(metadataBaseUrl);

    return {
        rules: {
            userAgent: '*',
            disallow: ['/admin', '/horizon'],
        },
        sitemap: new URL('/storage/sitemap.xml', baseUrl).toString(),
    };
}
