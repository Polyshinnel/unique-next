import type { Metadata } from 'next';
import ImageView from 'next/image';
import Link from 'next/link';
import { SocialProofSection } from '@/components/common/SocialProofSection';
import { canonicalUrl, getPageSeo, toMetadata } from '@/lib/seo';
import { PageStructuredData } from '@/components/seo/OrganizationJsonLd';
import { Pagination } from '@/components/common/Pagination';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
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
    IconArrowRight,
    IconBrandTelegram,
    IconBrandVk,
    IconChevronLeft,
    IconChevronRight,
    IconChevronsLeft,
    IconChevronsRight,
    IconMessageCircle,
    IconShieldCheck,
    IconTruckDelivery,
    IconVideo,
} from '@tabler/icons-react';
import { getShipmentHref, getShipmentsPage, type Shipment } from '@/lib/shipments';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('shipments');

    return toMetadata(seo, {
        title: 'Отгрузки оборудования | ЮНИК С',
        description: 'Кейсы и отгрузки промышленного оборудования ЮНИК С с описанием этапов сделки и логистики.',
        alternates: {
            canonical: canonicalUrl('/otgruzki'),
        },
    });
}

const remoteSupportSteps = [
    'Дополнительные фото и видео конкретных узлов по запросу.',
    'Показ состояния узлов и агрегатов по видеосвязи.',
    'Демонстрации в работе через мессенджеры.',
    'Контроль погрузки специалистами с фотофиксацией крепления оборудования в транспорте.',
];

const vkHref = 'https://vk.com/uniqset';
const telegramHref = 'https://telegram.me/uniqset_gen';

function shortText(text: string, maxLength = 150) {
    if (text.length <= maxLength) {
        return text;
    }

    return `${text.slice(0, maxLength - 1).trimEnd()}...`;
}

type OtgruzkiPageProps = {
    searchParams?: Promise<{
        page?: string | string[];
    }>;
};

function getRequestedPage(pageParam: string | string[] | undefined) {
    const pageValue = Array.isArray(pageParam) ? pageParam[0] : pageParam;
    const parsedPage = Number(pageValue ?? 1);

    if (!Number.isFinite(parsedPage) || parsedPage < 1) {
        return 1;
    }

    return Math.floor(parsedPage);
}

function getShipmentsPageHref(page: number) {
    return page === 1 ? '/otgruzki' : `/otgruzki?page=${page}`;
}

function getShipmentCardTags(tags: string[]) {
    return tags.slice(0, 3);
}

function ShipmentsSection({
    shipments,
    page,
    totalPages,
}: {
    shipments: Shipment[];
    page: number;
    totalPages: number;
}) {
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
                    {shipments.map((shipment) => (
                        <article key={shipment.id} className="shipment-card">
                            <Link href={getShipmentHref(shipment)} className="shipment-card__image">
                                <ImageView src={shipment.image} alt={shipment.title} width={768} height={576} unoptimized />
                            </Link>
                            <div className="shipment-card__body">
                                <Text size="sm" c="dimmed">{shipment.date}</Text>

                                {shipment.location ? <Text size="sm" c="dimmed">{shipment.location}</Text> : null}

                                <Title order={3}>
                                    <Link href={getShipmentHref(shipment)}>{shipment.title}</Link>
                                </Title>

                                <Text>{shortText(shipment.summary)}</Text>

                                <Group gap="xs">
                                    {getShipmentCardTags(shipment.tags).map((tag) => (
                                        <span key={tag} className="product-tag product-tag--small">{tag}</span>
                                    ))}
                                </Group>

                                <Button
                                    component="a"
                                    href={getShipmentHref(shipment)}
                                    className="product-card__more shipment-card__more"
                                    rightSection={<IconArrowRight size={17} />}
                                >
                                    Подробнее
                                </Button>
                            </div>
                        </article>
                    ))}
                </SimpleGrid>

                {shipments.length ? (
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
                ) : (
                    <Text c="dimmed">Пока нет опубликованных отгрузок.</Text>
                )}
            </Container>
        </section>
    );
}

function FollowSection() {
    return <SocialProofSection className="content-section" />;
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
    const requestedPage = getRequestedPage(params?.page);
    const { shipments, pagination } = await getShipmentsPage(requestedPage);

    return (
        <>
            <PageStructuredData seoKey="shipments" path="/otgruzki" pageType="CollectionPage" fallbackTitle="Отгрузки оборудования | ЮНИК С" fallbackDescription="Кейсы и отгрузки промышленного оборудования ЮНИК С с описанием этапов сделки и логистики." />
            <Header />
            <main>
                <section className="page-hero otgruzki-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <span>Отгрузки</span>
                        </div>
                        <Stack gap="lg" align="flex-start" maw={900}>
                            <Title order={1}>Отгрузки оборудования</Title>
                            <Text size="lg">
                                В современном рынке доверие особенно важно: поэтому мы показываем реальные отгрузки,
                                публикуем кейсы и фиксируем ключевые этапы сделки.
                            </Text>
                            <Group gap="md" wrap="wrap">
                                <Button
                                    component="a"
                                    href={vkHref}
                                    target="_blank"
                                    rel="noreferrer"
                                    size="lg"
                                    leftSection={<IconBrandVk size={18} />}
                                >
                                    VK
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

                <ShipmentsSection
                    shipments={shipments}
                    page={pagination.currentPage}
                    totalPages={Math.max(1, pagination.totalPages)}
                />
                <FollowSection />
                <RemoteShipmentSection />
            </main>
            <Footer />
        </>
    );
}
