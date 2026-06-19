import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { getOfficeMapEmbedUrl, getOfficeMapImageUrl, getOfficeMapLink } from '@/lib/site-config';
import { emailHref, phoneHref, siteContacts } from '@/lib/site-content';
import {
    Anchor,
    Button,
    Container,
    Group,
    Image,
    SimpleGrid,
    Stack,
    Text,
    Title,
} from '@mantine/core';
import {
    IconClockHour4,
    IconMail,
    IconMapPin,
    IconPhone,
    IconRoute,
} from '@tabler/icons-react';

const mapImageUrl = getOfficeMapImageUrl();
const mapEmbedUrl = getOfficeMapEmbedUrl();
const mapLink = getOfficeMapLink();

const contactCards = [
    {
        title: 'Телефон',
        value: siteContacts.phone,
        description: 'Свяжитесь с нами для консультации, подбора оборудования и обсуждения сделки.',
        href: phoneHref(siteContacts.phone),
        action: 'Позвонить',
        icon: IconPhone,
    },
    {
        title: 'Email',
        value: siteContacts.email,
        description: 'Отправьте запрос на подбор, коммерческое предложение или документы по сделке.',
        href: emailHref(siteContacts.email),
        action: 'Написать',
        icon: IconMail,
    },
    {
        title: 'Адрес офиса',
        value: siteContacts.address,
        description: 'Принимаем в офисе по предварительному согласованию времени визита.',
        href: mapLink,
        action: 'Открыть маршрут',
        icon: IconMapPin,
    },
];

export function ContactsPageView() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero contacts-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <span>Контакты</span>
                        </div>
                        <Title order={1}>Контакты</Title>
                        <Text size="lg">
                            Поможем с подбором, покупкой, реализацией и поставкой промышленного оборудования.
                            Свяжитесь с нами удобным способом или приезжайте в офис в Калуге.
                        </Text>
                        <Group mt="xl" gap="sm">
                            <Button component="a" href={phoneHref(siteContacts.phone)} size="lg" leftSection={<IconPhone size={18} />}>
                                Позвонить
                            </Button>
                            <Button component="a" href={emailHref(siteContacts.email)} size="lg" variant="white" leftSection={<IconMail size={18} />}>
                                Написать на email
                            </Button>
                        </Group>
                    </Container>
                </section>

                <section className="content-section contacts-section">
                    <Container size="xl">
                        <Stack gap="xl">
                            <SimpleGrid cols={{ base: 1, md: 3 }} spacing="lg">
                                {contactCards.map(({ title, value, description, href, action, icon: Icon }) => (
                                    <article key={title} className="contact-card">
                                        <span className="contact-card__icon">
                                            <Icon size={22} />
                                        </span>
                                        <Stack gap="sm" className="contact-card__content">
                                            <Text className="contact-card__eyebrow">{title}</Text>
                                            <Text className="contact-card__value">{value}</Text>
                                            <Text c="dimmed">{description}</Text>
                                        </Stack>
                                        <Button
                                            component="a"
                                            href={href}
                                            className="contact-card__link"
                                            color="green"
                                            variant="filled"
                                        >
                                            {action}
                                        </Button>
                                    </article>
                                ))}
                            </SimpleGrid>

                            <div className="contacts-layout">
                                <section className="contacts-panel">
                                    <Stack gap="xl">
                                        <div>
                                            <Text className="contact-card__eyebrow">Режим работы</Text>
                                            <div className="contacts-info-list">
                                                <div className="contacts-info-row">
                                                    <IconClockHour4 size={20} />
                                                    <div>
                                                        <b>{siteContacts.workingHours.weekdays}</b>
                                                        <span>{siteContacts.workingHours.weekends}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <Text className="contact-card__eyebrow">Как нас найти</Text>
                                            <div className="contacts-info-list">
                                                <div className="contacts-info-row">
                                                    <IconMapPin size={20} />
                                                    <div>
                                                        <b>{siteContacts.shortAddress}</b>
                                                        <span>248002, 3 этаж, офисы 313, 314</span>
                                                    </div>
                                                </div>
                                                <div className="contacts-info-row">
                                                    <IconRoute size={20} />
                                                    <div>
                                                        <b>Координаты</b>
                                                        <span>
                                                            {siteContacts.coordinates.longitude}, {siteContacts.coordinates.latitude}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="contacts-note">
                                            <Title order={3}>Поможем быстро сориентироваться</Title>
                                            <Text c="dimmed">
                                                Если вам удобнее начать с короткого звонка или письма, мы уточним задачу
                                                и подскажем дальнейший формат работы без лишних шагов.
                                            </Text>
                                            <Button
                                                component="a"
                                                href={phoneHref(siteContacts.phone)}
                                                className="contacts-note__button"
                                                color="green"
                                                leftSection={<IconPhone size={18} />}
                                            >
                                                Свяжитесь со мной
                                            </Button>
                                        </div>
                                    </Stack>
                                </section>

                                <section className="contacts-map-card">
                                    <div className="contacts-map-card__header">
                                        <div>
                                            <Text className="contact-card__eyebrow">Карта</Text>
                                            <Title order={2}>Офис ЮНИК С в Калуге</Title>
                                        </div>
                                        <Button
                                            component="a"
                                            href={mapLink}
                                            target="_blank"
                                            rel="noreferrer"
                                            variant="outline"
                                            rightSection={<IconRoute size={18} />}
                                        >
                                            Построить маршрут
                                        </Button>
                                    </div>

                                    {mapImageUrl ? (
                                        <Anchor href={mapLink} target="_blank" rel="noreferrer" className="contacts-map-card__media">
                                            <Image
                                                src={mapImageUrl}
                                                alt={`Карта офиса ЮНИК С: ${siteContacts.address}`}
                                            />
                                        </Anchor>
                                    ) : (
                                        <div className="contacts-map-card__fallback">
                                            <iframe
                                                title={`Карта офиса ЮНИК С: ${siteContacts.address}`}
                                                src={mapEmbedUrl}
                                                className="contacts-map-card__iframe"
                                                loading="lazy"
                                                referrerPolicy="no-referrer-when-downgrade"
                                            />
                                        </div>
                                    )}
                                </section>
                            </div>
                        </Stack>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
