import type { MetadataRoute } from 'next';
import { siteConfig } from '@/lib/site-config';

export default function robots(): MetadataRoute.Robots {
    const baseUrl = new URL(siteConfig.appUrl);

    return {
        rules: {
            userAgent: '*',
            disallow: ['/admin', '/horizon'],
        },
        sitemap: new URL('/storage/sitemap.xml', baseUrl).toString(),
    };
}
