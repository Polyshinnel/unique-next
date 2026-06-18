'use client';

import { useState } from 'react';
import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { Badge, Button, Container, Group, Image, SimpleGrid, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';

const coverageRegions = [
    'ЦФО: Калужская, Смоленская, Тульская, Воронежская, Брянская области, а также Москва и Московская область.',
    'ПФО: Республики Марий Эл, Башкортостан, Удмуртия, Кировская, Самарская, Нижегородская области.',
    'ЮФО: Волгоградская область.',
    'УрФО: Челябинская область.',
];

const aboutTabs = [
    {
        id: 'expertise',
        label: 'Мы умеем',
        title: 'Знаем, как устроен этот рынок',
        text: 'Мы уже более 5 лет работаем в сфере купли-продажи промышленного оборудования, импорта и поставки оборудования и промышленного инструмента. У нас большая клиентская база по всей территории РФ. А также всегда более 250 единиц оборудования в реализации. Мы обладаем всеми необходимыми компетенциями и инструментами для успешной работы в данной сфере.',
    },
    {
        id: 'values',
        label: 'Мы ценим',
        title: 'Ценим наших клиентов и поставщиков',
        text: 'Среди наших партнеров иностранные и российские компании. Наши основные клиенты - это крупные, средние и малые производственные компании по всей территории РФ. Работаем без НДС и с НДС. Даем гарантии там, где мы можем их дать. Всегда платим НДС, исполняем сроки и обязательства. Наши обязательства - это главный фокус в нашей работе.',
    },
    {
        id: 'honesty',
        label: 'Мы работаем',
        title: 'Работаем прозрачно и честно',
        text: 'Больше всего мы ценим прозрачность и честность в нашей работе. Именно поэтому мы стараемся сделать нашу работу максимально прозрачной: всегда предоставим всю необходимую информацию по компании, бухгалтерскую и финансовую отчетность по запросу. Также вы можете увидеть наши регулярные отгрузки или почитать отзывы о нас в социальных сетях.',
    },
    {
        id: 'support',
        label: 'Мы организуем',
        title: 'Организуем сопутствующие вопросы',
        text: 'У нас большой опыт по организации демонтажных работ, есть проверенные подрядчики, при необходимости привлечем спецтехнику. Через наших партнеров и через АТИ организуем любой транспорт в любую точку РФ и за ее границы. При поставке инструмента сделаем техническую документацию, проработаем вопросы закупки инструмента и привоза из-за границы: налаженные связи с поставщиками в КНР, логистика и таможня.',
    },
];

const bottomLinks = [
    {
        title: 'Почему мы',
        text: 'Рассказываем, почему нам доверяют поставки, выкуп и сопровождение промышленных проектов.',
        href: '/why-we',
        badge: 'Преимущества',
    },
    {
        title: 'Вакансии',
        text: 'Если вам близок наш подход к работе, посмотрите актуальные карьерные возможности.',
        href: '/vacancy',
        badge: 'Команда',
    },
];

export function AboutPageView() {
    const [activeTab, setActiveTab] = useState(aboutTabs[0].id);
    const currentTab = aboutTabs.find((tab) => tab.id === activeTab) ?? aboutTabs[0];

    return (
        <>
            <Header />
            <main>
                <section className="page-hero about-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <span>О компании</span>
                        </div>
                        <Title order={1}>О компании</Title>
                        <Text size="lg">
                            ЮНИК С помогает промышленным компаниям решать сложные задачи в поставках, оборудовании и сопровождении проектов по всей России.
                        </Text>
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="xl">
                        <div className="about-company-grid">
                            <Stack gap="lg">
                                <Title order={2}>Сильная инженерная и проектная база</Title>
                                <Stack gap={12}>
                                    <Text c="dimmed" size="lg">
                                        Наша компания Юник С появилась благодаря более чем 10-летнему опыту работы в сфере инжиниринга и управления проектами с российскими и иностранными промышленными компаниями.
                                    </Text>
                                    <Text c="dimmed" size="lg">
                                        Мы любим сложные задачи, умеем их структурировать и раскладывать на простые, приоритезировать и успешно решать.
                                    </Text>
                                    <Text c="dimmed" size="lg">
                                        Наше главное преимущество - это наши компетенции, которые ежедневно помогают нам при решении абсолютно любых задач.
                                    </Text>
                                </Stack>
                            </Stack>

                            <div className="about-company-image">
                                <Image src="/assets/img/about/main.jpeg" alt="Команда ЮНИК С" />
                            </div>
                        </div>
                    </Container>
                </section>

                <section className="content-section">
                    <Container size="xl">
                        <blockquote className="about-quote-card">
                            <p>
                                Компания «Юник С» - ваш надежный партнер на рынке промышленного оборудования,
                                инструмента и спецоснастки.
                            </p>
                            <footer>Денис Конюков, генеральный директор</footer>
                        </blockquote>
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="xl">
                        <div className="about-map-layout">
                            <Stack gap="lg">
                                <Title order={2}>География нашей работы</Title>
                                <Text c="dimmed" size="lg">
                                    Главный офис нашей компании находится в г. Калуга. Мы продаем оборудование и инструмент по всей территории России: от финской границы до Дальнего Востока. Все оборудование, находящееся у нас в работе, представлено в следующих регионах:
                                </Text>
                                <div className="about-region-list">
                                    {coverageRegions.map((region) => (
                                        <div key={region} className="about-region-item">
                                            <span className="about-region-item__dot" />
                                            <Text>{region}</Text>
                                        </div>
                                    ))}
                                </div>
                            </Stack>

                            <div className="about-map-card">
                                <div className="about-map-card__image">
                                    <Image src="/assets/img/about/map.jpg" alt="Карта регионов поставок ЮНИК С" />
                                </div>
                                <Text className="about-map-card__caption">
                                    У нас в офисе висит карта, где мы отмечаем новые города, куда мы отправили оборудование.
                                </Text>
                            </div>
                        </div>
                    </Container>
                </section>

                <section className="content-section">
                    <Container size="xl">
                        <Stack gap="xl">
                            <Stack gap="sm" maw={840}>
                                <Title order={2}>Как мы работаем</Title>
                                <Text c="dimmed" size="lg">
                                    Четыре принципа, на которых строится наша ежедневная работа с клиентами, поставщиками и логистикой.
                                </Text>
                            </Stack>

                            <div className="about-tabs">
                                <div className="about-tabs__list" role="tablist" aria-label="Блок о компании">
                                    {aboutTabs.map((tab) => (
                                        <button
                                            key={tab.id}
                                            type="button"
                                            role="tab"
                                            aria-selected={tab.id === currentTab.id}
                                            className={`about-tabs__button ${tab.id === currentTab.id ? 'about-tabs__button--active' : ''}`}
                                            onClick={() => setActiveTab(tab.id)}
                                        >
                                            {tab.label}
                                        </button>
                                    ))}
                                </div>

                                <div className="about-tabs__panel" role="tabpanel">
                                    <Badge variant="light" color="blue">{currentTab.label}</Badge>
                                    <Title order={3}>{currentTab.title}</Title>
                                    <Text c="dimmed" size="lg">{currentTab.text}</Text>
                                </div>
                            </div>
                        </Stack>
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="xl">
                        <SimpleGrid cols={{ base: 1, md: 2 }} spacing="lg">
                            {bottomLinks.map((item) => (
                                <article key={item.href} className="about-link-card">
                                    <Badge variant="light" color="orange">{item.badge}</Badge>
                                    <Title order={3}>{item.title}</Title>
                                    <Text c="dimmed">{item.text}</Text>
                                    <Group style={{ marginTop: 'auto' }}>
                                        <Button component={Link} href={item.href} rightSection={<IconArrowRight size={18} />}>
                                            Перейти
                                        </Button>
                                    </Group>
                                </article>
                            ))}
                        </SimpleGrid>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
