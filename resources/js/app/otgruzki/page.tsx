'use client';

import ImageView from 'next/image';
import Link from 'next/link';
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
    IconExternalLink,
    IconMessageCircle,
    IconShieldCheck,
    IconTruckDelivery,
    IconVideo,
} from '@tabler/icons-react';

const shipments = [
    {
        id: 'shipment-1',
        title: 'Отгрузка машины для шовной сварки SBKJ SBFN-55',
        date: '29.01.2024',
        destination: 'Республика Беларусь, г. Смолевичи',
        image: '/assets/img/otgruzki-1.jpeg',
        summary: 'Экспортная отгрузка машины шовной сварки в Беларусь: онлайн-показ, проверка комплектности и полный пакет документов.',
    },
    {
        id: 'shipment-2',
        title: 'Отгрузка станка плазменной резки с ЧПУ Hypertherm PowerMax 105',
        date: '26.09.2023',
        destination: 'г. Обнинск, Калужская область',
        image: '/assets/img/otgruzki-2.jpg',
        summary: 'Плазменный станок Hypertherm PowerMax 105 отгружен в Обнинск после согласования условий, договора и логистики.',
    },
    {
        id: 'shipment-3',
        title: 'Отгрузка 1531М — токарно-карусельного одностоечного станка',
        date: '31.08.2023',
        destination: 'г. Пенза',
        image: '/assets/img/otgruzki-3.jpeg',
        summary: 'Токарно-карусельный 1531М отправлен в Пензу под восстановление с удаленным согласованием и контролем отгрузки.',
    },
    {
        id: 'shipment-4',
        title: 'Удаленная отгрузка с полным контролем процесса',
        date: 'Кейс удаленной покупки',
        destination: 'Отгрузка без присутствия клиента',
        image: '/assets/img/otgruzki-1.jpeg',
        summary: 'Если клиент не приезжает, организуем удаленную отгрузку: фото и видео узлов, онлайн-показ и фиксация погрузки.',
    },
];

const remoteSupportSteps = [
    'Дополнительные фото и видео конкретных узлов по запросу.',
    'Показ состояния узлов и агрегатов по видеосвязи.',
    'Демонстрации в работе через мессенджеры.',
    'Контроль погрузки специалистами с фотофиксацией крепления оборудования в транспорте.',
];

function shortText(text: string, maxLength = 150) {
    if (text.length <= maxLength) {
        return text;
    }

    return `${text.slice(0, maxLength - 1).trimEnd()}…`;
}

function ShipmentsSection() {
    return (
        <section className="content-section content-section--white">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Title order={2}>Наши отгрузки</Title>
                        <Text c="dimmed">Публикуем процесс сделки и логистики, чтобы вы видели, как проходят реальные поставки.</Text>
                    </Stack>
                    <Button component={Link} href="/catalog" variant="outline" rightSection={<IconArrowRight size={18} />}>
                        Перейти в каталог
                    </Button>
                </Group>

                <SimpleGrid cols={{ base: 1, sm: 2, xl: 4 }} spacing="lg">
                    {shipments.map((shipment) => (
                        <article key={shipment.id} className="shipment-card">
                            <div className="shipment-card__image">
                                <ImageView src={shipment.image} alt={shipment.title} width={768} height={576} />
                            </div>
                            <Group justify="space-between" gap="xs">
                                <Badge variant="light" color="blue">{shipment.date}</Badge>
                                <Text size="sm" c="dimmed">{shipment.destination}</Text>
                            </Group>
                            <Title order={3}>{shipment.title}</Title>
                            <Text>{shortText(shipment.summary)}</Text>
                        </article>
                    ))}
                </SimpleGrid>
            </Container>
        </section>
    );
}

function FollowSection() {
    return (
        <section className="content-section">
            <Container size="xl">
                <SimpleGrid cols={{ base: 1, lg: 2 }} spacing="lg">
                    <article className="otgruzki-follow-card">
                        <Badge variant="light" color="orange">Соцсети</Badge>
                        <Title order={3}>Наша официальная группа во ВКонтакте</Title>
                        <Text c="dimmed">
                            Подписывайтесь, чтобы следить за нашей работой, новыми поставками и живыми кейсами.
                        </Text>
                        <Button
                            component="a"
                            href="https://vk.com/uniqset"
                            target="_blank"
                            rel="noreferrer"
                            rightSection={<IconExternalLink size={17} />}
                        >
                            Подписаться
                        </Button>
                    </article>

                    <article className="otgruzki-follow-card">
                        <Badge variant="light" color="blue">Отзывы</Badge>
                        <Title order={3}>97% клиентов довольны нашей работой</Title>
                        <Text c="dimmed">
                            Посмотрите отзывы о компании в профиле Авито и переходите к актуальным объявлениям.
                        </Text>
                        <Group gap="sm">
                            <Button
                                component="a"
                                href="https://www.avito.ru/brands/i182086396"
                                target="_blank"
                                rel="noreferrer"
                                rightSection={<IconExternalLink size={17} />}
                            >
                                Смотреть отзывы
                            </Button>
                            <Button
                                component="a"
                                href="https://www.avito.ru/brands/i182086396"
                                target="_blank"
                                rel="noreferrer"
                                variant="default"
                            >
                                Мы на Авито
                            </Button>
                        </Group>
                    </article>
                </SimpleGrid>
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
                        <Button component={Link} href="/about" variant="outline" rightSection={<IconArrowRight size={17} />}>
                            О компании
                        </Button>
                    </Group>
                </Stack>
            </Container>
        </section>
    );
}

export default function OtgruzkiPage() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero">
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

                <ShipmentsSection />
                <FollowSection />
                <RemoteShipmentSection />
            </main>
            <Footer />
        </>
    );
}
