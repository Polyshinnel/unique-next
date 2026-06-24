import type { Metadata } from 'next';
import Link from 'next/link';
import { ProductGallery } from '@/components/catalog/ProductGallery';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { ToolCategoriesSection } from './components/ToolCategoriesSection';
import { SuppliersSection } from './components/SuppliersSection';
import {
    Button,
    Container,
    Group,
    Stack,
    Text,
    Title,
} from '@mantine/core';
import {
    IconBrandTelegram,
    IconCertificate,
    IconCheck,
    IconMail,
    IconMapPin,
    IconPhone,
    IconTruckDelivery,
} from '@tabler/icons-react';
import type { TablerIcon } from '@tabler/icons-react';

export const metadata: Metadata = {
    title: 'Продажа инструмента | ЮНИК С',
    description: 'Инструмент от компании Коникс: поставка со склада в Йошкар-Оле, консультации, прайс и отправка по России.',
};

const galleryImages = Array.from({ length: 15 }, (_, index) => `/assets/img/services/instrument-page/${index + 1}.jpg`);

const contactItems: ReadonlyArray<{
    icon: TablerIcon;
    text: string;
    href?: string;
}> = [
    {
        icon: IconMail,
        text: 'Отправляйте заявки в любой свободной форме на e-mail: konics@mail.ru',
        href: 'mailto:konics@mail.ru',
    },
    {
        icon: IconPhone,
        text: 'Звоните и получайте консультации по тел./ф.: 8 (8362) 64-14-00, 64-18-55',
        href: 'tel:+78362641400',
    },
    {
        icon: IconCertificate,
        text: 'С полной номенклатурой и ценами можно ознакомиться в актуальном прайсе по запросу.',
    },
] as const;

const priceRequestHref = 'mailto:konics@mail.ru?subject=%D0%97%D0%B0%D0%BF%D1%80%D0%BE%D1%81%20%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE%D0%B3%D0%BE%20%D0%BF%D1%80%D0%B0%D0%B9%D1%81%D0%B0';
const telegramHref = 'https://telegram.me/uniqset_gen';

export default function ToolSalesPage() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero catalog-hero instrument-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <Link href="/services">Услуги</Link>
                            <span>/</span>
                            <span>Продажа инструмента</span>
                        </div>
                        <Stack gap="lg" maw={900}>
                            <Title order={1}>Продажа инструмента</Title>
                            <Text size="lg">
                                В данном разделе представлен инструмент от наших надежных партнеров, фирмы
                                &nbsp;«Коникс». Компания профессионально занимается инструментом с 1997 года.
                            </Text>
                            <Group gap="md">
                                <Button component="a" href={priceRequestHref} size="lg" leftSection={<IconMail size={18} />}>
                                    Запросить актуальный прайс
                                </Button>
                                <Button
                                    component="a"
                                    href={telegramHref}
                                    target="_blank"
                                    rel="noreferrer"
                                    size="lg"
                                    variant="white"
                                    color="dark"
                                    leftSection={<IconBrandTelegram size={18} />}
                                >
                                    Написать в Telegram
                                </Button>
                            </Group>
                        </Stack>
                    </Container>
                </section>

                <section className="content-section instrument-section">
                    <Container size="xl">
                        <div className="instrument-layout">
                            <ToolCategoriesSection />

                            <ProductGallery
                                title="Продажа инструмента"
                                images={galleryImages}
                            />

                            <section className="sales-quote-section content-section--tight-top">
                                <blockquote className="sales-quote-card instrument-quote-card">
                                    <p>
                                        Инструмент из качественных инструментальных сплавов Р6М5, ВК8, Т5К10, TiAlN и
                                        многих других
                                    </p>
                                </blockquote>
                            </section>

                            <SuppliersSection />

                            <section className="instrument-card">
                                <Stack gap="lg">
                                    <div className="instrument-side-note">
                                        <span className="instrument-side-note__icon">
                                            <IconTruckDelivery size={22} />
                                        </span>
                                        <div>
                                            <Title order={3}>Отгрузка и логистика</Title>
                                            <Text c="dimmed">
                                                Инструмент продается со склада в г. Йошкар-Оле, отправка по России
                                                возможна при заказе от 5 т.р.
                                            </Text>
                                        </div>
                                    </div>

                                    <div className="instrument-side-note">
                                        <span className="instrument-side-note__icon">
                                            <IconMapPin size={22} />
                                        </span>
                                        <div>
                                            <Title order={3}>Формат поставки</Title>
                                            <Text c="dimmed">
                                                Поможем подобрать позиции под задачу, уточним наличие, сроки и удобный
                                                вариант оплаты.
                                            </Text>
                                        </div>
                                    </div>

                                    <Button component="a" href={priceRequestHref} size="lg">
                                        Запросить актуальный прайс
                                    </Button>
                                </Stack>
                            </section>

                            <section className="instrument-card">
                                <Stack gap="lg">
                                    <Title order={2}>Пишите, звоните, задавайте вопросы</Title>
                                    <Stack gap="sm">
                                        {contactItems.map((item) => {
                                            const Icon = item.icon;

                                            return (
                                                <div key={item.text} className="instrument-contact-row">
                                                    <Icon size={18} />
                                                    {item.href ? (
                                                        <a href={item.href}>{item.text}</a>
                                                    ) : (
                                                        <Text>{item.text}</Text>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </Stack>
                                    <Button
                                        component="a"
                                        href={priceRequestHref}
                                        size="lg"
                                        variant="default"
                                        leftSection={<IconCheck size={18} />}
                                    >
                                        Запросить актуальный прайс
                                    </Button>
                                </Stack>
                            </section>
                        </div>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
