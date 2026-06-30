export const navItems = [
    { label: 'Главная', href: '/' },
    { label: 'Услуги', href: '/services' },
    { label: 'Каталог оборудования', href: '/catalog' },
    { label: 'Отгрузки', href: '/otgruzki' },
    { label: 'Компания', href: '/about' },
    { label: 'Контакты', href: '/contacts' },
];

export const siteContacts = {
    phone: '+7 (4842) 59-65-75',
    email: 'info@uniqset.com',
    address: '248002, г. Калуга, ул. Никитина, д. 41 стр. 1, 3 этаж, офисы 313, 314',
    shortAddress: 'г. Калуга, ул. Никитина, д. 41 стр. 1',
    workingHours: {
        weekdays: 'Пн - Пт: с 9.00 до 18.00',
        weekends: 'Сб, Вск - выходные',
    },
    coordinates: {
        longitude: 36.27238477976226,
        latitude: 54.505882234159415,
    },
    socialLinks: {
        max: '#',
        vk: '#',
        telegram: '#',
    },
};

export const demoServices = [
    {
        id: 'service-1',
        title: 'Продажа оборудования',
        slug: 'prodazha-oborudovaniya',
        excerpt: 'У нас представлено несколько сотен единиц самых востребованных станков в понятном состоянии. По запросу дополнительно ответим на все ваши вопросы. Организуем транспорт, демонтаж, погрузку. У нас большой опыт удаленных отгрузок по всей территории РФ.',
        image: '/assets/img/serv-1.jpeg',
    },
    {
        id: 'service-2',
        title: 'Выкуп оборудования',
        slug: 'vykup',
        excerpt: 'Выкупаем станки по рыночной цене. Избавим от необходимости отвечать на пустые запросы «просто спросить», тратить время и деньги на подготовку объявлений на площадках, а также рассылку коммерческих предложений. У нас настроенный маркетинг и большая клиентская база.',
        image: '/assets/img/serv-2.jpg',
    },
    {
        id: 'service-3',
        title: 'Продажа инструмента',
        slug: 'prodazha-instrumenta',
        excerpt: 'Сориентируем, где можно заказать весь необходимый инструмент и расходники в одном месте со склада, в нужно объёме, в приемлемые сроки; получить исчерпывающую консультацию; воспользоваться удобной формой оплаты и приемлемыми сроками доставки.',
        image: '/assets/img/serv-4.jpg',
    },
    {
        id: 'service-4',
        title: 'Импорт оборудования',
        slug: 'import-oborudovaniya',
        excerpt: 'У нас большой опыт работы с иностранными компаниями, есть все необходимые компетенции и опыт при работе в ВЭД. Мы знаем как работает таможня, умеем правильно посчитать налоги и пошлины, реально оцениваем сроки и условия поставки (EXW, DDP и т.д.). Тем самым корректно прогнозируем стоимость.',
        image: '/assets/img/serv-3.jpg',
    },
];

export type DemoService = (typeof demoServices)[number];
export type NavItem = (typeof navItems)[number];
export type SiteContacts = typeof siteContacts;

export function phoneHref(phone: string) {
    const normalized = phone.replace(/[^\d+]/g, '');

    return normalized ? `tel:${normalized}` : '#';
}

export function emailHref(email: string) {
    return email ? `mailto:${email}` : '#';
}
