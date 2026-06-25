import type { Metadata } from 'next';
import { CatalogPageView } from '@/components/catalog/CatalogPageView';

export const metadata: Metadata = {
    title: 'Каталог оборудования | ЮНИК С',
    description: 'Каталог промышленного оборудования, станков, спецтехники и инструмента с карточками товаров и контактами менеджера.',
};

type CatalogPageProps = {
    searchParams?: Promise<{
        page?: string | string[];
        region?: string | string[];
    }>;
};

export default async function CatalogPage({ searchParams }: CatalogPageProps) {
    const params = await searchParams;

    return <CatalogPageView searchParams={params} />;
}
