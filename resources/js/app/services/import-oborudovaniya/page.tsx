import type { Metadata } from 'next';
import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { Button, Container, Stack, Text, Title } from '@mantine/core';

export const metadata: Metadata = {
    title: 'Импорт оборудования | ЮНИК С',
    description: 'Сопровождаем импорт промышленного оборудования, ВЭД и поставки под задачи производства.',
};

export default function EquipmentImportPage() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <Link href="/services">Услуги</Link>
                            <span>/</span>
                            <span>Импорт оборудования</span>
                        </div>
                        <Stack gap="lg" maw={760}>
                            <Title order={1}>Импорт оборудования</Title>
                            <Text size="lg">
                                Организуем поставки промышленного оборудования из-за рубежа, помогаем с расчетом
                                стоимости, логистикой, таможней и документами.
                            </Text>
                            <Text size="lg">
                                Раздел находится в разработке. Для текущих запросов по ВЭД и поставкам лучше сразу
                                написать или позвонить нам.
                            </Text>
                            <Button component="a" href="/contacts" size="lg">
                                Обсудить поставку
                            </Button>
                        </Stack>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
