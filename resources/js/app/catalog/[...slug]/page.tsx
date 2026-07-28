import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound, permanentRedirect } from 'next/navigation';
import { canonicalUrl, withSocialMetadata } from '@/lib/seo';
import { CatalogPageView } from '@/components/catalog/CatalogPageView';
import { ProductCollectionSection } from '@/components/catalog/ProductCollectionSection';
import { FeedbackRequestModal } from '@/components/common/FeedbackRequestModal';
import { ProductGallery } from '@/components/catalog/ProductGallery';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import {
    getCatalogPage,
    getCatalogProduct,
    type CatalogCategoryBreadcrumb,
    type CatalogProductCard,
    type CatalogPageResponse,
    type CatalogProductDetail,
    type CatalogSearchParams,
    type CatalogSortValue,
} from '@/lib/catalog-api';
import { formatCatalogPrice } from '@/lib/catalog-format';
import { emailHref, phoneHref } from '@/lib/site-content';
import {
    Anchor,
    Badge,
    Button,
    Container,
    Divider,
    Group,
    Stack,
    Text,
    Title,
} from '@mantine/core';
import {
    IconArrowRight,
    IconBrandTelegram,
    IconBrandVk,
    IconMessageCircle,
    IconMail,
    IconPhone,
} from '@tabler/icons-react';

type CatalogRouteSearchParams = {
    page?: string | string[];
    region?: string | string[];
    availability?: string | string[];
    state?: string | string[];
    sort?: string | string[];
    search?: string | string[];
};

type CatalogSlugPageProps = {
    params: Promise<{
        slug: string[];
    }>;
    searchParams?: Promise<CatalogRouteSearchParams>;
};

const categoryDescriptionFallback = 'Каталог промышленного оборудования, станков, спецтехники и инструмента с карточками товаров и контактами менеджера.';

function getSearchParamValue(value: string | string[] | undefined): string | undefined {
    return Array.isArray(value) ? value[0] : value;
}

function getCatalogSortValue(value: string | undefined): CatalogSortValue | null {
    if (value === 'price_asc' || value === 'price_desc' || value === 'default') {
        return value;
    }

    return null;
}

function getCatalogSearchParams(params: CatalogRouteSearchParams | undefined, categoryPath: string): CatalogSearchParams {
    const sort = getCatalogSortValue(getSearchParamValue(params?.sort));

    return {
        category_path: categoryPath,
        page: getSearchParamValue(params?.page),
        region: getSearchParamValue(params?.region),
        availability: getSearchParamValue(params?.availability),
        state: getSearchParamValue(params?.state),
        sort,
        search: getSearchParamValue(params?.search),
    };
}

function isProductRoute(slug: string[]): boolean {
    return /^\d+$/.test(slug.at(-1) ?? '');
}

function isApiNotFoundError(error: unknown): boolean {
    return error instanceof Error && error.message.includes('API Error: 404');
}

async function getCatalogPageOrNull(params: CatalogSearchParams): Promise<CatalogPageResponse | null> {
    try {
        return await getCatalogPage(params);
    } catch (error) {
        if (isApiNotFoundError(error)) {
            return null;
        }

        throw error;
    }
}

async function getCatalogProductOrNotFound(id: string): Promise<CatalogProductDetail> {
    try {
        return await getCatalogProduct(id);
    } catch (error) {
        if (isApiNotFoundError(error)) {
            notFound();
        }

        throw error;
    }
}

async function getCatalogProductOrNull(id: string): Promise<CatalogProductDetail | null> {
    try {
        return await getCatalogProduct(id);
    } catch (error) {
        if (isApiNotFoundError(error)) {
            return null;
        }

        throw error;
    }
}

async function getRelatedProducts(product: CatalogProductDetail): Promise<CatalogProductCard[]> {
    const firstTag = product.tags[0]?.trim();

    if (!firstTag) {
        return [];
    }

    const related = await getCatalogPage({
        search: firstTag,
    });

    return related.products
        .filter((item) => item.id !== product.id)
        .slice(0, 6);
}

function getRoutePathname(slug: string[]): string {
    return `/catalog/${slug.join('/')}`;
}

function htmlToPlainText(value: string | null): string | null {
    if (!value) {
        return null;
    }

    return value
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim() || null;
}

