import ImageView from 'next/image';
import Link from 'next/link';
import { ProductGallery } from '@/components/catalog/ProductGallery';
import { Badge, Button, Container, Group, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import type { Shipment } from '@/lib/shipments';

export function ShipmentDetailPageView({ shipment }: { shipment: Shipment }) {
    const summary = shipment.summary.trim();
    const contentParagraphs = (shipment.content ?? []).filter((paragraph) => paragraph.trim() !== summary);

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
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="xl">
                        <article className="shipment-detail">
                            <div className="shipment-detail__media">
                                {shipment.galleryImages?.length ? (
                                    <ProductGallery
                                        title={shipment.title}
                                        images={shipment.galleryImages}
                                    />
                                ) : (
                                    <div className="shipment-detail__image">
                                        <ImageView src={shipment.image} alt={shipment.title} width={1440} height={960} unoptimized />
                                    </div>
                                )}
                            </div>

                            <Stack gap="lg" p={{ base: 'lg', md: 'xl' }} className="shipment-detail__content">
                                <Group gap="sm">
                                    <Badge variant="light" color="blue">{shipment.date}</Badge>
                                    {shipment.location ? <Badge variant="light" color="gray">{shipment.location}</Badge> : null}
                                </Group>

                                <div className="shipment-detail__text">
                                    {contentParagraphs.map((paragraph) => (
                                        <Text key={paragraph} size="lg">
                                            {paragraph}
                                        </Text>
                                    ))}
                                </div>

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
