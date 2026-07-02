import type { Metadata } from 'next';
import { canonicalUrl, getPageSeo, toMetadata } from '@/lib/seo';
import { AboutPageView } from '@/components/about/AboutPageView';

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
    return <AboutPageView />;
}
