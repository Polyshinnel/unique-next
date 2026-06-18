export const catalogProducts = [
    {
        id: 'demo-1',
        title: 'Токарный станок 16К20',
        sku: 'UNQ-2048',
        category: 'Металлорежущие станки',
        location: 'Калуга',
        price: { amount: 1250000, isPublished: true },
        availability: 'В наличии',
        condition: 'Б/у',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog/demo-1',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Универсальный токарно-винторезный станок для обработки деталей из стали, чугуна и цветных металлов.',
        galleryImages: ['/assets/img/stanok.webp', '/assets/img/catalog.jpeg'],
        details: [
            { label: 'Год выпуска', value: 'уточняется' },
            { label: 'Тип оборудования', value: 'токарный станок' },
            { label: 'Готовность к осмотру', value: 'по предварительной записи' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Диаметр обработки над станиной: до 400 мм', 'Расстояние между центрами: до 1000 мм', 'Механическая коробка скоростей'],
            },
            {
                title: 'Основная информация',
                items: ['Станок подходит для ремонтных и серийных производственных задач.', 'Перед продажей доступна проверка состояния основных узлов.'],
            },
            {
                title: 'Условия продажи',
                items: ['Цена указана за единицу оборудования.', 'Резервирование и отгрузка согласуются с менеджером.'],
            },
        ],
        tags: ['токарный', 'металлообработка', '16К20'],
    },
    {
        id: 'demo-2',
        title: 'Листогибочный пресс 100 т',
        sku: 'UNQ-2051',
        category: 'Прессовое оборудование',
        location: 'Москва',
        price: { amount: null, isPublished: false },
        availability: 'По запросу',
        condition: 'Б/у',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog/demo-2',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Гидравлический листогибочный пресс для гибки листового металла на производственных участках.',
        galleryImages: ['/assets/img/catalog.jpeg', '/assets/img/stanok.webp'],
        details: [
            { label: 'Усилие', value: '100 т' },
            { label: 'Тип оборудования', value: 'листогибочный пресс' },
            { label: 'Комплектация', value: 'уточняется' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Гидравлическая схема привода', 'Рабочая длина гиба уточняется', 'Подходит для листового металла'],
            },
            {
                title: 'Комплектация',
                items: ['Базовый комплект оснастки согласуется перед сделкой.', 'Дополнительные матрицы и пуансоны уточняются по запросу.'],
            },
            {
                title: 'Условия продажи',
                items: ['Стоимость предоставляется по запросу.', 'Возможна организация осмотра и погрузки.'],
            },
        ],
        tags: ['листогиб', 'пресс', 'гибка'],
    },
    {
        id: 'demo-3',
        title: 'Фрезерный обрабатывающий центр',
        sku: 'UNQ-2074',
        category: 'Станки с ЧПУ',
        location: 'Тула',
        price: { amount: 3900000, isPublished: true },
        availability: 'В наличии',
        condition: 'После сервиса',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog/demo-3',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Вертикальный обрабатывающий центр с ЧПУ для фрезерования корпусных деталей и оснастки.',
        galleryImages: ['/assets/img/stanok.webp', '/assets/img/catalog.jpeg'],
        details: [
            { label: 'Система управления', value: 'ЧПУ' },
            { label: 'Тип', value: 'вертикальный центр' },
            { label: 'Сервисная подготовка', value: 'проведена' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Вертикальная компоновка', 'Инструментальный магазин', 'Рабочие перемещения уточняются'],
            },
            {
                title: 'Проверка',
                items: ['Проверяется геометрия, шпиндельный узел и система ЧПУ.', 'Демонстрация в работе возможна по предварительной договоренности.'],
            },
            {
                title: 'Погрузка',
                items: ['Погрузка и крепление оборудования рассчитываются отдельно.', 'Поможем организовать доставку по России.'],
            },
        ],
        tags: ['ЧПУ', 'фрезерный', 'обрабатывающий центр'],
    },
    {
        id: 'demo-4',
        title: 'Погрузчик вилочный 3 т',
        sku: 'UNQ-2080',
        category: 'Складская техника',
        location: 'Калуга',
        price: { amount: 980000, isPublished: true },
        availability: 'В наличии',
        condition: 'Б/у',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog/demo-4',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Вилочный погрузчик грузоподъемностью 3 тонны для склада, производства и погрузочных работ.',
        galleryImages: ['/assets/img/catalog.jpeg', '/assets/img/stanok.webp'],
        details: [
            { label: 'Грузоподъемность', value: '3 т' },
            { label: 'Назначение', value: 'складская логистика' },
            { label: 'Осмотр', value: 'Калуга' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Грузоподъемность до 3000 кг', 'Подходит для складских и производственных задач', 'Высота подъема уточняется'],
            },
            {
                title: 'Основная информация',
                items: ['Техника доступна для предварительного осмотра.', 'Состояние и наработка подтверждаются перед сделкой.'],
            },
            {
                title: 'Дополнительная информация',
                items: ['Возможна помощь с доставкой и оформлением документов.'],
            },
        ],
        tags: ['погрузчик', 'склад', '3 т'],
    },
    {
        id: 'demo-5',
        title: 'Сверлильный станок 2Н125',
        sku: 'UNQ-2086',
        category: 'Сверлильные станки',
        location: 'Рязань',
        price: { amount: 640000, isPublished: true },
        availability: 'В наличии',
        condition: 'Б/у',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog/demo-5',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Вертикально-сверлильный станок для сверления, рассверливания, зенкерования и нарезания резьбы.',
        galleryImages: ['/assets/img/stanok.webp', '/assets/img/catalog.jpeg'],
        details: [
            { label: 'Тип оборудования', value: 'вертикально-сверлильный станок' },
            { label: 'Модель', value: '2Н125' },
            { label: 'Состояние', value: 'рабочее' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Универсальный станок ремонтной группы', 'Ручная подача и настройка режимов', 'Компактное размещение в цехе'],
            },
            {
                title: 'Технические характеристики',
                items: ['Максимальный диаметр сверления уточняется', 'Параметры электропитания согласуются с менеджером'],
            },
            {
                title: 'Условия продажи',
                items: ['Цена опубликована на карточке.', 'Доступны резерв и подготовка к отгрузке.'],
            },
        ],
        tags: ['сверлильный', '2Н125', 'станок'],
    },
    {
        id: 'demo-6',
        title: 'Гидравлический пресс 160 т',
        sku: 'UNQ-2091',
        category: 'Гидравлические прессы',
        location: 'Москва',
        price: { amount: 2150000, isPublished: true },
        availability: 'По запросу',
        condition: 'После сервиса',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog/demo-6',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Гидравлический пресс усилием 160 тонн для производственных, ремонтных и сборочных операций.',
        galleryImages: ['/assets/img/catalog.jpeg', '/assets/img/stanok.webp'],
        details: [
            { label: 'Усилие', value: '160 т' },
            { label: 'Привод', value: 'гидравлический' },
            { label: 'Готовность', value: 'по запросу' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Усилие прессования 160 тонн', 'Гидравлическая система после сервисной подготовки', 'Рабочий стол и ход ползуна уточняются'],
            },
            {
                title: 'Демонтаж',
                items: ['Демонтаж и такелажные работы рассчитываются после осмотра площадки.', 'Команда ЮНИК С может сопровождать подготовку к вывозу.'],
            },
            {
                title: 'Условия продажи',
                items: ['Порядок оплаты и сроки отгрузки согласуются индивидуально.'],
            },
        ],
        tags: ['гидравлический пресс', '160 т', 'пресс'],
    },
    {
        id: 'demo-7',
        title: 'Ленточнопильный станок',
        sku: 'UNQ-2097',
        category: 'Пильное оборудование',
        location: 'Калуга',
        price: { amount: 870000, isPublished: true },
        availability: 'В наличии',
        condition: 'Б/у',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog/demo-7',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Ленточнопильный станок для резки сортового и профильного металлопроката.',
        galleryImages: ['/assets/img/stanok.webp', '/assets/img/catalog.jpeg'],
        details: [
            { label: 'Тип резки', value: 'ленточная' },
            { label: 'Материал', value: 'металлопрокат' },
            { label: 'Локация', value: 'Калуга' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Подходит для заготовительного участка', 'Режущий диапазон уточняется', 'Наличие СОЖ согласуется при осмотре'],
            },
            {
                title: 'Основная информация',
                items: ['Станок можно использовать в мелкосерийном и ремонтном производстве.'],
            },
            {
                title: 'Проверка',
                items: ['Перед покупкой доступна визуальная проверка и уточнение комплектности.'],
            },
        ],
        tags: ['ленточнопильный', 'резка', 'пила'],
    },
    {
        id: 'demo-8',
        title: 'Компрессор винтовой 11 кВт',
        sku: 'UNQ-2102',
        category: 'Компрессорное оборудование',
        location: 'Москва',
        price: { amount: null, isPublished: false },
        availability: 'По запросу',
        condition: 'Б/у',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog/demo-8',
        manager: { name: 'Отдел продаж ЮНИК С', phone: '8 (4842) 59-65-75' },
        summary: 'Винтовой компрессор мощностью 11 кВт для стабильного снабжения производственной линии сжатым воздухом.',
        galleryImages: ['/assets/img/catalog.jpeg', '/assets/img/stanok.webp'],
        details: [
            { label: 'Мощность', value: '11 кВт' },
            { label: 'Тип', value: 'винтовой компрессор' },
            { label: 'Цена', value: 'по запросу' },
        ],
        characteristicBlocks: [
            {
                title: 'Основные характеристики',
                items: ['Мощность электродвигателя 11 кВт', 'Рабочее давление уточняется', 'Подходит для производственных пневмосистем'],
            },
            {
                title: 'Комплектация',
                items: ['Ресивер, осушитель и магистральные фильтры уточняются отдельно.'],
            },
            {
                title: 'Условия продажи',
                items: ['Стоимость и срок готовности предоставляются по запросу менеджеру.'],
            },
        ],
        tags: ['компрессор', 'винтовой', '11 кВт'],
    },
] as const;

export type CatalogProduct = (typeof catalogProducts)[number];

export function getCatalogProduct(id: string) {
    return catalogProducts.find((product) => product.id === id);
}

export function formatCatalogPrice(price: CatalogProduct['price']) {
    if (!price || !price.isPublished || !price.amount) {
        return 'По запросу';
    }

    return `${String(price.amount).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')} ₽`;
}
