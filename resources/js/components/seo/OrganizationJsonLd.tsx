import { canonicalUrl, getPageSeo } from '@/lib/seo';
import type { CatalogProductCard } from '@/lib/catalog-api';
import { formatOfficeAddress, getSiteContacts, type SiteContacts } from '@/lib/site-contacts';
import { siteConfig } from '@/lib/site-config';

function nonEmpty(value: string): string | undefined {
    return value.trim() || undefined;
}

const breadcrumbLabels: Record<string, string> = {
    services: 'Услуги',
    catalog: 'Каталог оборудования',
    otgruzki: 'Отгрузки',
    about: 'Компания',
    contacts: 'Контакты',
    vacancy: 'Вакансии',
    'why-we': 'Почему мы',
    'ohrana-truda': 'Охрана труда',
    'private-policy': 'Политика конфиденциальности',
};

function humanizeSegment(segment: string): string {
    return segment
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function buildBreadcrumbs(
    url: string,
    pageTitle: string,
    breadcrumbItems: Array<{ href: string; name: string; title?: string }> = [],
) {
    const pathname = new URL(url).pathname.replace(/^\/+|\/+$/g, '');

    if (!pathname) {
        return [{
            '@type': 'ListItem',
            position: 1,
            name: 'Главная',
            item: `${siteConfig.appUrl}/`,
        }];
    }

    const segments = pathname.split('/');
    const items = [{
        '@type': 'ListItem',
        position: 1,
        name: 'Главная',
        item: `${siteConfig.appUrl}/`,
    }];

    segments.forEach((segment, index) => {
        const itemPath = `/${segments.slice(0, index + 1).join('/')}`;
        const category = breadcrumbItems.find((breadcrumb) => breadcrumb.href === itemPath);

        items.push({
            '@type': 'ListItem',
            position: index + 2,
            name: index === segments.length - 1
                ? pageTitle
                : (category?.title ?? category?.name ?? breadcrumbLabels[segment] ?? humanizeSegment(segment)),
            item: canonicalUrl(itemPath),
        });
    });

    return items;
}

function organizationNode(contacts: SiteContacts) {
    const sameAs = [contacts.telegram, contacts.vk]
        .map(nonEmpty)
        .filter((value): value is string => Boolean(value) && value !== '#');

    return {
        '@type': 'Organization',
        '@id': `${siteConfig.appUrl}/#organization`,
        name: 'Юник С',
        legalName: 'ООО “Юник С”',
        url: siteConfig.appUrl,
        logo: canonicalUrl('/assets/img/unique-logo.webp'),
        taxID: nonEmpty(contacts.inn),
        identifier: nonEmpty(contacts.ogrn),
        sameAs: sameAs.length > 0 ? sameAs : undefined,
        telephone: nonEmpty(contacts.phone),
        email: nonEmpty(contacts.email),
        address: {
            '@type': 'PostalAddress',
            streetAddress: nonEmpty(formatOfficeAddress(contacts)),
        },
        openingHoursSpecification: [
            {
                '@type': 'OpeningHoursSpecification',
                dayOfWeek: [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                ],
                opens: '09:00',
                closes: '18:00',
            },
        ],
    };
}

function productAvailability(product: CatalogProductCard): string | undefined {
    if (product.price.isSold || product.price.isReserve) return 'https://schema.org/SoldOut';
    return product.price.isPublished ? 'https://schema.org/InStock' : undefined;
}

function itemListNode(path: string, products: CatalogProductCard[], itemListId: string) {
    return {
        '@type': 'ItemList',
        '@id': itemListId,
        url: canonicalUrl(path),
        numberOfItems: products.length,
        itemListElement: products.map((product, index) => {
            const amount = Number(product.price.amount);
            const availability = productAvailability(product);
            const offer = availability && (product.price.isPublished ? Number.isFinite(amount) : true)
                ? {
                    '@type': 'Offer',
                    ...(product.price.isPublished ? { price: String(amount) } : {}),
                    priceCurrency: 'RUB',
                    availability,
                }
                : undefined;

            return {
                '@type': 'ListItem',
                position: index + 1,
                item: {
                    '@type': 'Product',
                    name: product.title,
                    url: canonicalUrl(product.href),
                    ...(product.imageUrl ? { image: canonicalUrl(product.imageUrl) } : {}),
                    ...(product.sku ? { sku: product.sku } : {}),
                    ...(offer ? { offers: offer } : {}),
                },
            };
        }),
    };
}

type PageJsonLdProps = {
    seoKey?: string;
    path: string;
    fallbackTitle: string;
    fallbackDescription: string;
    breadcrumbItems?: Array<{ href: string; name: string; title?: string }>;
    pageType?: 'WebPage' | 'CollectionPage' | 'ContactPage';
    service?: boolean;
    article?: {
        headline: string;
        description: string;
        image?: string;
        datePublished?: string;
        dateModified?: string;
    };
    itemList?: {
        path: string;
        products: CatalogProductCard[];
    };
    product?: {
        name: string;
        description: string;
        sku?: string | null;
        category?: string | null;
        images: string[];
        price?: number | string | null;
        isPublished: boolean;
        isReserve?: boolean;
        isSold?: boolean;
    };
};

function productNode(
    product: NonNullable<PageJsonLdProps['product']>,
    url: string,
    productId: string,
    organizationId: string,
) {
    const availability = product.isSold || product.isReserve
        ? 'https://schema.org/SoldOut'
        : product.isPublished
            ? 'https://schema.org/InStock'
            : undefined;
    const amount = Number(product.price);
    const offer = availability
        ? {
            '@type': 'Offer',
            url,
            ...(product.isPublished && Number.isFinite(amount) ? { price: String(amount) } : {}),
            priceCurrency: 'RUB',
            availability,
            seller: { '@id': organizationId },
        }
        : undefined;

    return {
        '@type': 'Product',
        '@id': productId,
        name: product.name,
        description: product.description,
        ...(product.sku ? { sku: product.sku } : {}),
        ...(product.category ? { category: product.category } : {}),
        ...(product.images.length ? { image: product.images.map((image) => canonicalUrl(image)) } : {}),
        url,
        itemCondition: 'https://schema.org/UsedCondition',
        ...(offer ? { offers: offer } : {}),
    };
}

export async function PageStructuredData({
    seoKey,
    path,
    fallbackTitle,
    fallbackDescription,
    breadcrumbItems = [],
    pageType = 'WebPage',
    service = false,
    article,
    itemList,
    product,
}: PageJsonLdProps) {
    const [seo, contacts] = await Promise.all([
        seoKey ? getPageSeo(seoKey) : Promise.resolve(null),
        getSiteContacts(),
    ]);
    const url = canonicalUrl(path);
    const isHomePage = new URL(url).pathname === '/';
    const pageTitle = (seo?.title ?? fallbackTitle).replace(/\s*\|\s*ЮНИК С\s*$/, '') || 'Страница';
    const pageDescription = seo?.description ?? fallbackDescription;
    const breadcrumbId = `${url}#breadcrumb`;
    const serviceId = `${url}#service`;
    const articleId = `${url}#article`;
    const imageId = `${url}#primaryimage`;
    const itemListId = `${url}#itemlist`;
    const productId = `${url}#product`;
    const organizationId = `${siteConfig.appUrl}/#organization`;
    const organization = organizationNode(contacts);
    const website = {
        '@type': 'WebSite',
        '@id': `${siteConfig.appUrl}/#website`,
        url: `${siteConfig.appUrl}/`,
        name: 'Юник С',
        publisher: { '@id': `${siteConfig.appUrl}/#organization` },
        inLanguage: 'ru-RU',
    };

    return (
        <script
            type="application/ld+json"
            dangerouslySetInnerHTML={{
                __html: JSON.stringify({
                    '@context': 'https://schema.org',
                    '@graph': [
                        organization,
                        ...(isHomePage ? [website] : []),
                        {
                            '@type': pageType,
                            '@id': `${url}#webpage`,
                            url,
                            name: seo?.title ?? fallbackTitle,
                            description: pageDescription,
                            isPartOf: { '@id': `${siteConfig.appUrl}/#website` },
                            about: { '@id': `${siteConfig.appUrl}/#organization` },
                            ...(service ? { mainEntity: { '@id': serviceId } } : {}),
                            ...(article ? { mainEntity: { '@id': articleId } } : {}),
                            ...(itemList ? { mainEntity: { '@id': itemListId } } : {}),
                            ...(product ? { mainEntity: { '@id': productId } } : {}),
                            breadcrumb: { '@id': breadcrumbId },
                            inLanguage: 'ru-RU',
                        },
                        {
                            '@type': 'BreadcrumbList',
                            '@id': breadcrumbId,
                            itemListElement: buildBreadcrumbs(url, pageTitle, breadcrumbItems),
                        },
                        ...(service ? [{
                            '@type': 'Service',
                            '@id': serviceId,
                            name: pageTitle,
                            description: pageDescription,
                            url,
                            provider: { '@id': `${siteConfig.appUrl}/#organization` },
                            areaServed: {
                                '@type': 'Country',
                                name: 'Россия',
                            },
                        }] : []),
                        ...(article ? [{
                            '@type': 'Article',
                            '@id': articleId,
                            headline: article.headline,
                            description: article.description,
                            ...(article.image ? { image: { '@id': imageId } } : {}),
                            ...(article.datePublished ? { datePublished: article.datePublished } : {}),
                            ...(article.dateModified ? { dateModified: article.dateModified } : {}),
                            author: { '@id': `${siteConfig.appUrl}/#organization` },
                            publisher: { '@id': `${siteConfig.appUrl}/#organization` },
                            mainEntityOfPage: { '@id': `${url}#webpage` },
                        }, ...(article.image ? [{
                            '@type': 'ImageObject',
                            '@id': imageId,
                            url: article.image,
                        }] : [])] : []),
                        ...(itemList ? [itemListNode(itemList.path, itemList.products, itemListId)] : []),
                        ...(product ? [productNode(product, url, productId, organizationId)] : []),
                    ],
                }).replace(/</g, '\\u003c'),
            }}
        />
    );
}
