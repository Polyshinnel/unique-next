import type { Metadata } from 'next';
import { canonicalUrl, getPageSeo, toMetadata } from '@/lib/seo';
import { AboutPageView } from '@/components/about/AboutPageView';
import { PageStructuredData } from '@/components/seo/OrganizationJsonLd';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('company');

    return toMetadata(seo, {
        title: 'О компании | ЮНИК С',
        description: 'История, география поставок и принципы работы компании ЮНИК С.',
        alternates: {
            canonical: canonicalUrl('/about'),
        },
    });
}

export default function AboutPage() {
    return <><PageStructuredData seoKey="company" path="/about" fallbackTitle="О компании | ЮНИК С" fallbackDescription="История, география поставок и принципы работы компании ЮНИК С." /><AboutPageView /></>;
}
