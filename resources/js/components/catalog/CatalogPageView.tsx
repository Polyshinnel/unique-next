import type { ReactNode } from 'react';
import Link from 'next/link';
import { FeedbackRequestModal } from '@/components/common/FeedbackRequestModal';
import { Pagination } from '@/components/common/Pagination';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { catalogCategoryTree, getCatalogCategoryAncestors, getCatalogCategoryByPath, type CatalogCategory, type FlatCatalogCategory } from '@/lib/catalog-categories';
import { catalogProducts, type CatalogProduct, getCatalogProductsByCategory } from '@/lib/catalog-products';
import { siteContacts } from '@/lib/site-content';
import {
    Badge,
    Button,
    Container,
    Group,
    NumberInput,
    Select,
    SimpleGrid,
    Stack,
    Text,
    TextInput,
    Title,
} from '@mantine/core';
import {
    IconBrandTelegram,
    IconChevronLeft,
    IconChevronRight,
    IconChevronsLeft,
    IconChevronsRight,
    IconMessageCircle,
    IconSearch,
} from '@tabler/icons-react';
import { ProductCard } from './ProductCard';

const availability = ['В наличии', 'По запросу', 'Резерв'];
const conditions = ['Новое', 'Б/у', 'После сервиса'];
const catalogRegions = Array.from(new Set(['Москва', 'Калуга', 'Тула', 'Рязань', ...catalogProducts.map((product) => product.location)]));
const CATALOG_PRODUCTS_PER_PAGE = 12;

type CatalogSearchParams = {
    page?: string | string[];
    region?: string | string[];
};

type CatalogPageViewProps = {
    currentCategory?: FlatCatalogCategory;
    searchParams?: CatalogSearchParams;
};

function getCurrentPage(pageParam: string | string[] | undefined, totalPages: number) {
    const pageValue = Array.isArray(pageParam) ? pageParam[0] : pageParam;
    const parsedPage = Number(pageValue ?? 1);

    if (!Number.isFinite(parsedPage) || parsedPage < 1) {
        return 1;
    }

    return Math.min(Math.floor(parsedPage), totalPages);
}

function getSearchValue(value: string | string[] | undefined) {
    return Array.isArray(value) ? value[0] : value;
}

function getPageHref(baseHref: string, page: number, region?: string) {
    const params = new URLSearchParams();

    if (page > 1) {
        params.set('page', String(page));
    }

    if (region) {
        params.set('region', region);
    }

    const query = params.toString();

    return query ? `${baseHref}?${query}` : baseHref;
}

function getRegionCounts(products: CatalogProduct[]) {
    return products.reduce((counts, product) => {
        counts.set(product.location, (counts.get(product.location) ?? 0) + 1);

        return counts;
    }, new Map<string, number>());
}

function FilterCard({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="catalog-filter-card">
            <Text fw={800} className="catalog-filter-card__title">{title}</Text>
            <Stack gap="xs">{children}</Stack>
        </div>
    );
}

function CatalogCategoryTreeItem({
    category,
    currentCategory,
    parentPath = [],
    level = 0,
}: {
    category: CatalogCategory;
    currentCategory?: FlatCatalogCategory;
    parentPath?: string[];
    level?: number;
}) {
    const path = [...parentPath, category.slug];
    const flatCategory = getCatalogCategoryByPath(path);

    if (!flatCategory) {
        return null;
    }

    const isCurrent = currentCategory?.href === flatCategory.href;
    const isInCurrentPath = currentCategory?.path.slice(0, path.length).join('/') === path.join('/');
    const productCount = getCatalogProductsByCategory(flatCategory).length;

    return (
        <div className="catalog-category-tree__item">
            <Link
                href={flatCategory.href}
                className={`catalog-category-link${isCurrent ? ' is-active' : ''}${isInCurrentPath ? ' is-open' : ''}`}
                aria-current={isCurrent ? 'page' : undefined}
                style={{ paddingLeft: `${level * 14}px` }}
            >
                <span>{flatCategory.title}</span>
                <Badge variant="light" color="gray" radius="sm">{productCount}</Badge>
            </Link>
            {category.children?.length ? (
                <div className="catalog-category-tree__children">
                    {category.children.map((child) => (
                        <CatalogCategoryTreeItem
                            key={child.slug}
                            category={child}
                            currentCategory={currentCategory}
                            parentPath={path}
                            level={level + 1}
                        />
                    ))}
                </div>
            ) : null}
        </div>
    );
}

