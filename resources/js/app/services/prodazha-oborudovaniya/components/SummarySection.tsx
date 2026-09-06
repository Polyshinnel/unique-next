import { Container, Stack, Text, Title } from '@mantine/core';

type SummarySectionProps = {
    items: string[];
};

export function SummarySection({ items }: SummarySectionProps) {
    return (
        <section className="content-section sales-summary-section">
            <Container size="xl">
                <div className="sales-summary-card">
                    <Stack className="sales-summary-card__content" gap="xl">
                        <Title className="sales-summary-card__title" order={2} mb="md">
                            Другими словами, мы уже:
                        </Title>
                        <Stack className="sales-summary-card__list" gap="md" mt="md">
                            {items.map((item) => (
                                <div key={item} className="sales-list-item">
                                    <span className="sales-list-item__dot" />
                                    <Text>{item}</Text>
                                </div>
                            ))}
                        </Stack>
                    </Stack>
                </div>
            </Container>
        </section>
    );
}
