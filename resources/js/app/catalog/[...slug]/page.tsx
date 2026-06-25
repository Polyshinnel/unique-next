import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound, permanentRedirect } from 'next/navigation';
import { CatalogPageView } from '@/components/catalog/CatalogPageView';
import { ProductGallery } from '@/components/catalog/ProductGallery';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { flatCatalogCategories, getCatalogCategoryAncestors, getCatalogCategoryByPath, type FlatCatalogCategory } from '@/lib/catalog-categories';
import {
    catalogProducts,
    formatCatalogPrice,
    getCatalogProduct,
    getCatalogProductCategory,
    getCatalogProductHref,
    type CatalogProduct,
} from '@/lib/catalog-products';
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
import { IconArrowRight, IconBrandTelegram, IconBrandWhatsapp, IconCheck, IconMessageCircle, IconMail, IconPhone } from '@tabler/icons-react';

type CatalogSlugPageProps = {
    params: Promise<{
        slug: string[];
    }>;
    searchParams?: Promise<{
        page?: string | string[];
        region?: string | string[];
    }>;
};

type ResolvedCatalogPath =
    | {
        type: 'category';
        category: FlatCatalogCategory;
    }
    | {
        type: 'product';
        product: CatalogProduct;
        category: FlatCatalogCategory;
        canonicalPath: string[];
        isCanonical: boolean;
    };

function pathsEqual(firstPath: string[], secondPath: string[]) {
    return firstPath.length === secondPath.length && firstPath.every((segment, index) => segment === secondPath[index]);
}

function getProductCanonicalPath(product: CatalogProduct) {
    const category = getCatalogProductCategory(product);

    return category ? [...category.path, product.id] : [product.id];
}

function resolveCatalogPath(slug: string[]): ResolvedCatalogPath | null {
    const product = getCatalogProduct(slug.at(-1) ?? '');

    if (product) {
        const category = getCatalogProductCategory(product);

        if (!category) {
            return null;
        }

        const canonicalPath = getProductCanonicalPath(product);

        return {
            type: 'product',
            product,
            category,
            canonicalPath,
            isCanonical: pathsEqual(slug, canonicalPath),
        };
    }

    const category = getCatalogCategoryByPath(slug);

    if (category) {
        return {
            type: 'category',
            category,
        };
    }

    return null;
}

export function generateStaticParams() {
    const categoryParams = flatCatalogCategories.map((category) => ({ slug: category.path }));
    const productParams = catalogProducts
        .map((product) => {
            const category = getCatalogProductCategory(product);

            return category ? { slug: [...category.path, product.id] } : null;
        })
        .filter((item): item is { slug: string[] } => Boolean(item));

    return [...categoryParams, ...productParams];
}

export async function generateMetadata({ params }: CatalogSlugPageProps): Promise<Metadata> {
    const { slug } = await params;
    const resolvedPath = resolveCatalogPath(slug);

    if (!resolvedPath) {
        return {
            title: 'Страница не найдена | ЮНИК С',
            description: 'Запрошенная страница каталога не найдена.',
        };
    }

    if (resolvedPath.type === 'category') {
        return {
            title: resolvedPath.category.seoTitle,
            description: resolvedPath.category.seoDescription,
        };
    }

    return {
        title: `${resolvedPath.product.title} | ЮНИК С`,
        description: resolvedPath.product.summary,
        alternates: {
            canonical: getCatalogProductHref(resolvedPath.product),
        },
    };
}

function ProductBreadcrumbs({ product, category }: { product: CatalogProduct; category: FlatCatalogCategory }) {
    const ancestors = getCatalogCategoryAncestors(category);

    return (
        <div className="catalog-breadcrumbs">
            <Link href="/">Главная</Link>
            <span>/</span>
            <Link href="/catalog">Каталог оборудования</Link>
            {ancestors.map((item) => (
                <span key={item.href} className="catalog-breadcrumbs__group">
                    <span>/</span>
                    <Link href={item.href}>{item.title}</Link>
                </span>
            ))}
            <span>/</span>
            <span>{product.title}</span>
        </div>
    );
}

