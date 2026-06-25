import type { Metadata } from 'next';
import Link from 'next/link';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { Button, Container, Stack, Text, Title } from '@mantine/core';
import { IconFileTypePdf } from '@tabler/icons-react';

const pdfFileName = 'Svodnaya_vedomost_rezultatov_provedeniya_spec_ocenki_uslovij_truda.pdf';
const pdfHref = `/assets/${pdfFileName}`;

export const metadata: Metadata = {
    title: 'Охрана труда | ЮНИК С',
    description: 'Сводная ведомость результатов проведения специальной оценки условий труда ООО «Юник С».',
    alternates: {
        canonical: 'https://uniqset.com/ohrana-truda',
    },
};

export default function OccupationalSafetyPage() {
    return (
        <>
            <Header />
            <main>
                <section className="page-hero privacy-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <span>Охрана труда</span>
                        </div>
                        <Stack gap="lg" maw={880}>
                            <Title order={1}>Охрана труда</Title>
                            <Text size="lg">
                                Сводная ведомость результатов проведения специальной оценки условий труда.
                            </Text>
                        </Stack>
                    </Container>
                </section>

                <section className="content-section">
                    <Container size="xl">
                        <div className="occupational-safety-card">
                            <img
                                src="/assets/img/ohrana-truda.png"
                                alt="Сводная ведомость результатов проведения специальной оценки условий труда"
                                className="occupational-safety-card__image"
                            />
                        </div>

                        <div className="occupational-safety-card__actions">
                            <Button
                                component="a"
                                href={pdfHref}
                                download={pdfFileName}
                                size="lg"
                                leftSection={<IconFileTypePdf size={20} />}
                                className="occupational-safety-card__button"
                            >
                                Скачать ведомость
                            </Button>
                        </div>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