export async function generateMetadata({ params }: CatalogSlugPageProps): Promise<Metadata> {
    const { slug } = await params;
    const routeKey = slug.at(-1) ?? '';

    try {
        if (isProductRoute(slug)) {
            const product = await getCatalogProduct(routeKey);

            return withSocialMetadata({
                title: `${product.title} | ЮНИК С`,
                description: htmlToPlainText(product.summary ?? product.description) ?? categoryDescriptionFallback,
                alternates: {
                    canonical: canonicalUrl(product.canonicalHref),
                },
            });
        }

        const data = await getCatalogPage({ category_path: slug.join('/') });
        const category = data.category;

        return withSocialMetadata({
            title: category?.title || category?.name || 'Каталог оборудования',
            description: category?.description || categoryDescriptionFallback,
            alternates: {
                canonical: canonicalUrl(category?.href ?? getRoutePathname(slug)),
            },
        });
    } catch (error) {
        if (isApiNotFoundError(error)) {
            const product = routeKey === '' ? null : await getCatalogProductOrNull(routeKey);

            if (product !== null) {
                return withSocialMetadata({
                    title: `${product.title} | ЮНИК С`,
                    description: htmlToPlainText(product.summary ?? product.description) ?? categoryDescriptionFallback,
                    alternates: {
                        canonical: canonicalUrl(product.canonicalHref),
                    },
                });
            }

            return withSocialMetadata({
                title: 'Страница не найдена | ЮНИК С',
                description: 'Запрошенная страница каталога не найдена.',
                alternates: {
                    canonical: canonicalUrl(getRoutePathname(slug)),
                },
            });
        }

        throw error;
    }
}

function ProductBreadcrumbs({ product }: { product: CatalogProductDetail }) {
    const breadcrumbs: CatalogCategoryBreadcrumb[] = product.category
        ? [...product.category.breadcrumbs, product.category]
        : [];

    return (
        <div className="catalog-breadcrumbs">
            <Link href="/">Главная</Link>
            <span>/</span>
            <Link href="/catalog">Каталог оборудования</Link>
            {breadcrumbs.map((item) => (
                <span key={item.href} className="catalog-breadcrumbs__group">
                    <span>/</span>
                    <Link href={item.href}>{item.name}</Link>
                </span>
            ))}
            <span>/</span>
            <span>{product.title}</span>
        </div>
    );
}

