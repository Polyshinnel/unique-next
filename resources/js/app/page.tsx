'use client';

import Link from 'next/link';
import {
    Anchor,
    Badge,
    Box,
    Burger,
    Button,
    Container,
    Divider,
    Drawer,
    Group,
    Image,
    SimpleGrid,
    Stack,
    Text,
    TextInput,
    Title,
    UnstyledButton,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import {
    IconArrowRight,
    IconBrandTelegram,
    IconBrandVk,
    IconChevronLeft,
    IconChevronRight,
    IconMail,
    IconMapPin,
    IconMessageCircle,
    IconPackage,
    IconPhone,
    IconSearch,
    IconShieldCheck,
    IconTool,
    IconTruckDelivery,
} from '@tabler/icons-react';
import { useEffect, useState } from 'react';

const navItems = [
    { label: 'Главная', href: '/' },
    { label: 'Услуги', href: '/services' },
    { label: 'Каталог оборудования', href: '/catalog' },
    { label: 'Отгрузки', href: '/otgruzki' },
    { label: 'Компания', href: '/about' },
    { label: 'Контакты', href: '/contacts' },
];

const contacts = {
    phone: '8 (4842) 59-65-75',
    email: 'info@uniqset.com',
    address: 'г. Калуга',
    socialLinks: {
        max: '#',
        vk: '#',
        telegram: '#',
    },
};

const heroSlides = [
    {
        title: 'Промышленное оборудование с понятной историей',
        text: 'Подбираем станки, прессовое и складское оборудование под производственные задачи, сроки и бюджет.',
        label: 'Каталог ЮНИК С',
        action: 'Смотреть каталог',
        href: '/catalog',
        image: '/assets/img/catalog.jpeg',
    },
    {
        title: 'Быстрые поступления для производства',
        text: 'Показываем доступность, состояние и ключевые параметры, чтобы заявку можно было собрать без лишних уточнений.',
        label: 'Последние поступления',
        action: 'Перейти к товарам',
        href: '/catalog',
        image: '/assets/img/stanok.webp',
    },
    {
        title: 'Выкуп, продажа и сопровождение сделок',
        text: 'Помогаем с оценкой, демонтажем, погрузкой и логистикой оборудования по России.',
        label: 'Сервис под ключ',
        action: 'Услуги компании',
        href: '/services',
        image: '/assets/img/catalog.jpeg',
    },
];

const demoProducts = [
    {
        id: 'demo-1',
        title: 'Токарный станок 16К20',
        sku: 'UNQ-2048',
        category: { name: 'Металлорежущие станки' },
        price: { amount: 1250000, isPublished: true },
        availability: 'in_stock',
        condition: 'used',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog',
    },
    {
        id: 'demo-2',
        title: 'Листогибочный пресс 100 т',
        sku: 'UNQ-2051',
        category: { name: 'Прессовое оборудование' },
        price: { amount: null, isPublished: false },
        availability: 'on_request',
        condition: 'used',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog',
    },
    {
        id: 'demo-3',
        title: 'Фрезерный обрабатывающий центр',
        sku: 'UNQ-2074',
        category: { name: 'Станки с ЧПУ' },
        price: { amount: 3900000, isPublished: true },
        availability: 'in_stock',
        condition: 'after_service',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog',
    },
    {
        id: 'demo-4',
        title: 'Погрузчик вилочный 3 т',
        sku: 'UNQ-2080',
        category: { name: 'Складская техника' },
        price: { amount: 980000, isPublished: true },
        availability: 'in_stock',
        condition: 'used',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog',
    },
    {
        id: 'demo-5',
        title: 'Сверлильный станок 2Н125',
        sku: 'UNQ-2086',
        category: { name: 'Сверлильные станки' },
        price: { amount: 640000, isPublished: true },
        availability: 'in_stock',
        condition: 'used',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog',
    },
    {
        id: 'demo-6',
        title: 'Гидравлический пресс 160 т',
        sku: 'UNQ-2091',
        category: { name: 'Гидравлические прессы' },
        price: { amount: 2150000, isPublished: true },
        availability: 'on_request',
        condition: 'after_service',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog',
    },
    {
        id: 'demo-7',
        title: 'Ленточнопильный станок',
        sku: 'UNQ-2097',
        category: { name: 'Пильное оборудование' },
        price: { amount: 870000, isPublished: true },
        availability: 'in_stock',
        condition: 'used',
        imageUrl: '/assets/img/stanok.webp',
        url: '/catalog',
    },
    {
        id: 'demo-8',
        title: 'Компрессор винтовой 11 кВт',
        sku: 'UNQ-2102',
        category: { name: 'Компрессорное оборудование' },
        price: { amount: null, isPublished: false },
        availability: 'on_request',
        condition: 'used',
        imageUrl: '/assets/img/catalog.jpeg',
        url: '/catalog',
    },
];

const demoServices = [
    {
        id: 'service-1',
        title: 'Продажа оборудования',
        slug: 'prodazha-oborudovaniya',
        excerpt: 'Подбор, проверка состояния, резервирование и сопровождение покупки.',
    },
    {
        id: 'service-2',
        title: 'Выкуп оборудования',
        slug: 'vykup',
        excerpt: 'Оценка станков и производственных линий с быстрым согласованием сделки.',
    },
    {
        id: 'service-3',
        title: 'Продажа инструмента',
        slug: 'prodazha-instrumenta',
        excerpt: 'Поможем заказать инструмент и расходники со склада в нужном объеме и с удобной доставкой.',
    },
    {
        id: 'service-4',
        title: 'Импорт оборудования',
        slug: 'import-oborudovaniya',
        excerpt: 'Сопровождаем ВЭД, таможенное оформление и расчет поставки, чтобы точно прогнозировать стоимость.',
    },
];

type Contact = typeof contacts;
type Product = (typeof demoProducts)[number];
type SocialIcon = typeof IconBrandVk;

function phoneHref(phone: string) {
    const normalized = phone.replace(/[^\d+]/g, '');

    return normalized ? `tel:${normalized}` : '#';
}

function emailHref(email: string) {
    return email ? `mailto:${email}` : '#';
}

function formatPrice(price: Product['price']) {
    if (!price || !price.isPublished || !price.amount) {
        return 'По запросу';
    }

    return `${String(price.amount).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')} ₽`;
}

function readableStatus(value: string) {
    const statuses: Record<string, string> = {
        in_stock: 'В наличии',
        on_request: 'По запросу',
        reserved: 'Резерв',
        sold: 'Продано',
        new: 'Новое',
        used: 'Б/у',
        after_service: 'После сервиса',
    };

    return statuses[value] || value || 'Уточняется';
}

function Header({ contacts: siteContacts }: { contacts: Contact }) {
    const [opened, { toggle, close }] = useDisclosure(false);

    const menu = (
        <nav className="site-nav">
            {navItems.map((item) => (
                <Link key={item.href} href={item.href} className="site-nav__link" onClick={close}>
                    {item.label}
                </Link>
            ))}
        </nav>
    );

    return (
        <header className="site-header">
            <Container size="xl" className="site-header__inner">
                <Link href="/" className="site-logo" aria-label="ЮНИК С">
                    <Image src="/assets/img/unique-logo.png" alt="ЮНИК С" />
                </Link>

                <Box className="site-header__nav">{menu}</Box>

                <div className="site-header__contacts">
                    <Anchor href={phoneHref(siteContacts.phone)} className="contact-link">
                        <IconPhone size={18} className="contact-link__icon" />
                        <span className="contact-link__text">{siteContacts.phone}</span>
                    </Anchor>
                    <Anchor href={emailHref(siteContacts.email)} className="contact-link">
                        <IconMail size={18} className="contact-link__icon" />
                        <span className="contact-link__text">{siteContacts.email}</span>
                    </Anchor>
                </div>

                <Burger opened={opened} onClick={toggle} hiddenFrom="md" aria-label="Меню" />
            </Container>

            <Drawer opened={opened} onClose={close} position="right" title="ЮНИК С" size="sm">
                <Stack gap="lg">
                    {menu}
                    <Divider />
                    <Anchor href={phoneHref(siteContacts.phone)} className="contact-link">
                        <IconPhone size={18} />
                        <span>{siteContacts.phone}</span>
                    </Anchor>
                    <Anchor href={emailHref(siteContacts.email)} className="contact-link">
                        <IconMail size={18} />
                        <span>{siteContacts.email}</span>
                    </Anchor>
                    <Button component={Link} href="/contacts" onClick={close}>
                        Связаться
                    </Button>
                </Stack>
            </Drawer>
        </header>
    );
}

function HeroSlider() {
    const [active, setActive] = useState(0);
    const slide = heroSlides[active];

    useEffect(() => {
        const timer = window.setInterval(() => {
            setActive((current) => (current + 1) % heroSlides.length);
        }, 7000);

        return () => window.clearInterval(timer);
    }, []);

    const previous = () => setActive((current) => (current - 1 + heroSlides.length) % heroSlides.length);
    const next = () => setActive((current) => (current + 1) % heroSlides.length);

    return (
        <section className="hero-slider">
            <div className="hero-slider__media" style={{ backgroundImage: `url(${slide.image})` }} />
            <Container size="xl" className="hero-slider__content">
                <Stack gap="lg" maw={760}>
                    <Badge className="hero-slider__badge">{slide.label}</Badge>
                    <Title order={1}>{slide.title}</Title>
                    <Text>{slide.text}</Text>
                    <Group gap="sm">
                        <Button component={Link} href={slide.href} size="lg" rightSection={<IconArrowRight size={19} />}>
                            {slide.action}
                        </Button>
                        <Button component={Link} href="/contacts" size="lg" variant="white">
                            Получить консультацию
                        </Button>
                    </Group>
                </Stack>

                <Group className="hero-slider__controls">
                    <UnstyledButton className="slider-arrow" onClick={previous} aria-label="Предыдущий баннер">
                        <IconChevronLeft size={24} />
                    </UnstyledButton>
                    <Group gap={8}>
                        {heroSlides.map((item, index) => (
                            <UnstyledButton
                                key={item.title}
                                className={`slider-dot ${index === active ? 'slider-dot--active' : ''}`}
                                onClick={() => setActive(index)}
                                aria-label={`Баннер ${index + 1}`}
                            />
                        ))}
                    </Group>
                    <UnstyledButton className="slider-arrow" onClick={next} aria-label="Следующий баннер">
                        <IconChevronRight size={24} />
                    </UnstyledButton>
                </Group>
            </Container>
        </section>
    );
}

function CompanySection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <div className="company-block">
                    <Stack gap="md">
                        <Badge variant="light" color="orange">О компании</Badge>
                        <Title order={2}>ЮНИК С помогает производствам находить оборудование без лишнего риска</Title>
                        <Text c="dimmed" size="lg">
                            Мы работаем с промышленным оборудованием, которое уже прошло реальную эксплуатацию:
                            подбираем позиции под задачу, фиксируем состояние, помогаем с осмотром, оформлением
                            сделки и дальнейшей отгрузкой.
                        </Text>
                        <SimpleGrid cols={{ base: 1, sm: 3 }} spacing="md">
                            <div className="fact-tile">
                                <b>2019</b>
                                <span>год основания</span>
                            </div>
                            <div className="fact-tile">
                                <b>Россия</b>
                                <span>поставка по регионам</span>
                            </div>
                            <div className="fact-tile">
                                <b>Под ключ</b>
                                <span>сделка и логистика</span>
                            </div>
                        </SimpleGrid>
                    </Stack>
                    <div className="company-image">
                        <Image src="/assets/img/catalog.jpeg" alt="Промышленное оборудование ЮНИК С" />
                    </div>
                </div>
            </Container>
        </section>
    );
}

