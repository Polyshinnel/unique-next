import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { AboutTabs } from '@/components/about/AboutTabs';
import { Badge, Button, Container, Group, Image, SimpleGrid, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';

const coverageRegions = [
    'ЦФО: Калужская, Смоленская, Тульская, Воронежская, Брянская области, а также Москва и Московская область.',
    'ПФО: Республики Марий Эл, Башкортостан, Удмуртия, Кировская, Самарская, Нижегородская области.',
    'ЮФО: Волгоградская область.',
    'УрФО: Челябинская область.',
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
                        <Stack gap="lg" maw={760}>
                            <Title order={1}>О компании</Title>
                            <Text size="lg">
                                ЮНИК С помогает промышленным компаниям решать сложные задачи в поставках, оборудовании и сопровождении проектов по всей России.
                            </Text>
                            <Group gap="md">
                                <Button component="a" href="/why-we" size="lg">
                                    Почему мы
                                </Button>
                                <Button component="a" href="/vacancy" size="lg" variant="white" color="dark">
                                    Вакансии
                                </Button>
                            </Group>
                        </Stack>
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

                            <AboutTabs />
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
                                        <Button component="a" href={item.href} rightSection={<IconArrowRight size={18} />}>
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
