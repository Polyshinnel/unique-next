import type { Metadata } from 'next';
import type { ReactNode } from 'react';
import Link from 'next/link';
import { catalogProducts } from '@/lib/catalog-products';
import { Pagination } from '@/components/common/Pagination';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { ProductCard } from '@/components/catalog/ProductCard';
import {
    Button,
    Checkbox,
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
    IconChevronLeft,
    IconChevronRight,
    IconChevronsLeft,
    IconChevronsRight,
    IconSearch,
} from '@tabler/icons-react';

export const metadata: Metadata = {
    title: 'Каталог оборудования | ЮНИК С',
    description: 'Каталог промышленного оборудования, станков, спецтехники и инструмента с карточками товаров и контактами менеджера.',
};

const categories = Array.from(new Set(catalogProducts.map((product) => product.category)));
const regions = Array.from(new Set(catalogProducts.map((product) => product.location)));
const availability = ['В наличии', 'По запросу', 'Резерв'];
const conditions = ['Новое', 'Б/у', 'После сервиса'];
const CATALOG_PRODUCTS_PER_PAGE = 12;

type CatalogPageProps = {
    searchParams?: Promise<{
        page?: string | string[];
    }>;
};

function getCurrentPage(pageParam: string | string[] | undefined, totalPages: number) {
    const pageValue = Array.isArray(pageParam) ? pageParam[0] : pageParam;
    const parsedPage = Number(pageValue ?? 1);

    if (!Number.isFinite(parsedPage) || parsedPage < 1) {
        return 1;
    }

    return Math.min(Math.floor(parsedPage), totalPages);
}

function getCatalogPageHref(page: number) {
    return page === 1 ? '/catalog' : `/catalog?page=${page}`;
}

function FilterCard({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="catalog-filter-card">
            <Text fw={800} className="catalog-filter-card__title">{title}</Text>
            <Stack gap="xs">{children}</Stack>
        </div>
    );
}

function CatalogFilters() {
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
                {regions.map((item) => <Checkbox key={item} label={item} />)}
            </FilterCard>
            <FilterCard title="По категориям">
                {categories.map((item) => <Checkbox key={item} label={item} />)}
            </FilterCard>
            <FilterCard title="По доступности">
                {availability.map((item) => <Checkbox key={item} label={item} />)}
            </FilterCard>
            <FilterCard title="По состоянию">
                {conditions.map((item) => <Checkbox key={item} label={item} />)}
            </FilterCard>
            <Button fullWidth className="product-card__more">Применить фильтры</Button>
            <Button fullWidth variant="default">Сбросить фильтры</Button>
        </aside>
    );
}

export default async function CatalogPage({ searchParams }: CatalogPageProps) {
    const params = await searchParams;
    const totalPages = Math.max(1, Math.ceil(catalogProducts.length / CATALOG_PRODUCTS_PER_PAGE));
    const currentPage = getCurrentPage(params?.page, totalPages);
    const startIndex = (currentPage - 1) * CATALOG_PRODUCTS_PER_PAGE;
    const visibleProducts = catalogProducts.slice(startIndex, startIndex + CATALOG_PRODUCTS_PER_PAGE);

    return (
        <>
            <Header />
            <main>
                <section className="page-hero catalog-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <span>Каталог оборудования</span>
                        </div>
                        <Title order={1}>Каталог оборудования</Title>
                        <Text size="lg">
                            Бывшее в употреблении промышленное оборудование: металлорежущие и деревообрабатывающие станки, прессовое и кузнечное оборудование, спецтехника и позиции для погрузочно-разгрузочных работ.
                        </Text>
                    </Container>
                </section>
                <section className="content-section catalog-section">
                    <Container size="xl">
                        <div className="catalog-layout">
                            <CatalogFilters />
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
                                <Text size="sm" c="dimmed" mb="lg">Найдено 30 объявлений</Text>
                                <SimpleGrid cols={{ base: 1, sm: 2, xl: 3 }} spacing="lg">
                                    {visibleProducts.map((product) => (
                                        <ProductCard key={product.id} product={product} />
                                    ))}
                                </SimpleGrid>
                                <Pagination
                                    currentPage={currentPage}
                                    totalPages={totalPages}
                                    getPageHref={getCatalogPageHref}
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
                            <Button component="a" href="/contacts" size="lg" leftSection={<IconSearch size={19} />}>
                                Оставить заявку
                            </Button>
                        </Group>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