function CatalogFilters({
    baseHref,
    currentCategory,
    regionCounts,
    activeRegion,
}: {
    baseHref: string;
    currentCategory?: FlatCatalogCategory;
    regionCounts: Map<string, number>;
    activeRegion?: string;
}) {
    return (
        <aside className="catalog-sidebar">
            <FilterCard title="Поиск">
                <TextInput placeholder="Введите запрос..." leftSection={<IconSearch size={17} />} />
                <Button className="product-card__more">Найти</Button>
            </FilterCard>
            <FilterCard title="По цене">
                <Group grow>
                    <NumberInput label="От" placeholder="0" suffix=" ₽" min={0} hideControls />
                    <NumberInput label="До" placeholder="5 000 000" suffix=" ₽" min={0} hideControls />
                </Group>
            </FilterCard>
            <FilterCard title="По региону">
                <Link
                    href={baseHref}
                    className={`catalog-region-link${!activeRegion ? ' is-active' : ''}`}
                    aria-current={!activeRegion ? 'page' : undefined}
                >
                    <span>Все регионы</span>
                    <Badge variant="light" color="gray" radius="sm">
                        {Array.from(regionCounts.values()).reduce((sum, count) => sum + count, 0)}
                    </Badge>
                </Link>
                {catalogRegions.map((region) => {
                    const count = regionCounts.get(region) ?? 0;
                    const isActive = activeRegion === region;

                    if (count === 0) {
                        return (
                            <span key={region} className="catalog-region-link is-disabled" aria-disabled="true">
                                <span>{region}</span>
                                <Badge variant="light" color="gray" radius="sm">0</Badge>
                            </span>
                        );
                    }

                    return (
                        <Link
                            key={region}
                            href={getPageHref(baseHref, 1, region)}
                            className={`catalog-region-link${isActive ? ' is-active' : ''}`}
                            aria-current={isActive ? 'page' : undefined}
                        >
                            <span>{region}</span>
                            <Badge variant="light" color="gray" radius="sm">{count}</Badge>
                        </Link>
                    );
                })}
            </FilterCard>
            <FilterCard title="По категориям">
                <Link
                    href="/catalog"
                    className={`catalog-category-link${!currentCategory ? ' is-active' : ''}`}
                    aria-current={!currentCategory ? 'page' : undefined}
                >
                    <span>Все категории</span>
                    <Badge variant="light" color="gray" radius="sm">{catalogProducts.length}</Badge>
                </Link>
                <div className="catalog-category-tree">
                    {catalogCategoryTree.map((category) => (
                        <CatalogCategoryTreeItem key={category.slug} category={category} currentCategory={currentCategory} />
                    ))}
                </div>
            </FilterCard>
            <FilterCard title="По доступности">
                {availability.map((item) => <span key={item} className="catalog-filter-static">{item}</span>)}
            </FilterCard>
            <FilterCard title="По состоянию">
                {conditions.map((item) => <span key={item} className="catalog-filter-static">{item}</span>)}
            </FilterCard>
            <Button fullWidth component="a" href={baseHref} variant="default">Сбросить фильтры</Button>
        </aside>
    );
}

function CatalogBreadcrumbs({ currentCategory }: { currentCategory?: FlatCatalogCategory }) {
    const ancestors = currentCategory ? getCatalogCategoryAncestors(currentCategory) : [];

    return (
        <div className="catalog-breadcrumbs">
            <Link href="/">Главная</Link>
            <span>/</span>
            {currentCategory ? <Link href="/catalog">Каталог оборудования</Link> : <span>Каталог оборудования</span>}
            {ancestors.map((category, index) => (
                <span key={category.href} className="catalog-breadcrumbs__group">
                    <span>/</span>
                    {index === ancestors.length - 1 ? (
                        <span>{category.title}</span>
                    ) : (
                        <Link href={category.href}>{category.title}</Link>
                    )}
                </span>
            ))}
        </div>
    );
}