function ProductShowPage({ product, category }: { product: CatalogProduct; category: FlatCatalogCategory }) {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero">
                    <Container size="xl">
                        <ProductBreadcrumbs product={product} category={category} />
                        <Title order={1}>{product.title}</Title>
                        <Text size="lg">{product.summary}</Text>
                    </Container>
                </section>

                <section className="content-section product-show-section">
                    <Container size="xl">
                        <div className="product-show-layout">
                            <div className="product-show-main">
                                <ProductGallery
                                    title={product.title}
                                    images={[product.imageUrl, ...product.galleryImages]}
                                />

                                {product.characteristicBlocks.map((block) => (
                                    <section key={block.title} className="product-info-block">
                                        <Title order={2}>{block.title}</Title>
                                        <div className="product-info-block__content">
                                            {block.items.map((item) => (
                                                <div key={item} className="product-info-row">
                                                    <IconCheck size={18} />
                                                    <Text>{item}</Text>
                                                </div>
                                            ))}
                                        </div>
                                    </section>
                                ))}

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
                            </div>

                            <aside className="product-show-aside">
                                <div className="product-show-panel">
                                    <Stack gap="md">
                                        <Badge variant="light" color="blue">{product.availability}</Badge>
                                        <div>
                                            <Text c="dimmed" size="sm" fw={700}>Цена</Text>
                                            <div className="product-show-price">{formatCatalogPrice(product.price)}</div>
                                        </div>
                                        <Divider />
                                        <div className="product-show-details">
                                            <div className="product-show-detail"><span>Название:</span><b>{product.title}</b></div>
                                            <div className="product-show-detail"><span>Артикул:</span><b>{product.sku}</b></div>
                                            <div className="product-show-detail"><span>Состояние:</span><b>{product.condition}</b></div>
                                            <div className="product-show-detail"><span>Наличие:</span><b>{product.availability}</b></div>
                                            <div className="product-show-detail"><span>Локация:</span><b>{product.location}</b></div>
                                            <div className="product-show-detail"><span>Категория:</span><b>{category.title}</b></div>
                                        </div>
                                        <Divider />
                                        <div className="product-manager-card">
                                            <Text fw={800}>Контакты менеджера</Text>
                                            <Text c="dimmed" size="sm">{product.manager.name}</Text>
                                            <Anchor href={phoneHref(product.manager.phone)} className="contact-link">
                                                <IconPhone size={18} />
                                                <span>{product.manager.phone}</span>
                                            </Anchor>
                                            <Anchor href={emailHref(product.manager.email)} className="contact-link">
                                                <IconMail size={18} />
                                                <span>{product.manager.email}</span>
                                            </Anchor>
                                            <Group gap="xs" mt="xs">
                                                <Button
                                                    component="a"
                                                    href={product.manager.socialLinks.whatsapp}
                                                    size="sm"
                                                    variant="light"
                                                    leftSection={<IconBrandWhatsapp size={16} />}
                                                >
                                                    WA
                                                </Button>
                                                <Button
                                                    component="a"
                                                    href={product.manager.socialLinks.telegram}
                                                    size="sm"
                                                    variant="light"
                                                    leftSection={<IconBrandTelegram size={16} />}
                                                >
                                                    TG
                                                </Button>
                                                <Button
                                                    component="a"
                                                    href={product.manager.socialLinks.max}
                                                    size="sm"
                                                    variant="light"
                                                    leftSection={<IconMessageCircle size={16} />}
                                                >
                                                    MAX
                                                </Button>
                                            </Group>
                                        </div>
                                        <Button
                                            component="a"
                                            href={phoneHref(product.manager.phone)}
                                            size="lg"
                                            leftSection={<IconPhone size={19} />}
                                            className="product-show-call-button"
                                        >
                                            Позвонить
                                        </Button>
                                        <Button
                                            component="a"
                                            href="/contacts"
                                            size="lg"
                                            variant="default"
                                            rightSection={<IconArrowRight size={18} />}
                                            className="product-show-contact-button"
                                        >
                                            Свяжитесь со мной
                                        </Button>
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
    const resolvedPath = resolveCatalogPath(slug);

    if (!resolvedPath) {
        notFound();
    }

    if (resolvedPath.type === 'category') {
        return <CatalogPageView currentCategory={resolvedPath.category} searchParams={resolvedSearchParams} />;
    }

    if (!resolvedPath.isCanonical) {
        permanentRedirect(getCatalogProductHref(resolvedPath.product));
    }

    return <ProductShowPage product={resolvedPath.product} category={resolvedPath.category} />;
}