function ProductCard({ product }: { product: Product }) {
    return (
        <article className="product-card">
            <Link href={product.url} className="product-card__image">
                <Image src={product.imageUrl} alt={product.title} />
            </Link>
            <div className="product-card__body">
                <Group justify="space-between" gap="xs">
                    <Badge variant="light" color="blue">{product.sku}</Badge>
                    <span className="product-card__price">{formatPrice(product.price)}</span>
                </Group>
                <Title order={3}>
                    <Link href={product.url}>{product.title}</Link>
                </Title>
                <Text c="dimmed" size="sm">{product.category.name}</Text>
                <Group gap="xs" mt="sm">
                    <span className="product-tag">{readableStatus(product.condition)}</span>
                    <span className="product-tag">{readableStatus(product.availability)}</span>
                </Group>
                <Button component={Link} href={product.url} variant="default" rightSection={<IconArrowRight size={17} />}>
                    Подробнее
                </Button>
            </div>
        </article>
    );
}

function LatestProducts() {
    return (
        <section className="content-section content-section--tight-top">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Badge variant="light" color="orange">Каталог</Badge>
                        <Title order={2}>Последние поступления</Title>
                        <Text c="dimmed">Примеры карточек товаров для будущего наполнения каталога.</Text>
                    </Stack>
                    <Button component={Link} href="/catalog" variant="outline" rightSection={<IconArrowRight size={18} />}>
                        Перейти в каталог
                    </Button>
                </Group>
                <SimpleGrid cols={{ base: 1, sm: 2, lg: 4 }} spacing="lg">
                    {demoProducts.map((product) => (
                        <ProductCard key={product.id} product={product} />
                    ))}
                </SimpleGrid>
            </Container>
        </section>
    );
}

