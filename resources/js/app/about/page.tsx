import type { Metadata } from 'next';
import { AboutPageView } from '@/components/about/AboutPageView';

export const metadata: Metadata = {
    title: 'О компании | ЮНИК С',
    description: 'История, география поставок и принципы работы компании ЮНИК С.',
};

export default function AboutPage() {
    return <AboutPageView />;
}
