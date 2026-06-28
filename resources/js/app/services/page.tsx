import type { Metadata } from 'next';
import Link from 'next/link';
import { getPageSeo, toMetadata } from '@/lib/seo';
import { demoServices } from '@/lib/site-content';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { ServiceCard } from '@/components/services/ServiceCard';
import { Container, Group, SimpleGrid, Stack, Text, Title } from '@mantine/core';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('services');

    return toMetadata(seo, {
        title: 'Услуги | ЮНИК С',
        description: 'Услуги ЮНИК С: продажа, выкуп, поставка, импорт и сопровождение сделок с промышленным оборудованием.',
    });
}

function ServicesSection() {
    return (
        <section className="content-section content-section--white content-section--tight-top services-section">
            <Container size="xl">
                <Group justify="space-between" align="end" mb="xl" gap="lg">
                    <Stack gap={6}>
                        <Title order={2}>Услуги</Title>
                        <Text c="dimmed">Основные направления работы с оборудованием и сделками.</Text>
                    </Stack>
                </Group>
                <SimpleGrid cols={{ base: 1, sm: 2, xl: 4 }} spacing="lg">
                    {demoServices.map((service) => (
                        <ServiceCard key={service.id} service={service} />
                    ))}
                </SimpleGrid>
            </Container>
        </section>
    );
}

export default function ServicesPage() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero services-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <span>Услуги</span>
                        </div>
                        <Title order={1}>Услуги</Title>
                        <Text size="lg">
                            Помогаем подобрать, купить, продать и доставить промышленное оборудование с понятными условиями сделки.
                        </Text>
                    </Container>
                </section>
                <ServicesSection />
            </main>
            <Footer />
        </>
    );
}