function ServicesSection() {
    const icons = [IconPackage, IconTool, IconTruckDelivery, IconShieldCheck];

    return (
        <section className="content-section content-section--white content-section--tight-top">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Badge variant="light" color="orange">Сервис</Badge>
                        <Title order={2}>Услуги</Title>
                        <Text c="dimmed">Основные направления работы с оборудованием и сделками.</Text>
                    </Stack>
                    <Button component={Link} href="/services" variant="outline" rightSection={<IconArrowRight size={18} />}>
                        Все услуги
                    </Button>
                </Group>
                <SimpleGrid cols={{ base: 1, sm: 2, xl: 4 }} spacing="lg">
                    {demoServices.map((service, index) => {
                        const Icon = icons[index] || IconTool;

                        return (
                            <Link key={service.id} href={`/services/${service.slug}`} className="service-card">
                                <span className="service-card__icon">
                                    <Icon size={26} />
                                </span>
                                <Title order={3}>{service.title}</Title>
                                <Text c="dimmed">{service.excerpt}</Text>
                                <span className="service-card__more">
                                    Подробнее
                                    <IconArrowRight size={17} />
                                </span>
                            </Link>
                        );
                    })}
                </SimpleGrid>
            </Container>
        </section>
    );
}

function BusinessEquipmentSection() {
    return (
        <section className="content-section content-section--white content-section--tight-top">
            <Container size="xl">
                <Stack gap="lg">
                    <Title order={2}>Промышленное оборудование и спецтехника для бизнеса</Title>
                    <div className="info-article__content">
                        <Text>
                            Добро пожаловать на сайт вашего надежного партнера в сфере промышленного оборудования и
                            инструментов. Мы специализируемся на комплексных решениях для производственных предприятий
                            всех масштабов — от небольших цехов до крупных заводов. На наших страницах вы найдете
                            широкий ассортимент бывшей в употреблении техники, металлообрабатывающих и
                            деревообрабатывающих станков, прессового и кузнечного оборудования, а также спецтехники
                            для погрузочно-разгрузочных работ.
                        </Text>
                        <Text>
                            Мы предлагаем продажу промышленных станков и оборудования, которые прошли
                            профессиональную оценку и готовы к эксплуатации. Наши специалисты помогут подобрать
                            технику под конкретные технологические задачи, бюджет и сроки, обеспечив при этом
                            высокое качество и надежность поставок.
                        </Text>
                        <Text>
                            Для клиентов, желающих избавиться от излишнего или неиспользуемого оборудования, доступна
                            услуга выкупа техники. Мы оперативно оцениваем состояние оборудования и предлагаем
                            выгодные условия сотрудничества, позволяя бизнесу быстро решать вопросы реализации
                            активов без лишних затрат времени и ресурсов.
                        </Text>
                        <Text>
                            Помимо внутреннего рынка, мы организуем импорт оборудования из Европы и Азии с
                            оформлением всех необходимых документов, логистикой и таможенным сопровождением. Такой
                            подход обеспечивает клиентам доступ к широкому выбору техники и инструментов по
                            оптимальной цене.
                        </Text>
                        <Text>
                            В каталоге продукции представлено также множество видов инструмента и расходных
                            материалов: металлические, абразивные, слесарные и мерительные изделия от проверенных
                            поставщиков. Предусмотрена профессиональная консультация и удобные условия отправки по
                            всей территории России.
                        </Text>
                        <Text>
                            Мы ценим своих клиентов и стремимся к максимальной прозрачности в работе: предоставляем
                            подробную информацию о состоянии техники, демонстрации, фото и видео по запросу, а также
                            сопровождаем каждый этап сделки — от выбора до доставки.
                        </Text>
                        <Text>
                            Выбирая нас, вы получаете проверенные решения для роста и развития производства, удобные
                            формы оплаты, быструю логистику и поддержку на всех этапах сотрудничества.
                        </Text>
                    </div>
                </Stack>
            </Container>
        </section>
    );
}

