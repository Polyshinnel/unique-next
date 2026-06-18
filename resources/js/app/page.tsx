'use client';

import Link from 'next/link';
import { catalogProducts } from '@/lib/catalog-products';
import { demoServices } from '@/lib/site-content';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { ProductCard } from '@/components/catalog/ProductCard';
import { ServiceCard } from '@/components/services/ServiceCard';
import {
    Button,
    Container,
    Group,
    Image,
    SimpleGrid,
    Stack,
    Text,
    Title,
    UnstyledButton,
} from '@mantine/core';
import { IconArrowRight, IconChevronLeft, IconChevronRight, IconSearch } from '@tabler/icons-react';
import { useEffect, useState } from 'react';

const heroSlides = [
    {
        title: 'Продажа б/у оборудования',
        text: 'Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ.',
        action: 'В каталог',
        href: '/catalog',
        image: '/assets/img/slide-1.jpeg',
    },
    {
        title: 'Выкуп б/у оборудования',
        text: 'Осуществляем выкуп или берем на реализацию любое промышленное оборудование, производственные линии, станки с заводов, цехов, предприятий. Отталкиваемся от Ваших ценовых ожиданий. Производим оперативную оценку стоимости.',
        action: 'Подробнее',
        href: '/services',
        image: '/assets/img/slide-2.jpg',
    },
    {
        title: 'Поставка импортного оборудования',
        text: 'Ввоз и растаможка оборудования из стран ЕС и Азии, подготовка и оформление всех необходимых документов, организация транспорта. Мы - ваш российский поставщик оборудования.',
        action: 'Подробнее',
        href: '/services',
        image: '/assets/img/slide-3.jpg',
    },
    {
        title: 'Продажа инструмента',
        text: 'Оптовые поставки промышленного инструмента: металлорежущего, алмазного, абразивного, слесарного и прочего со склада. Профессиональная консультация. Отправка по всей территории РФ.',
        action: 'Подробнее',
        href: '/services',
        image: '/assets/img/slide-4.jpg',
    },
];

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
                    <Title order={1}>{slide.title}</Title>
                    <Text>{slide.text}</Text>
                    <Group gap="sm" className="hero-slider__actions">
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
                        <Title order={2}>Компания &quot;ЮНИК С&quot; - промышленные станки и оборудование</Title>
                        <Stack gap={12}>
                            <Text c="dimmed" size="lg">
                                Наша компания «Юник С» работает в сфере купли-продажи бывшего в употреблении
                                промышленного оборудования с 2019 года.
                            </Text>
                            <Text c="dimmed" size="lg">
                                У нас вы найдете станки и оборудование под любые технологические задачи.
                            </Text>
                            <Text c="dimmed" size="lg">
                                В случае необходимости поможем реализовать ваше оборудование.
                            </Text>
                            <Text c="dimmed" size="lg">
                                Также при необходимости проработаем варианты поставки импортного оборудования из
                                стран Азии и ЕС.
                            </Text>
                            <Text c="dimmed" size="lg">
                                На нашем сайте найдете информацию, где просто купить весь необходимый инструмент и
                                прочие расходники.
                            </Text>
                        </Stack>
                        <SimpleGrid cols={{ base: 1, sm: 3 }} spacing="md" mt="md">
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
                        <Image src="/assets/img/main.jpeg" alt="Промышленное оборудование ЮНИК С" />
                    </div>
                </div>
            </Container>
        </section>
    );
}

function LatestProducts() {
    return (
        <section className="content-section content-section--tight-top latest-products-section">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Title order={2}>Последние поступления</Title>
                        <Text c="dimmed">Примеры карточек товаров для будущего наполнения каталога.</Text>
                    </Stack>
                    <Button component={Link} href="/catalog" variant="outline" rightSection={<IconArrowRight size={18} />}>
                        Перейти в каталог
                    </Button>
                </Group>
                <SimpleGrid cols={{ base: 1, sm: 2, lg: 4 }} spacing="lg">
                    {catalogProducts.map((product) => (
                        <ProductCard key={product.id} product={product} />
                    ))}
                </SimpleGrid>
            </Container>
        </section>
    );
}

function ServicesSection() {
    return (
        <section className="content-section content-section--white content-section--tight-top services-section">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Title order={2}>Услуги</Title>
                        <Text c="dimmed">Основные направления работы с оборудованием и сделками.</Text>
                    </Stack>
                    <Button component={Link} href="/services" variant="outline" rightSection={<IconArrowRight size={18} />}>
                        Все услуги
                    </Button>
                </Group>
                <SimpleGrid cols={{ base: 1, sm: 2, xl: 4 }} spacing="lg">
                    {demoServices.map((service) => (
                        <ServiceCard key={service.id} service={service} />
                    ))}
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

export default function HomePage() {
    return (
        <>
            <Header />
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
            <Footer />
        </>
    );
}
