import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { YandexMap } from '@/components/contacts/YandexMap';
import { getOfficeMapLink, siteConfig } from '@/lib/site-config';
import { emailHref, phoneHref } from '@/lib/site-content';
import { formatCoordinates, formatOfficeAddress, type SiteContacts } from '@/lib/site-contacts';
import {
    Button,
    Container,
    Group,
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

type ContactsPageViewProps = {
    contacts: SiteContacts;
};

export function ContactsPageView({ contacts }: ContactsPageViewProps) {
    const mapLink = getOfficeMapLink(contacts);
    const contactCards = [
        {
            title: 'Телефон',
            value: contacts.phone,
            description: 'Свяжитесь с нами для консультации, подбора оборудования и обсуждения сделки.',
            href: phoneHref(contacts.phone),
            action: 'Позвонить',
            icon: IconPhone,
        },
        {
            title: 'Email',
            value: contacts.email,
            description: 'Отправьте запрос на подбор, коммерческое предложение или документы по сделке.',
            href: emailHref(contacts.email),
            action: 'Написать',
            icon: IconMail,
        },
        {
            title: 'Адрес офиса',
            value: formatOfficeAddress(contacts),
            description: 'Принимаем в офисе по предварительному согласованию времени визита.',
            href: mapLink,
            action: 'Открыть маршрут',
            icon: IconMapPin,
        },
    ];

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
                            <Button component="a" href={phoneHref(contacts.phone)} size="lg" leftSection={<IconPhone size={18} />}>
                                Позвонить
                            </Button>
                            <Button component="a" href={emailHref(contacts.email)} size="lg" variant="white" leftSection={<IconMail size={18} />}>
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
                                                        <b>{contacts.workSchedule}</b>
                                                        <span>{contacts.workSchedule2}</span>
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
                                                        <b>{contacts.address}</b>
                                                        <span>{contacts.address2}</span>
                                                    </div>
                                                </div>
                                                <div className="contacts-info-row">
                                                    <IconRoute size={20} />
                                                    <div>
                                                        <b>Координаты</b>
                                                        <span>{formatCoordinates(contacts)}</span>
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
                                                href={phoneHref(contacts.phone)}
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

                                    <YandexMap
                                        longitude={contacts.longitude}
                                        latitude={contacts.latitude}
                                        zoom={siteConfig.yandexMapsZoom}
                                        title={`Офис ЮНИК С: ${formatOfficeAddress(contacts)}`}
                                        className="contacts-map-card__media"
                                    />
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
