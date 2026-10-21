import type { Metadata } from 'next';
import Link from 'next/link';
import { FeedbackRequestModal } from '@/components/common/FeedbackRequestModal';
import { canonicalUrl, getPageSeo, toMetadata } from '@/lib/seo';
import { phoneHref } from '@/lib/site-content';
import { getSiteContacts } from '@/lib/site-contacts';
import { ProductGallery } from '@/components/catalog/ProductGallery';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { PageStructuredData } from '@/components/seo/OrganizationJsonLd';
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
    IconCertificate,
    IconCheck,
    IconMail,
    IconMapPin,
    IconPhone,
    IconTruckDelivery,
} from '@tabler/icons-react';
import type { TablerIcon } from '@tabler/icons-react';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('tools_sale');

    return toMetadata(seo, {
        title: 'Продажа инструмента | ЮНИК С',
        description: 'Инструмент от компании Коникс: поставка со склада в Йошкар-Оле, консультации, прайс и отправка по России.',
        alternates: {
            canonical: canonicalUrl('/services/prodazha-instrumenta'),
        },
    });
}

const galleryImages = Array.from({ length: 15 }, (_, index) => `/assets/img/services/instrument-page/${index + 1}.webp`);

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
export default async function ToolSalesPage() {
    const contacts = await getSiteContacts();

    return (
        <>
            <PageStructuredData service seoKey="tools_sale" path="/services/prodazha-instrumenta" fallbackTitle="Продажа инструмента | ЮНИК С" fallbackDescription="Инструмент от компании Коникс: поставка со склада в Йошкар-Оле, консультации, прайс и отправка по России." />
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
                                <Button component="a" href={phoneHref(contacts.phone)} size="lg" leftSection={<IconPhone size={18} />}>
                                    Позвонить
                                </Button>
                                <FeedbackRequestModal
                                    buttonLabel="Оставить заявку"
                                    modalTitle="Закажите обратный звонок"
                                    description="Оставьте контакты, и мы перезвоним вам, чтобы обсудить продажу инструмента."
                                    buttonVariant="white"
                                    buttonColor="dark"
                                    buttonLeftSection={null}
                                />
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
                                mobileSlider
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

                            <section className="instrument-card instrument-shipping-card">
                                <Stack gap="lg">
                                    <Title order={2}>Отгрузка и условия поставки</Title>
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

                                </Stack>
                            </section>

                            <section className="instrument-card instrument-contact-card">
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
                                    <FeedbackRequestModal
                                        buttonLabel="Запросить актуальный прайс"
                                        modalTitle="Запрос актуального прайса"
                                        description="Оставьте контакты, и мы отправим вам актуальный прайс на инструмент."
                                        initialMessage="Прошу отправить актуальный прайс на инструмент."
                                        buttonVariant="default"
                                        buttonLeftSection={<IconCheck size={18} />}
                                    />
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
