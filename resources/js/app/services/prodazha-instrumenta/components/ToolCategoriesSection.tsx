import { Group, Stack, Text, Title } from '@mantine/core';
import { IconTool } from '@tabler/icons-react';

const toolCategories = [
    'Металлорежущий: метчики, плашки, сверла, фрезы, резцы, развертки',
    'Алмазный: диски, бруски и другие позиции',
    'Абразивный: отрезные, зачистные, шлифовальные, алмазные круги, карандаши',
    'Мерительный: рулетки, линейки, индикаторы, микрометры, нутромеры',
    'Пневмоинструмент: молотки, краскораспылители, шлифмашины',
    'Электроинструмент: дрели, перфораторы, шлифмашины',
    'Газовое оборудование: резаки, горелки, манометры, редукторы',
    'Слесарный: молотки, плоскогубцы, напильники',
    'Патроны, УДГ и прочее',
] as const;

export function ToolCategoriesSection() {
    return (
        <section className="instrument-card sales-intro-card">
            <Stack gap="md">
                <Group gap="sm" align="center">
                    <span className="sales-search-card__icon">
                        <IconTool size={24} />
                    </span>
                    <Title order={2}>Коникс предлагает все основные виды инструмента</Title>
                </Group>
                <Stack gap="md">
                    {toolCategories.map((item) => (
                        <div key={item} className="sales-list-item sales-list-item--surface sales-list-item--fullwidth">
                            <span className="sales-list-item__dot" />
                            <Text>{item}</Text>
                        </div>
                    ))}
                </Stack>
            </Stack>
        </section>
    );
}
