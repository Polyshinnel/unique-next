import type { Metadata } from 'next';
import { canonicalUrl, getPageSeo, toMetadata } from '@/lib/seo';
import { CatalogPageView } from '@/components/catalog/CatalogPageView';
import { catalogPathWithParams, getCatalogPage, type CatalogSearchParams, type CatalogSortValue } from '@/lib/catalog-api';
import { PageStructuredData } from '@/components/seo/OrganizationJsonLd';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('catalog');

    return toMetadata(seo, {
        title: 'Каталог оборудования | ЮНИК С',
        description: 'Каталог промышленного оборудования, станков, спецтехники и инструмента с карточками товаров и контактами менеджера.',
        alternates: {
            canonical: canonicalUrl('/catalog'),
        },
    });
}

type CatalogPageProps = {
    searchParams?: Promise<{
        page?: string | string[];
        region?: string | string[];
        availability?: string | string[];
        state?: string | string[];
        sort?: string | string[];
        search?: string | string[];
    }>;
};

function getSearchParamValue(value: string | string[] | undefined): string | undefined {
    return Array.isArray(value) ? value[0] : value;
}

function getCatalogSortValue(value: string | undefined): CatalogSortValue | null {
    if (value === 'price_asc' || value === 'price_desc' || value === 'default') {
        return value;
    }

    return null;
}

function getCatalogSearchParams(params: Awaited<NonNullable<CatalogPageProps['searchParams']>> | undefined): CatalogSearchParams {
    const sort = getCatalogSortValue(getSearchParamValue(params?.sort));

    return {
        page: getSearchParamValue(params?.page),
        region: getSearchParamValue(params?.region),
        availability: getSearchParamValue(params?.availability),
        state: getSearchParamValue(params?.state),
        sort,
        search: getSearchParamValue(params?.search),
    };
}

export default async function CatalogPage({ searchParams }: CatalogPageProps) {
    const params = await searchParams;
    const catalogSearchParams = getCatalogSearchParams(params);
    const data = await getCatalogPage(catalogSearchParams);

    const schemaPath = catalogPathWithParams('/catalog', catalogSearchParams);

    return <><PageStructuredData seoKey="catalog" path={schemaPath} pageType="CollectionPage" fallbackTitle="Каталог оборудования | ЮНИК С" fallbackDescription="Каталог промышленного оборудования, станков, спецтехники и инструмента с карточками товаров и контактами менеджера." itemList={{ path: schemaPath, products: data.products }} /><CatalogPageView data={data} searchParams={catalogSearchParams} /></>;
}
