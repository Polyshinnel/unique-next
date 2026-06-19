import type { Metadata } from 'next';
import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { Button, Container, Stack, Text, Title } from '@mantine/core';

export const metadata: Metadata = {
    title: 'Почему мы | ЮНИК С',
    description: 'Преимущества работы с ЮНИК С при покупке, продаже и поставке промышленного оборудования.',
};

export default function WhyWePage() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <Link href="/about">О компании</Link>
                            <span>/</span>
                            <span>Почему мы</span>
                        </div>
                        <Title order={1}>Почему мы</Title>
                        <Text size="lg">
                            Подробный блок с преимуществами компании можно наполнить отдельно. Маршрут уже готов для дальнейшего контента.
                        </Text>
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="md">
                        <Stack gap="lg">
                            <Text c="dimmed" size="lg">
                                Здесь удобно разместить аргументы для клиентов: опыт команды, прозрачность сделок, географию поставок, отзывы и примеры реализованных проектов.
                            </Text>
                            <Button component="a" href="/contacts">
                                Связаться с нами
                            </Button>
                        </Stack>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
