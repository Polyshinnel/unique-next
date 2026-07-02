import type { ReactNode } from 'react';
import Link from 'next/link';
import { CatalogCategoryTree } from '@/components/catalog/CatalogCategoryTree';
import { FeedbackRequestModal } from '@/components/common/FeedbackRequestModal';
import { Pagination } from '@/components/common/Pagination';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import type { CatalogCategoryBreadcrumb, CatalogFilterOption, CatalogPageResponse, CatalogSearchParams } from '@/lib/catalog-api';
import { getSiteContacts } from '@/lib/site-contacts';
import {
    Badge,
    Button,
    Container,
    Group,
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

type CatalogPageViewProps = {
    data: CatalogPageResponse;
    searchParams?: CatalogSearchParams;
};

type CatalogFilterKey = 'region' | 'availability' | 'state';
type CatalogParamKey = keyof CatalogSearchParams;

const catalogTitleFallback = 'Каталог оборудования';
const catalogDescriptionFallback = 'Все оборудование, которое мы берем в работу, моментально попадает сюда в наш каталог. Информацию о новых поступлениях, акциях и изменениях цен мы размещаем в своих каналах в Телеграм и в МАКСе.';

function getParamValue(value: CatalogSearchParams[CatalogParamKey] | undefined): string | undefined {
    if (value === null || value === undefined) {
        return undefined;
    }

    const normalizedValue = String(value).trim();

    return normalizedValue === '' ? undefined : normalizedValue;
}

function setCatalogParam(params: URLSearchParams, key: CatalogParamKey, value: CatalogSearchParams[CatalogParamKey] | undefined) {
    const normalizedValue = getParamValue(value);

    params.delete(key);

    if (!normalizedValue) {
        return;
    }

    if (key === 'page' && Number(normalizedValue) === 1) {
        return;
    }

    if (key === 'sort' && normalizedValue === 'default') {
        return;
    }

    if (key === 'category_path') {
        return;
    }

    params.set(key, normalizedValue);
}

function getCatalogHref(
    baseHref: string,
    searchParams: CatalogSearchParams | undefined,
    changes: Partial<CatalogSearchParams> = {},
) {
    const params = new URLSearchParams();
    const keys: CatalogParamKey[] = ['page', 'region', 'availability', 'state', 'sort', 'search'];

    keys.forEach((key) => {
        setCatalogParam(params, key, Object.prototype.hasOwnProperty.call(changes, key) ? changes[key] : searchParams?.[key]);
    });

    const query = params.toString();

    return query ? `${baseHref}?${query}` : baseHref;
}

function getOptionValue(option: CatalogFilterOption): string {
    return option.slug ?? String(option.id);
}

function getFilterHref(baseHref: string, key: CatalogFilterKey, value: string, searchParams?: CatalogSearchParams) {
    return getCatalogHref(baseHref, searchParams, {
        [key]: value,
        page: null,
    });
}

function FilterCard({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="catalog-filter-card">
            <Text component="h2" fw={800} className="catalog-filter-card__title">{title}</Text>
            <Stack gap="xs">{children}</Stack>
        </div>
    );
}

function HiddenCatalogInputs({
    searchParams,
    exclude = [],
}: {
    searchParams?: CatalogSearchParams;
    exclude?: CatalogParamKey[];
}) {
    const keys: CatalogParamKey[] = ['region', 'availability', 'state', 'sort', 'search'];

    return (
        <>
            {keys.map((key) => {
                if (exclude.includes(key)) {
                    return null;
                }

                const value = getParamValue(searchParams?.[key]);

                if (!value || (key === 'sort' && value === 'default')) {
                    return null;
                }

                return <input key={key} type="hidden" name={key} value={value} />;
            })}
        </>
    );
}

function FilterOptionLink({
    option,
    filterKey,
    activeValue,
    baseHref,
    searchParams,
}: {
    option: CatalogFilterOption;
    filterKey: CatalogFilterKey;
    activeValue?: string;
    baseHref: string;
    searchParams?: CatalogSearchParams;
}) {
    const value = getOptionValue(option);
    const isActive = activeValue === value;
    const content = (
        <>
            <span>{option.name}</span>
            <Badge variant="light" color="gray" radius="sm">{option.count}</Badge>
        </>
    );

    if (option.count === 0) {
        return (
            <span className="catalog-region-link is-disabled" aria-disabled="true">
                {content}
            </span>
        );
    }

    return (
        <Link
            href={getFilterHref(baseHref, filterKey, value, searchParams)}
            className={`catalog-region-link${isActive ? ' is-active' : ''}`}
            aria-current={isActive ? 'page' : undefined}
        >
            {content}
        </Link>
    );
}

function CatalogFilters({
    baseHref,
    data,
    searchParams,
}: {
    baseHref: string;
    data: CatalogPageResponse;
    searchParams?: CatalogSearchParams;
}) {
    const activeRegion = getParamValue(searchParams?.region);
    const activeAvailability = getParamValue(searchParams?.availability);
    const activeState = getParamValue(searchParams?.state);
    const currentCategoryHref = data.category?.href;

    return (
        <aside className="catalog-sidebar">
            <FilterCard title="Поиск">
                <form action={baseHref} method="get" className="catalog-search-form">
                    <HiddenCatalogInputs searchParams={searchParams} exclude={['search']} />
                    <TextInput
                        className="catalog-search-form__input"
                        placeholder="Введите запрос..."
                        leftSection={<IconSearch size={17} />}
                        name="search"
                        defaultValue={getParamValue(searchParams?.search)}
                    />
                    <Button type="submit" className="catalog-search-form__button">Найти</Button>
                </form>
            </FilterCard>
            <FilterCard title="По региону">
                <Link
                    href={getCatalogHref(baseHref, searchParams, { region: null, page: null })}
                    className={`catalog-region-link${!activeRegion ? ' is-active' : ''}`}
                    aria-current={!activeRegion ? 'page' : undefined}
                >
                    <span>Все регионы</span>
                    <Badge variant="light" color="gray" radius="sm">{data.pagination.total}</Badge>
                </Link>
                {data.filters.regions.map((region) => (
                    <FilterOptionLink
                        key={getOptionValue(region)}
                        option={region}
                        filterKey="region"
                        activeValue={activeRegion}
                        baseHref={baseHref}
                        searchParams={searchParams}
                    />
                ))}
            </FilterCard>
            <FilterCard title="По категориям">
                <Link
                    href={getCatalogHref('/catalog', searchParams, { page: null })}
                    className={`catalog-category-link${!currentCategoryHref ? ' is-active' : ''}`}
                    aria-current={!currentCategoryHref ? 'page' : undefined}
                >
                    <span>Все категории</span>
                </Link>
                <CatalogCategoryTree
                    categories={data.filters.categories}
                    currentHref={currentCategoryHref}
                    searchParams={searchParams}
                />
            </FilterCard>
            <FilterCard title="По доступности">
                <Link
                    href={getCatalogHref(baseHref, searchParams, { availability: null, page: null })}
                    className={`catalog-region-link${!activeAvailability ? ' is-active' : ''}`}
                    aria-current={!activeAvailability ? 'page' : undefined}
                >
                    <span>Любая доступность</span>
                    <Badge variant="light" color="gray" radius="sm">{data.pagination.total}</Badge>
                </Link>
                {data.filters.availabilities.map((item) => (
                    <FilterOptionLink
                        key={getOptionValue(item)}
                        option={item}
                        filterKey="availability"
                        activeValue={activeAvailability}
                        baseHref={baseHref}
                        searchParams={searchParams}
                    />
                ))}
            </FilterCard>
            <FilterCard title="По состоянию">
                <Link
                    href={getCatalogHref(baseHref, searchParams, { state: null, page: null })}
                    className={`catalog-region-link${!activeState ? ' is-active' : ''}`}
                    aria-current={!activeState ? 'page' : undefined}
                >
                    <span>Любое состояние</span>
                    <Badge variant="light" color="gray" radius="sm">{data.pagination.total}</Badge>
                </Link>
                {data.filters.states.map((item) => (
                    <FilterOptionLink
                        key={getOptionValue(item)}
                        option={item}
                        filterKey="state"
                        activeValue={activeState}
                        baseHref={baseHref}
                        searchParams={searchParams}
                    />
                ))}
            </FilterCard>
            <Button fullWidth component="a" href={baseHref} variant="default">Сбросить фильтры</Button>
        </aside>
    );
}

function CatalogBreadcrumbs({ data }: { data: CatalogPageResponse }) {
    const currentCategory: CatalogCategoryBreadcrumb | null = data.category
        ? {
            id: data.category.id,
            name: data.category.name,
            slug: data.category.slug,
            path: data.category.path,
            pathString: data.category.path.join('/'),
            href: data.category.href,
            level: data.category.breadcrumbs.length,
        }
        : null;
    const breadcrumbs = data.category && currentCategory ? [...data.category.breadcrumbs, currentCategory] : [];

    return (
        <div className="catalog-breadcrumbs">
            <Link href="/">Главная</Link>
            <span>/</span>
            {breadcrumbs.length ? <Link href="/catalog">Каталог оборудования</Link> : <span>Каталог оборудования</span>}
            {breadcrumbs.map((category, index) => (
                <span key={category.href} className="catalog-breadcrumbs__group">
                    <span>/</span>
                    {index === breadcrumbs.length - 1 ? (
                        <span>{category.name}</span>
                    ) : (
                        <Link href={category.href}>{category.name}</Link>
                    )}
                </span>
            ))}
        </div>
    );
}

function CatalogSorting({
    baseHref,
    data,
    searchParams,
}: {
    baseHref: string;
    data: CatalogPageResponse;
    searchParams?: CatalogSearchParams;
}) {
    return (
        <Group justify="flex-start" align="center" gap="sm" wrap="wrap" className="catalog-toolbar">
            <Text fw={700}>Сортировка:</Text>
            {data.sorting.options.map((option) => {
                const isActive = option.value === data.sorting.active;

                return (
                    <Link
                        key={option.value}
                        href={getCatalogHref(baseHref, searchParams, { sort: option.value, page: null })}
                        className={`catalog-sort-link${isActive ? ' is-active' : ''}`}
                        aria-current={isActive ? 'page' : undefined}
                    >
                        {option.label}
                    </Link>
                );
            })}
        </Group>
    );
}

export async function CatalogPageView({ data, searchParams }: CatalogPageViewProps) {
    const contacts = await getSiteContacts();
    const baseHref = data.category?.href ?? '/catalog';
    const title = data.category ? (data.category.title || data.category.name) : catalogTitleFallback;
    const description = data.category?.description ?? catalogDescriptionFallback;
    const activeRegion = getParamValue(searchParams?.region);

    return (
        <>
            <Header />
            <main>
                <section className="page-hero catalog-hero">
                    <Container size="xl">
                        <CatalogBreadcrumbs data={data} />
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
                                    href={contacts.max}
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
                            <CatalogFilters baseHref={baseHref} data={data} searchParams={searchParams} />
                            <div className="catalog-main">
                                <Title order={2} className="visually-hidden">Результаты каталога</Title>
                                <CatalogSorting baseHref={baseHref} data={data} searchParams={searchParams} />
                                <Text size="sm" c="dimmed" mb="lg">
                                    Найдено {data.pagination.total} объявлений
                                    {activeRegion ? `, регион: ${activeRegion}` : ''}
                                </Text>
                                {data.products.length ? (
                                    <SimpleGrid cols={{ base: 1, sm: 2, xl: 3 }} spacing="lg">
                                        {data.products.map((product) => (
                                            <ProductCard key={product.id} product={product} />
                                        ))}
                                    </SimpleGrid>
                                ) : (
                                    <div className="catalog-empty">
                                        <Title order={2}>В этой выборке пока нет станков</Title>
                                        <Text c="dimmed">Выберите другую категорию или измените фильтры.</Text>
                                    </div>
                                )}
                                <Pagination
                                    currentPage={data.pagination.currentPage}
                                    totalPages={data.pagination.totalPages}
                                    getPageHref={(page) => getCatalogHref(baseHref, searchParams, { page })}
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
