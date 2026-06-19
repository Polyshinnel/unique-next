import type { Metadata } from 'next';
import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { Button, Container, Stack, Text, Title } from '@mantine/core';

export const metadata: Metadata = {
    title: 'Вакансии | ЮНИК С',
    description: 'Карьерная страница ЮНИК С для публикации актуальных вакансий и условий работы.',
};

export default function VacancyPage() {
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
                            <span>Вакансии</span>
                        </div>
                        <Title order={1}>Вакансии</Title>
                        <Text size="lg">
                            Страница для карьерного блока уже создана и готова к наполнению актуальными позициями.
                        </Text>
                    </Container>
                </section>

                <section className="content-section content-section--white">
                    <Container size="md">
                        <Stack gap="lg">
                            <Text c="dimmed" size="lg">
                                Здесь можно добавить список вакансий, описание условий работы, стек задач и контакты для отклика.
                            </Text>
                            <Button component="a" href="/contacts">
                                Отправить запрос
                            </Button>
                        </Stack>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
