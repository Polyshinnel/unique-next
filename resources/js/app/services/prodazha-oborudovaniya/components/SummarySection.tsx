import { Container, Stack, Text, Title } from '@mantine/core';

type SummarySectionProps = {
    items: string[];
};

export function SummarySection({ items }: SummarySectionProps) {
    return (
        <section className="content-section">
            <Container size="xl">
                <div className="sales-summary-card">
                    <Stack gap="xl">
                        <Title order={2} mb="md">
                            Другими словами, мы уже:
                        </Title>
                        <Stack gap="md" mt="md">
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
