import type { Metadata } from 'next';
import ImageView from 'next/image';
import Link from 'next/link';
import { getPageSeo, toMetadata } from '@/lib/seo';
import { Pagination } from '@/components/common/Pagination';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
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
    IconChevronLeft,
    IconChevronRight,
    IconChevronsLeft,
    IconChevronsRight,
    IconExternalLink,
    IconMessageCircle,
    IconShieldCheck,
    IconTruckDelivery,
    IconVideo,
} from '@tabler/icons-react';
import { shipments } from '@/lib/shipments';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('shipments');

    return toMetadata(seo, {
        title: 'Отгрузки оборудования | ЮНИК С',
        description: 'Кейсы и отгрузки промышленного оборудования ЮНИК С с описанием этапов сделки и логистики.',
    });
}

const remoteSupportSteps = [
    'Дополнительные фото и видео конкретных узлов по запросу.',
    'Показ состояния узлов и агрегатов по видеосвязи.',
    'Демонстрации в работе через мессенджеры.',
    'Контроль погрузки специалистами с фотофиксацией крепления оборудования в транспорте.',
];

const followCards = [
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

function shortText(text: string, maxLength = 150) {
    if (text.length <= maxLength) {
        return text;
    }

    return `${text.slice(0, maxLength - 1).trimEnd()}…`;
}

const SHIPMENTS_PER_PAGE = 8;

type OtgruzkiPageProps = {
    searchParams?: Promise<{
        page?: string | string[];
    }>;
};

function getCurrentPage(pageParam: string | string[] | undefined, totalPages: number) {
    const pageValue = Array.isArray(pageParam) ? pageParam[0] : pageParam;
    const parsedPage = Number(pageValue ?? 1);

    if (!Number.isFinite(parsedPage) || parsedPage < 1) {
        return 1;
    }

    return Math.min(Math.floor(parsedPage), totalPages);
}

function getShipmentsPageHref(page: number) {
    return page === 1 ? '/otgruzki' : `/otgruzki?page=${page}`;
}

function ShipmentsSection({ page }: { page: number }) {
    const totalPages = Math.ceil(shipments.length / SHIPMENTS_PER_PAGE);
    const startIndex = (page - 1) * SHIPMENTS_PER_PAGE;
    const visibleShipments = shipments.slice(startIndex, startIndex + SHIPMENTS_PER_PAGE);

    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Title order={2}>Наши отгрузки</Title>
                        <Text c="dimmed">Публикуем процесс сделки и логистики, чтобы вы видели, как проходят реальные поставки.</Text>
                    </Stack>
                </Group>

                <SimpleGrid cols={{ base: 1, sm: 2, lg: 4 }} spacing="lg">
                    {visibleShipments.map((shipment) => (
                        <article key={shipment.id} className="shipment-card">
                            <Link href={`/otgruzki/${shipment.id}`} className="shipment-card__image">
                                <ImageView src={shipment.image} alt={shipment.title} width={768} height={576} />
                            </Link>
                            <div className="shipment-card__body">
                                <Text size="sm" c="dimmed">{shipment.date}</Text>

                                <Text size="sm" c="dimmed">{shipment.location}</Text>

                                <Title order={3}>
                                    <Link href={`/otgruzki/${shipment.id}`}>{shipment.title}</Link>
                                </Title>

                                <Text>{shortText(shipment.summary)}</Text>

                                <Group gap="xs">
                                    {shipment.tags.map((tag) => (
                                        <span key={tag} className="product-tag">{tag}</span>
                                    ))}
                                </Group>

                                <Button
                                    component="a"
                                    href={`/otgruzki/${shipment.id}`}
                                    className="product-card__more shipment-card__more"
                                    rightSection={<IconArrowRight size={17} />}
                                >
                                    Подробнее
                                </Button>
                            </div>
                        </article>
                    ))}
                </SimpleGrid>

                <Pagination
                    currentPage={page}
                    totalPages={totalPages}
                    getPageHref={getShipmentsPageHref}
                    ariaLabel="Пагинация отгрузок"
                    className="shipments-pagination"
                    firstControl={<IconChevronsLeft size={18} />}
                    previousControl={<IconChevronLeft size={18} />}
                    nextControl={<IconChevronRight size={18} />}
                    lastControl={<IconChevronsRight size={18} />}
                />
            </Container>
        </section>
    );
}

function FollowSection() {
    return (
        <section className="content-section">
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
                        {followCards.map((card) => (
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

function RemoteShipmentSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Stack gap="lg">
                    <Title order={2}>Когда отгрузка проходит без вашего выезда</Title>
                    <Text c="dimmed" maw={900}>
                        Периодически мы отгружаем оборудование полностью удаленно, хотя всегда рекомендуем лично убедиться
                        в его состоянии. Чтобы сделка оставалась прозрачной, наши сотрудники проводят дополнительные проверки.
                    </Text>
                    <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
                        {remoteSupportSteps.map((step, index) => {
                            const icons = [IconShieldCheck, IconVideo, IconMessageCircle, IconTruckDelivery];
                            const Icon = icons[index] || IconShieldCheck;

                            return (
                                <div key={step} className="otgruzki-step">
                                    <span className="otgruzki-step__icon">
                                        <Icon size={20} />
                                    </span>
                                    <Text>{step}</Text>
                                </div>
                            );
                        })}
                    </SimpleGrid>
                    <Group justify="space-between" gap="lg">
                        <Text c="dimmed" maw={760}>
                            Если вы выбираете удаленный формат и не присутствуете на отгрузке, мы контролируем процесс
                            погрузки и отправляем фото того, как оборудование размещено и закреплено в транспорте.
                        </Text>
                        <Button component="a" href="/about" variant="outline" rightSection={<IconArrowRight size={17} />}>
                            О компании
                        </Button>
                    </Group>
                </Stack>
            </Container>
        </section>
    );
}

export default async function OtgruzkiPage({ searchParams }: OtgruzkiPageProps) {
    const params = await searchParams;
    const totalPages = Math.max(1, Math.ceil(shipments.length / SHIPMENTS_PER_PAGE));
    const page = getCurrentPage(params?.page, totalPages);

    return (
        <>
            <Header />
            <main>
                <section className="page-hero otgruzki-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <span>Отгрузки</span>
                        </div>
                        <Title order={1}>Отгрузки оборудования</Title>
                        <Text size="lg">
                            В современном рынке доверие особенно важно: поэтому мы показываем реальные отгрузки,
                            публикуем кейсы и фиксируем ключевые этапы сделки.
                        </Text>
                    </Container>
                </section>

                <ShipmentsSection page={page} />
                <FollowSection />
                <RemoteShipmentSection />
            </main>
            <Footer />
        </>
    );
}