function SocialButton({ href, icon: Icon, label }: { href: string; icon: SocialIcon; label: string }) {
    return (
        <Anchor href={href} className={`social-button ${href === '#' ? 'social-button--disabled' : ''}`}>
            <span className="social-button__icon">
                <Icon size={18} />
            </span>
            <span className="social-button__label">{label}</span>
        </Anchor>
    );
}

function Footer({ contacts: siteContacts }: { contacts: Contact }) {
    const year = new Date().getFullYear();

    return (
        <footer className="site-footer">
            <Container size="xl">
                <SimpleGrid cols={{ base: 1, sm: 2, lg: 5 }} spacing="xl">
                    <Stack gap="md">
                        <Image src="/assets/img/unique-logo.png" alt="ЮНИК С" className="footer-logo" />
                        <Text c="white" fw={700}>ООО “Юник С”</Text>
                        <Text c="gray.4" size="sm">ИНН: 4027139409</Text>
                        <Text c="gray.4" size="sm">ОГРН: 1194027002861</Text>
                    </Stack>

                    <Stack gap="sm">
                        <Text c="white" fw={700}>Разделы</Text>
                        {navItems.slice(1).map((item) => (
                            <Anchor key={item.href} component={Link} href={item.href} className="footer-link">
                                {item.label}
                            </Anchor>
                        ))}
                    </Stack>

                    <Stack gap="sm">
                        <Text c="white" fw={700}>Документы</Text>
                        <Anchor href="#" className="footer-link">Охрана труда</Anchor>
                        <Anchor href="#" className="footer-link">Политика конфиденциальности</Anchor>
                    </Stack>

                    <Stack gap="sm">
                        <Text c="white" fw={700}>Услуги</Text>
                        {demoServices.map((service) => (
                            <Anchor
                                key={service.id}
                                component={Link}
                                href={`/services/${service.slug}`}
                                className="footer-link"
                            >
                                {service.title}
                            </Anchor>
                        ))}
                    </Stack>

                    <Stack gap="md">
                        <Text c="white" fw={700}>Контакты</Text>
                        <Anchor href={phoneHref(siteContacts.phone)} className="footer-contact">
                            <span className="footer-contact__row">
                                <IconPhone size={18} className="footer-contact__icon" />
                                <span className="footer-contact__text">{siteContacts.phone}</span>
                            </span>
                        </Anchor>
                        <Anchor href={emailHref(siteContacts.email)} className="footer-contact">
                            <span className="footer-contact__row">
                                <IconMail size={18} className="footer-contact__icon" />
                                <span className="footer-contact__text">{siteContacts.email}</span>
                            </span>
                        </Anchor>
                        <span className="footer-contact">
                            <span className="footer-contact__row">
                                <IconMapPin size={18} className="footer-contact__icon" />
                                <span className="footer-contact__text">{siteContacts.address}</span>
                            </span>
                        </span>
                        <Group gap="xs">
                            <SocialButton href={siteContacts.socialLinks.max} icon={IconMessageCircle} label="Max" />
                            <SocialButton href={siteContacts.socialLinks.vk} icon={IconBrandVk} label="VK" />
                            <SocialButton href={siteContacts.socialLinks.telegram} icon={IconBrandTelegram} label="Telegram" />
                        </Group>
                    </Stack>
                </SimpleGrid>

                <Divider color="rgba(255,255,255,.14)" my="xl" />

                <Group justify="space-between" gap="md" className="footer-bottom">
                    <Text c="gray.5" size="sm">© “ЮНИК С” 2019-{year}</Text>
                    <Stack gap={8} align="flex-end">
                        <Group gap="xs" className="footer-request">
                            <TextInput placeholder="Ваш телефон" aria-label="Телефон для обратного звонка" />
                            <Button>Позвоните мне</Button>
                        </Group>
                        <Text size="sm" c="gray.5" className="footer-consent">
                            Нажимая кнопку соглашаюсь с{' '}
                            <Anchor href="#" className="footer-consent__link">
                                Политикой конфиденциальности
                            </Anchor>
                        </Text>
                    </Stack>
                </Group>
            </Container>
        </footer>
    );
}

export default function HomePage() {
    return (
        <>
            <Header contacts={contacts} />
            <main>
                <HeroSlider />
                <CompanySection />
                <LatestProducts />
                <ServicesSection />
                <BusinessEquipmentSection />
                <section className="search-band">
                    <Container size="xl">
                        <Group justify="space-between" gap="lg">
                            <Stack gap={4}>
                                <Title order={2}>Нужна конкретная позиция?</Title>
                                <Text>Оставьте запрос, и менеджер подберет подходящее оборудование.</Text>
                            </Stack>
                            <Button component={Link} href="/contacts" size="lg" leftSection={<IconSearch size={19} />}>
                                Оставить заявку
                            </Button>
                        </Group>
                    </Container>
                </section>
            </main>
            <Footer contacts={contacts} />
        </>
    );
}
