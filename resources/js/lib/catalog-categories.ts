export type CatalogCategory = {
    slug: string;
    title: string;
    description: string;
    seoTitle: string;
    seoDescription: string;
    children?: CatalogCategory[];
};

export type FlatCatalogCategory = Omit<CatalogCategory, 'children'> & {
    path: string[];
    href: string;
    level: number;
    descendantSlugs: string[];
};

export const catalogCategoryTree: CatalogCategory[] = [
    {
        slug: 'tokarnie-stanki',
        title: 'Токарные станки',
        description: 'Токарные станки и близкие модели для ремонтного, инструментального и серийного производства.',
        seoTitle: 'Токарные станки купить | Каталог ЮНИК С',
        seoDescription: 'Токарные станки в наличии и под заказ: 16К20, аналоги и другое металлообрабатывающее оборудование.',
        children: [
            {
                slug: '16k20-i-analogi',
                title: '16К20 и аналоги',
                description: 'Станки 16К20 и близкие универсальные токарно-винторезные модели для металлообработки.',
                seoTitle: '16К20 и аналоги купить | ЮНИК С',
                seoDescription: 'Каталог токарных станков 16К20 и аналогов с локациями, состоянием и контактами менеджера.',
            },
        ],
    },
    {
        slug: 'pressovoe-oborudovanie',
        title: 'Прессовое оборудование',
        description: 'Прессы для гибки, штамповки, сборочных и ремонтных операций.',
        seoTitle: 'Прессовое оборудование купить | Каталог ЮНИК С',
        seoDescription: 'Гидравлические, листогибочные и другое прессовое оборудование с сопровождением сделки и логистики.',
        children: [
            {
                slug: 'listogibochnye-pressy',
                title: 'Листогибочные прессы',
                description: 'Листогибочные прессы для гибки листового металла на производственных участках.',
                seoTitle: 'Листогибочные прессы купить | ЮНИК С',
                seoDescription: 'Листогибочные прессы в каталоге ЮНИК С: состояние, локации и условия продажи.',
            },
            {
                slug: 'gidravlicheskie-pressy',
                title: 'Гидравлические прессы',
                description: 'Гидравлические прессы для производственных, ремонтных и сборочных задач.',
                seoTitle: 'Гидравлические прессы купить | ЮНИК С',
                seoDescription: 'Гидравлические прессы с актуальными карточками, ценами по запросу и организацией отгрузки.',
            },
        ],
    },
    {
        slug: 'stanki-s-chpu',
        title: 'Станки с ЧПУ',
        description: 'Станки и обрабатывающие центры с ЧПУ для фрезерования и серийной обработки деталей.',
        seoTitle: 'Станки с ЧПУ купить | Каталог ЮНИК С',
        seoDescription: 'Станки с ЧПУ и обрабатывающие центры с проверкой состояния, локациями и сопровождением сделки.',
        children: [
            {
                slug: 'frezernye-obrabatyvayushchie-centry',
                title: 'Фрезерные обрабатывающие центры',
                description: 'Вертикальные и другие фрезерные обрабатывающие центры для корпусных деталей и оснастки.',
                seoTitle: 'Фрезерные обрабатывающие центры купить | ЮНИК С',
                seoDescription: 'Фрезерные обрабатывающие центры с ЧПУ в каталоге ЮНИК С с описанием состояния и условий продажи.',
            },
        ],
    },
    {
        slug: 'skladskaya-tehnika',
        title: 'Складская техника',
        description: 'Погрузчики и техника для склада, производства и погрузочных работ.',
        seoTitle: 'Складская техника купить | Каталог ЮНИК С',
        seoDescription: 'Складская техника и погрузчики в каталоге ЮНИК С с актуальными локациями и карточками товаров.',
    },
    {
        slug: 'sverlilnye-stanki',
        title: 'Сверлильные станки',
        description: 'Вертикально-сверлильные и универсальные станки для ремонтных и производственных задач.',
        seoTitle: 'Сверлильные станки купить | Каталог ЮНИК С',
        seoDescription: 'Сверлильные станки в каталоге промышленного оборудования ЮНИК С.',
    },
    {
        slug: 'pilnoe-oborudovanie',
        title: 'Пильное оборудование',
        description: 'Пильные станки для резки сортового и профильного металлопроката.',
        seoTitle: 'Пильное оборудование купить | Каталог ЮНИК С',
        seoDescription: 'Пильное оборудование и ленточнопильные станки с описанием состояния и локации.',
    },
    {
        slug: 'kompressornoe-oborudovanie',
        title: 'Компрессорное оборудование',
        description: 'Компрессоры и оборудование для производственных пневмосистем.',
        seoTitle: 'Компрессорное оборудование купить | Каталог ЮНИК С',
        seoDescription: 'Компрессорное оборудование в каталоге ЮНИК С с ценами по запросу и контактами менеджера.',
    },
];

function flattenCatalogCategories(
    categories: CatalogCategory[],
    parentPath: string[] = [],
    level = 0,
): FlatCatalogCategory[] {
    return categories.flatMap((category) => {
        const path = [...parentPath, category.slug];
        const children = flattenCatalogCategories(category.children ?? [], path, level + 1);
        const descendantSlugs = children.map((child) => child.slug);

        return [
            {
                slug: category.slug,
                title: category.title,
                description: category.description,
                seoTitle: category.seoTitle,
                seoDescription: category.seoDescription,
                path,
                href: `/catalog/${path.join('/')}`,
                level,
                descendantSlugs,
            },
            ...children,
        ];
    });
}

export const flatCatalogCategories = flattenCatalogCategories(catalogCategoryTree);

export function getCatalogCategoryByPath(path: string[]) {
    return flatCatalogCategories.find((category) => category.path.join('/') === path.join('/'));
}

export function getCatalogCategoryBySlug(slug: string) {
    return flatCatalogCategories.find((category) => category.slug === slug);
}

export function getCatalogCategoryDescendantSlugs(category: FlatCatalogCategory) {
    return [category.slug, ...category.descendantSlugs];
}

export function getCatalogCategoryAncestors(category: FlatCatalogCategory) {
    return category.path
        .map((_, index) => getCatalogCategoryByPath(category.path.slice(0, index + 1)))
        .filter((item): item is FlatCatalogCategory => Boolean(item));
}
