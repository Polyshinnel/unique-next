import type { Metadata } from 'next';
import Link from 'next/link';
import ImageView from 'next/image';
import { getPageSeo, toMetadata } from '@/lib/seo';
import { catalogProducts } from '@/lib/catalog-products';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { ProductCard } from '@/components/catalog/ProductCard';
import { SummarySection } from './components/SummarySection';
import {
    Badge,
    Button,
    Container,
    Group,
    SimpleGrid,
    Stack,
    Text,
    Title,
} from '@mantine/core';
import {
    IconArrowRight,
    IconBrandTelegram,
    IconExternalLink,
    IconFileDescription,
    IconMessageCircle,
    IconPhotoScan,
    IconReceiptTax,
    IconShieldCheck,
    IconTruckDelivery,
    IconVideo,
} from '@tabler/icons-react';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('equipment_sale');

    return toMetadata(seo, {
        title: 'Продажа оборудования | ЮНИК С',
        description: 'Продажа б/у промышленного оборудования по всей России с понятным состоянием, сопровождением сделки и логистики.',
    });
}

const catalogHref = '/catalog';
const telegramHref = 'https://telegram.me/uniqset_gen';

const advantages = [
    {
        icon: IconReceiptTax,
        title: 'Надежного партнера',
        text: 'Мы платим НДС, по запросу предоставим бухгалтерскую отчетность и уставные документы. Берем на себя ответственность и соблюдаем договоренности.',
    },
    {
        icon: IconPhotoScan,
        title: 'Понятное состояние',
        text: 'Предоставим всю имеющуюся у нас информацию, а так же при необходимости сделаем доп. фото или покажем по видео-связи. Организуем проверку в работе.',
    },
    {
        icon: IconTruckDelivery,
        title: 'Минимум головной боли',
        text: 'Прикинем стоимость доставки или демонтажа. Найдем машину или подрядчика на ваших условиях. Проконтролируем и отчитаемся о ходе демонтажа и погрузки.',
    },
];

const alreadyDoneList = [
    'Осмотрели и оценили оборудование',
    'Сделали общие фотографии, а также отдельные основных узлов и агрегатов',
    'Сняли видео в работе',
    'Обговорили все условия и сроки с компанией-поставщиком',
    'Заморозили цену и сделали любую удобную форму расчета: безнал с НДС или без НДС',
    'Согласовали процедуру вывода оборудования с баланса',
    'Оформили наши отношения с поставщиком ОФИЦИАЛЬНО',
    'Проработали сопутствующие вопросы, касающиеся демонтажа и погрузки',
    'Зачастую, уже выкупили ту или иную единицу оборудования',
];

const socialCards = [
    {
        badge: 'ВКонтакте',
        badgeColor: 'orange',
        title: 'Наша официальная группа во ВКонтакте',
        description: 'Публикуем новые поступления, свежие кейсы по отгрузкам и показываем оборудование в работе.',
        href: 'https://vk.com/uniqset',
        action: 'Перейти во ВКонтакте',
        qrSrc: '/assets/img/qr-vk-uniqset.svg',
        qrAlt: 'QR-код для перехода в группу ЮНИК С во ВКонтакте',
    },
    {
        badge: 'Авито',
        badgeColor: 'blue',
        title: 'Профиль компании и отзывы на Авито',
        description: 'Смотрите актуальные объявления, переходите к отзывам и оценивайте наш профиль перед обращением.',
        href: 'https://www.avito.ru/brands/i182086396',
        action: 'Открыть Авито',
        qrSrc: '/assets/img/qr-avito-uniqset.svg',
        qrAlt: 'QR-код для перехода в профиль ЮНИК С на Авито',
    },
] as const;

const remotePurchaseSupport = [
    'Дополнительные фото и видео конкретных узлов по запросу',
    'Показы состояния узлов и агрегатов по видео связи',
    'Демонстрации в работе по видео через мессенджеры',
];

