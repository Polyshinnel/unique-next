import Link from 'next/link';
import { SocialProofSection } from '@/components/common/SocialProofSection';
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
    IconBrandTelegram,
    IconMail,
    IconMessageCircle,
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
            title: 'Телефон и Email',
            value: contacts.phone,
            description: 'Свяжитесь с нами для консультации, подбора оборудования и обсуждения сделки.',
            href: phoneHref(contacts.phone),
            action: 'Позвонить',
            icon: IconPhone,
        },
        {
            title: 'Мессенджеры',
            value: '',
            description: 'Выберите удобный мессенджер — ответим на вопросы и поможем с подбором оборудования.',
            href: '',
            action: '',
            icon: IconMessageCircle,
            isMessenger: true,
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
                        <Group mt="xl" gap="sm" wrap="wrap">
                            <Button component="a" href={phoneHref(contacts.phone)} size="lg" leftSection={<IconPhone size={18} />}>
                                Позвонить
                            </Button>
                            <Button component="a" href={emailHref(contacts.email)} size="lg" variant="white" leftSection={<IconMail size={18} />}>
                                Написать на email
                            </Button>
                            <Button
                                component="a"
                                href="https://max.ru/u/f9LHodD0cOIo9EF4dyFLsLTpWWuc1m9Gprh6sJZhyD3Bu0dKezDRd_uEBqA"
                                target="_blank"
                                rel="noreferrer"
                                size="lg"
                                className="contacts-hero__max-button"
                                leftSection={<IconMessageCircle size={18} />}
                            >
                                Написать в MAX
                            </Button>
                            <Button
                                component="a"
                                href="https://t.me/uniqset_catalog"
                                target="_blank"
                                rel="noreferrer"
                                size="lg"
                                className="contacts-hero__telegram-button"
                                leftSection={<IconBrandTelegram size={18} />}
                            >
                                Написать в Telegram
                            </Button>
                        </Group>
                    </Container>
                </section>

                <section className="content-section contacts-section">
                    <Container size="xl">
                        <Stack gap="xl">
                            <Title order={2} className="visually-hidden">Контактная информация</Title>
                            <SimpleGrid cols={{ base: 1, md: 3 }} spacing="lg">
                                {contactCards.map(({ title, value, description, href, action, icon: Icon, isMessenger }) => (
                                    <article key={title} className="contact-card">
                                        <span className="contact-card__icon">
                                            <Icon size={22} />
                                        </span>
                                        <Stack gap="sm" className="contact-card__content">
                                            <Title order={3} className="visually-hidden">{title}</Title>
                                            <Text className="contact-card__eyebrow" aria-hidden="true">{title}</Text>
                                            {isMessenger ? (
                                                <Stack gap="sm" className="contact-card__messenger-actions">
                                                    <Text className="contact-card__value">MAX и Telegram</Text>
                                                    <Text c="dimmed">{description}</Text>
                                                    <Button
                                                        component="a"
                                                        href="https://t.me/uniqset_catalog"
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="contact-card__messenger-button contact-card__messenger-button--telegram"
                                                        leftSection={<IconBrandTelegram size={18} />}
                                                    >
                                                        Написать в Telegram
                                                    </Button>
                                                    <Button
                                                        component="a"
                                                        href="https://max.ru/u/f9LHodD0cOIo9EF4dyFLsLTpWWuc1m9Gprh6sJZhyD3Bu0dKezDRd_uEBqA"
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="contact-card__messenger-button contact-card__messenger-button--max"
                                                        leftSection={<IconMessageCircle size={18} />}
                                                    >
                                                        Написать в MAX
                                                    </Button>
                                                </Stack>
                                            ) : (
                                                <>
                                                    <Text className="contact-card__value">{value}</Text>
                                                    {title === 'Телефон и Email' && (
                                                        <Text component="a" href={emailHref(contacts.email)} className="contact-card__secondary-value">
                                                            {contacts.email}
                                                        </Text>
                                                    )}
                                                    <Text c="dimmed">{description}</Text>
                                                </>
                                            )}
                                        </Stack>
                                        {!isMessenger && (
                                            <Button
                                                component="a"
                                                href={href}
                                                className="contact-card__link"
                                                color="teal"
                                                variant="filled"
                                            >
                                                {action}
                                            </Button>
                                        )}
                                    </article>
                                ))}
                            </SimpleGrid>

                            <div className="contacts-layout">
                                <section className="contacts-panel">
                                    <Stack gap="xl">
                                        <Title order={3} className="visually-hidden">Режим работы и навигация</Title>
                                        <div>
                                            <Title order={4} className="visually-hidden">Режим работы</Title>
                                            <Text className="contact-card__eyebrow" aria-hidden="true">Режим работы</Text>
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
                                            <Title order={4} className="visually-hidden">Как нас найти</Title>
                                            <Text className="contact-card__eyebrow" aria-hidden="true">Как нас найти</Text>
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
                                            <Title order={4}>Форма обратной связи</Title>
                                            <Text c="dimmed">
                                                Опишите коротко свой запрос, вопрос или предложение и мы ответим вам в ближайшее время
                                            </Text>
                                            <Button
                                                component="a"
                                                href={phoneHref(contacts.phone)}
                                                className="contacts-note__button"
                                                color="teal"
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
                                            <Title order={3}>Офис ЮНИК С в Калуге</Title>
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

                <SocialProofSection />
            </main>
            <Footer />
        </>
    );
}