export function CatalogPageView({ currentCategory, searchParams }: CatalogPageViewProps) {
    const baseHref = currentCategory?.href ?? '/catalog';
    const activeRegion = getSearchValue(searchParams?.region);
    const categoryProducts = getCatalogProductsByCategory(currentCategory);
    const regionCounts = getRegionCounts(categoryProducts);
    const filteredProducts = activeRegion
        ? categoryProducts.filter((product) => product.location === activeRegion)
        : categoryProducts;
    const totalPages = Math.max(1, Math.ceil(filteredProducts.length / CATALOG_PRODUCTS_PER_PAGE));
    const currentPage = getCurrentPage(searchParams?.page, totalPages);
    const startIndex = (currentPage - 1) * CATALOG_PRODUCTS_PER_PAGE;
    const visibleProducts = filteredProducts.slice(startIndex, startIndex + CATALOG_PRODUCTS_PER_PAGE);
    const title = currentCategory?.title ?? 'Каталог оборудования';
    const description = currentCategory?.description
        ?? 'Все оборудование, которое мы берем в работу, моментально попадает сюда в наш каталог. Информацию о новых поступлениях, акциях и изменениях цен мы размещаем в своих каналах в Телеграм и в МАКСе.';

    return (
        <>
            <Header />
            <main>
                <section className="page-hero catalog-hero">
                    <Container size="xl">
                        <CatalogBreadcrumbs currentCategory={currentCategory} />
                        <Title order={1}>{title}</Title>
                        <Stack gap="xs" align="flex-start">
                            <Text size="lg">{description}</Text>
                            <Group gap="md" wrap="wrap" mt="sm">
                                <Button
                                    component="a"
                                    href="https://telegram.me/uniqset_gen"
                                    target="_blank"
                                    rel="noreferrer"
                                    leftSection={<IconBrandTelegram size={18} />}
                                >
                                    Telegram
                                </Button>
                                <Button
                                    component="a"
                                    href={siteContacts.socialLinks.max}
                                    target="_blank"
                                    rel="noreferrer"
                                    variant="white"
                                    color="dark"
                                    leftSection={<IconMessageCircle size={18} />}
                                >
                                    Max
                                </Button>
                            </Group>
                        </Stack>
                    </Container>
                </section>
                <section className="content-section catalog-section">
                    <Container size="xl">
                        <div className="catalog-layout">
                            <CatalogFilters
                                baseHref={baseHref}
                                currentCategory={currentCategory}
                                regionCounts={regionCounts}
                                activeRegion={activeRegion}
                            />
                            <div className="catalog-main">
                                <Group justify="flex-start" align="center" gap="sm" wrap="nowrap" className="catalog-toolbar">
                                    <Text fw={700}>Сортировка:</Text>
                                    <Select
                                        defaultValue="default"
                                        data={[
                                            { value: 'default', label: 'По умолчанию' },
                                            { value: 'price_desc', label: 'По убыванию цены' },
                                            { value: 'price_asc', label: 'По возрастанию цены' },
                                            { value: 'newest', label: 'Самые новые' },
                                        ]}
                                    />
                                </Group>
                                <Text size="sm" c="dimmed" mb="lg">
                                    Найдено {filteredProducts.length} объявлений
                                    {activeRegion ? `, регион: ${activeRegion}` : ''}
                                </Text>
                                {visibleProducts.length ? (
                                    <SimpleGrid cols={{ base: 1, sm: 2, xl: 3 }} spacing="lg">
                                        {visibleProducts.map((product) => (
                                            <ProductCard key={product.id} product={product} />
                                        ))}
                                    </SimpleGrid>
                                ) : (
                                    <div className="catalog-empty">
                                        <Title order={2}>В этой выборке пока нет станков</Title>
                                        <Text c="dimmed">Выберите другую категорию или регион.</Text>
                                    </div>
                                )}
                                <Pagination
                                    currentPage={currentPage}
                                    totalPages={totalPages}
                                    getPageHref={(page) => getPageHref(baseHref, page, activeRegion)}
                                    ariaLabel="Пагинация каталога"
                                    className="catalog-pagination"
                                    firstControl={<IconChevronsLeft size={18} />}
                                    previousControl={<IconChevronLeft size={18} />}
                                    nextControl={<IconChevronRight size={18} />}
                                    lastControl={<IconChevronsRight size={18} />}
                                />
                            </div>
                        </div>
                    </Container>
                </section>
                <section className="search-band">
                    <Container size="xl">
                        <Group justify="space-between" gap="lg">
                            <Stack gap={4}>
                                <Title order={2}>Не нашли нужную позицию?</Title>
                                <Text>Оставьте запрос, и менеджер подберет оборудование под ваши параметры.</Text>
                            </Stack>
                            <FeedbackRequestModal
                                modalTitle="Оставьте заявку на поиск оборудования"
                                description="Напишите, какая позиция нужна, и мы подберем оборудование под ваши параметры."
                            />
                        </Group>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