const deliverySupport = [
    'У нас есть постоянные партнеры – перевозчики',
    'Подберем машину сборным или попутным грузом через АТИ (биржа перевозок)',
    'Отправить транспортной компанией',
];

function HeroSection() {
    return (
        <section className="page-hero sales-hero">
            <Container size="xl">
                <div className="catalog-breadcrumbs">
                    <Link href="/">Главная</Link>
                    <span>/</span>
                    <Link href="/services">Услуги</Link>
                    <span>/</span>
                    <span>Продажа оборудования</span>
                </div>
                <Stack gap="lg" align="flex-start">
                    <Title order={1}>Продажа оборудования</Title>
                    <Text size="lg" maw={900}>
                        Все оборудование, которое мы берем в работу, сразу же попадает на наш каталог. Все оборудование,
                        представленное в каталоге актуальное, для того, чтобы ознакомиться с полным перечнем переходите
                        по кнопке ниже
                    </Text>
                    <Group gap="md">
                        <Button component="a" href={catalogHref} size="lg" rightSection={<IconArrowRight size={18} />}>
                            В каталог
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
                            Telegram
                        </Button>
                    </Group>
                </Stack>
            </Container>
        </section>
    );
}

function LatestProductsSection() {
    return (
        <section className="content-section content-section--tight-top latest-products-section">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Title order={2}>Последние поступления</Title>
                        <Text c="dimmed">Свежие новинки из нашего каталога.</Text>
                    </Stack>
                    <Button component="a" href={catalogHref} variant="filled" className="latest-products-section__button" rightSection={<IconArrowRight size={18} />}>
                        Перейти в каталог
                    </Button>
                </Group>
                <SimpleGrid cols={{ base: 1, sm: 2, lg: 4 }} spacing="lg">
                    {catalogProducts.slice(0, 4).map((product) => (
                        <ProductCard key={product.id} product={product} />
                    ))}
                </SimpleGrid>
            </Container>
        </section>
    );
}

function SloganSection() {
    return (
        <section className="content-section content-section--tight-top sales-quote-section">
            <Container size="xl">
                <blockquote className="sales-quote-card">
                    <p>
                        ООО «Юник С» – торгующая компания, мы занимаемся реализацией б.у. оборудования от
                        производственных предприятий на договорных условиях
                    </p>
                </blockquote>
            </Container>
        </section>
    );
}

function AdvantagesSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Stack gap="xl">
                    <Stack gap={6}>
                        <Title order={2}>Что вы получите, покупая оборудование у нас:</Title>
                    </Stack>

                    <SimpleGrid cols={{ base: 1, md: 3 }} spacing="lg">
                        {advantages.map((item) => {
                            const Icon = item.icon;

                            return (
                                <article key={item.title} className="sales-advantage-card">
                                    <span className="sales-advantage-card__icon">
                                        <Icon size={24} />
                                    </span>
                                    <Title order={3}>{item.title}</Title>
                                    <Text c="dimmed">{item.text}</Text>
                                </article>
                            );
                        })}
                    </SimpleGrid>
                </Stack>
            </Container>
        </section>
    );
}

function SocialSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Stack gap="xl">
                    <Stack gap={6}>
                        <Title order={2}>Где можно посмотреть нас ближе</Title>
                        <Text c="dimmed" maw={760}>
                            Переходите в наши публичные каналы, чтобы увидеть новые предложения, отзывы и реальные кейсы.
                        </Text>
                    </Stack>

                    <div className="otgruzki-follow-summary">
                        <Badge variant="filled" color="orange" size="lg">97%</Badge>
                        <Text size="lg">
                            <strong>97% клиентов довольны нашей работой</strong> и возвращаются к нам за следующими
                            сделками или рекомендуют нас коллегам.
                        </Text>
                    </div>

                    <SimpleGrid cols={{ base: 1, lg: 2 }} spacing="lg">
                        {socialCards.map((card) => (
                            <article key={card.title} className="otgruzki-follow-card">
                                <div className="otgruzki-follow-card__content">
                                    <div className="otgruzki-follow-card__body">
                                        <Badge variant="light" color={card.badgeColor}>{card.badge}</Badge>
                                        <Title order={3}>{card.title}</Title>
                                        <Text c="dimmed">{card.description}</Text>
                                        <Button
                                            component="a"
                                            href={card.href}
                                            target="_blank"
                                            rel="noreferrer"
                                            rightSection={<IconExternalLink size={17} />}
                                        >
                                            {card.action}
                                        </Button>
                                    </div>

                                    <div className="otgruzki-follow-card__qr">
                                        <div className="otgruzki-follow-card__qr-frame">
                                            <ImageView src={card.qrSrc} alt={card.qrAlt} width={220} height={220} />
                                        </div>
                                        <Text size="sm" c="dimmed" ta="center">
                                            Сканируйте QR-код, чтобы открыть страницу сразу на телефоне.
                                        </Text>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </SimpleGrid>
                </Stack>
            </Container>
        </section>
    );
}

function RemotePurchaseSection() {
    return (
        <section className="content-section">
            <Container size="xl">
                <div className="sales-remote-card">
                    <Stack gap="xl">
                        <Stack gap="xl">
                            <Title order={2} mb="md">
                                Наши клиенты часто покупают оборудование удалено, после предоставленной нашими
                                специалистами информации, например:
                            </Title>
                            <SimpleGrid cols={{ base: 1, md: 3 }} spacing="md" mt="md">
                                {remotePurchaseSupport.map((item, index) => {
                                    const icons = [IconShieldCheck, IconVideo, IconMessageCircle];
                                    const Icon = icons[index] || IconShieldCheck;

                                    return (
                                        <div key={item} className="otgruzki-step">
                                            <span className="otgruzki-step__icon">
                                                <Icon size={20} />
                                            </span>
                                            <Text>{item}</Text>
                                        </div>
                                    );
                                })}
                            </SimpleGrid>
                            <Text c="dimmed" size="lg">
                                Но мы всегда призываем наших клиентов лично убедиться в состоянии конкретной единицы
                                оборудования. Если вы все-таки предпочли удаленный способ приобретения, то наши
                                специалисты с удовольствием помогут вам найти транспорт для доставки:
                            </Text>
                        </Stack>

                        <SimpleGrid cols={{ base: 1, md: 3 }} spacing="md">
                            {deliverySupport.map((item) => (
                                <div key={item} className="sales-list-item sales-list-item--surface">
                                    <span className="sales-list-item__dot" />
                                    <Text>{item}</Text>
                                </div>
                            ))}
                        </SimpleGrid>

                        <Stack gap="sm">
                            <Text className="sales-section-kicker">Мы продаем оборудование по всей территории Российской федерации.</Text>
                            <Text c="dimmed" size="lg">
                                Подпишитесь на наш авито-магазин, чтобы всегда быть в курсе новых поступлений.
                            </Text>
                            <Text c="dimmed" size="lg">
                                Если вы не нашли то, что искали, всегда можно позвонить нашим менеджерам, возможно,
                                интересующий вас станок еще не попал в магазин или в процессе согласования условий с
                                поставщиком.
                            </Text>
                        </Stack>

                        <Group gap="md">
                            <Button
                                component="a"
                                href="https://www.avito.ru/brands/i182086396"
                                target="_blank"
                                rel="noreferrer"
                                rightSection={<IconExternalLink size={17} />}
                            >
                                Авито-магазин
                            </Button>
                            <Button
                                component="a"
                                href="/contacts"
                                variant="outline"
                                rightSection={<IconFileDescription size={17} />}
                            >
                                Связаться с менеджером
                            </Button>
                        </Group>
                    </Stack>
                </div>
            </Container>
        </section>
    );
}

export default function ProductSaleServicePage() {
    return (
        <>
            <Header />
            <main>
                <HeroSection />
                <LatestProductsSection />
                <AdvantagesSection />
                <SloganSection />
                <SummarySection items={alreadyDoneList} />
                <SocialSection />
                <RemotePurchaseSection />
            </main>
            <Footer />
        </>
    );
}