function ProductShowPage({
    product,
    relatedProducts,
}: {
    product: CatalogProductDetail;
    relatedProducts: CatalogProductCard[];
}) {
    const manager = product.manager;
    const productImages = product.images.length ? product.images : [product.imageUrl];
    const isSold = product.price.isSold === true;
    const availability = isSold ? 'Продано' : product.availability?.name;
    const productSku = product.sku ?? 'уточняется';
    const feedbackMessage = `Добрый день! Меня заинтересовал станок ${productSku}, прошу связаться со мной в ближайшее время.`;
    const state = product.state?.name;
    const region = product.region?.name;
    const category = product.category?.name;

    return (
        <>
            <Header />
            <main>
                <section className="page-hero">
                    <Container size="xl">
                        <ProductBreadcrumbs product={product} />
                        <Title order={1}>{product.title}</Title>
                        {product.summary ? (
                            <div
                                className="product-show-summary"
                                dangerouslySetInnerHTML={{ __html: product.summary }}
                            />
                        ) : null}
                    </Container>
                </section>

                <section className="content-section product-show-section">
                    <Container size="xl">
                        <div className="product-show-layout">
                            <div className="product-show-main">
                                <ProductGallery title={product.title} images={productImages} />

                                {isSold && relatedProducts.length > 0 ? (
                                    <ProductCollectionSection
                                        title="Может быть вас заинтересует"
                                        description={`Подобрали похожие товары по тегу "${product.tags[0]}".`}
                                        products={relatedProducts}
                                        href={`/catalog?search=${encodeURIComponent(product.tags[0] ?? '')}`}
                                        buttonLabel="Смотреть все"
                                        limit={6}
                                        withContainer={false}
                                        columns={{ base: 1, sm: 2, lg: 3 }}
                                    />
                                ) : null}

                                {product.characteristicBlocks.map((block) => (
                                    <section key={block.title} className="product-info-block">
                                        <Title order={2}>{block.title}</Title>
                                        <div
                                            className="product-info-block__content"
                                            dangerouslySetInnerHTML={{ __html: block.contentHtml }}
                                        />
                                    </section>
                                ))}

                                {product.tags.length ? (
                                    <section className="product-info-block">
                                        <Title order={2}>Теги товара</Title>
                                        <Group gap="xs">
                                            {product.tags.map((tag) => (
                                                <Link key={tag} href={`/catalog?search=${encodeURIComponent(tag)}`} className="product-tag">
                                                    {tag}
                                                </Link>
                                            ))}
                                        </Group>
                                    </section>
                                ) : null}

                                {!isSold && relatedProducts.length > 0 ? (
                                    <ProductCollectionSection
                                        title="Может быть вас заинтересует"
                                        description={`Подобрали похожие товары по тегу "${product.tags[0]}".`}
                                        products={relatedProducts}
                                        href={`/catalog?search=${encodeURIComponent(product.tags[0] ?? '')}`}
                                        buttonLabel="Смотреть все"
                                        limit={6}
                                        withContainer={false}
                                        columns={{ base: 1, sm: 2, lg: 3 }}
                                    />
                                ) : null}
                            </div>

                            <aside className="product-show-aside">
                                <div className="product-show-panel">
                                    <Stack gap="md">
                                        {availability ? <Badge variant="light" color="blue">{availability}</Badge> : null}
                                        <div>
                                            <Text c="dimmed" size="sm" fw={700}>Цена</Text>
                                            <div className="product-show-price">{formatCatalogPrice(product.price)}</div>
                                        </div>
                                        <Divider />
                                        <div className="product-show-details">
                                            <div className="product-show-detail"><span>Название:</span><b>{product.title}</b></div>
                                            <div className="product-show-detail"><span>Артикул:</span><b>{product.sku ?? 'уточняется'}</b></div>
                                            {state ? <div className="product-show-detail"><span>Состояние:</span><b>{state}</b></div> : null}
                                            {availability ? <div className="product-show-detail"><span>Наличие:</span><b>{availability}</b></div> : null}
                                            {region ? <div className="product-show-detail"><span>Локация:</span><b>{region}</b></div> : null}
                                            {category ? <div className="product-show-detail"><span>Категория:</span><b>{category}</b></div> : null}
                                        </div>
                                        {manager ? (
                                            <>
                                                <Divider />
                                                <div className="product-manager-card">
                                                    <Text fw={800}>Контакты менеджера</Text>
                                                    <Text c="dimmed" size="sm">{manager.name}</Text>
                                                    {manager.phone ? (
                                                        <Anchor href={phoneHref(manager.phone)} className="contact-link">
                                                            <IconPhone size={18} />
                                                            <span>{manager.phone}</span>
                                                        </Anchor>
                                                    ) : null}
                                                    {manager.email ? (
                                                        <Anchor href={emailHref(manager.email)} className="contact-link">
                                                            <IconMail size={18} />
                                                            <span>{manager.email}</span>
                                                        </Anchor>
                                                    ) : null}
                                                    <Group gap="xs" mt="xs">
                                                        {manager.socialLinks.vk ? (
                                                            <Button
                                                                component="a"
                                                                href={manager.socialLinks.vk}
                                                                size="sm"
                                                                variant="light"
                                                                leftSection={<IconBrandVk size={16} />}
                                                            >
                                                                VK
                                                            </Button>
                                                        ) : null}
                                                        {manager.socialLinks.telegram ? (
                                                            <Button
                                                                component="a"
                                                                href={manager.socialLinks.telegram}
                                                                size="sm"
                                                                variant="light"
                                                                leftSection={<IconBrandTelegram size={16} />}
                                                            >
                                                                TG
                                                            </Button>
                                                        ) : null}
                                                        {manager.socialLinks.max ? (
                                                            <Button
                                                                component="a"
                                                                href={manager.socialLinks.max}
                                                                size="sm"
                                                                variant="light"
                                                                leftSection={<IconMessageCircle size={16} />}
                                                            >
                                                                MAX
                                                            </Button>
                                                        ) : null}
                                                    </Group>
                                                </div>
                                                {manager.phone ? (
                                                    <Button
                                                        component="a"
                                                        href={phoneHref(manager.phone)}
                                                        size="lg"
                                                        leftSection={<IconPhone size={19} />}
                                                        className="product-show-call-button"
                                                    >
                                                        Позвонить
                                                    </Button>
                                                ) : null}
                                            </>
                                        ) : null}
                                        <FeedbackRequestModal
                                            buttonLabel="Свяжитесь со мной"
                                            modalTitle="Свяжитесь со мной"
                                            description="Оставьте ваши контактные данные и опишите вопрос и мы свяжемся с вами в ближайшее время."
                                            size="lg"
                                            buttonVariant="default"
                                            buttonClassName="product-show-contact-button"
                                            buttonRightSection={<IconArrowRight size={18} />}
                                            initialMessage={feedbackMessage}
                                        />
                                    </Stack>
                                </div>
                            </aside>
                        </div>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}

export default async function CatalogSlugPage({ params, searchParams }: CatalogSlugPageProps) {
    const [{ slug }, resolvedSearchParams] = await Promise.all([params, searchParams]);
    const routeKey = slug.at(-1) ?? '';

    if (isProductRoute(slug)) {
        const product = await getCatalogProductOrNotFound(routeKey);
        const relatedProducts = await getRelatedProducts(product);
        const currentPathname = getRoutePathname(slug);

        if (currentPathname !== product.canonicalHref) {
            permanentRedirect(product.canonicalHref);
        }

        return <ProductShowPage product={product} relatedProducts={relatedProducts} />;
    }

    const categoryPath = slug.join('/');
    const catalogSearchParams = getCatalogSearchParams(resolvedSearchParams, categoryPath);
    const categoryPage = await getCatalogPageOrNull(catalogSearchParams);

    if (categoryPage !== null) {
        return <CatalogPageView data={categoryPage} searchParams={catalogSearchParams} />;
    }

    const product = routeKey === '' ? null : await getCatalogProductOrNull(routeKey);

    if (product === null) {
        notFound();
    }

    const currentPathname = getRoutePathname(slug);

    if (currentPathname !== product.canonicalHref) {
        permanentRedirect(product.canonicalHref);
    }

    const relatedProducts = await getRelatedProducts(product);

    return <ProductShowPage product={product} relatedProducts={relatedProducts} />;
}
