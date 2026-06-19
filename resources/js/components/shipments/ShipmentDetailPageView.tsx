import ImageView from 'next/image';
import Link from 'next/link';
import { Badge, Button, Container, Group, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import type { Shipment } from '@/lib/shipments';

export function ShipmentDetailPageView({ shipment }: { shipment: Shipment }) {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <Link href="/otgruzki">Отгрузки</Link>
                            <span>/</span>
                            <span>{shipment.title}</span>
                        </div>
                        <Title order={1}>{shipment.title}</Title>
                        <Text size="lg">{shipment.summary}</Text>
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="lg">
                        <article className="shipment-detail">
                            <div className="shipment-detail__image">
                                <ImageView src={shipment.image} alt={shipment.title} width={1440} height={960} />
                            </div>
                            <Stack gap="lg" p={{ base: 'lg', md: 'xl' }}>
                                <Group gap="sm">
                                    <Badge variant="light" color="blue">{shipment.date}</Badge>
                                    <Badge variant="light" color="gray">{shipment.location}</Badge>
                                </Group>
                                <Text size="lg" c="dimmed">
                                    {shipment.summary}
                                </Text>
                                <Group gap="xs">
                                    {shipment.tags.map((tag) => (
                                        <span key={tag} className="product-tag">{tag}</span>
                                    ))}
                                </Group>
                                <Group>
                                    <Button component="a" href="/otgruzki" className="product-card__more" rightSection={<IconArrowRight size={17} />}>
                                        Все отгрузки
                                    </Button>
                                </Group>
                            </Stack>
                        </article>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
