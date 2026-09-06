import { ProductCard } from '@/components/catalog/ProductCard';
import { MobileProductSlider } from '@/components/catalog/MobileProductSlider';
import type { CatalogProductCard } from '@/lib/catalog-api';
import { Button, Container, Group, SimpleGrid, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';

type ProductCollectionSectionProps = {
    title: string;
    description: string;
    products: CatalogProductCard[];
    href: string;
    buttonLabel: string;
    limit?: number;
    withContainer?: boolean;
    mobileSlider?: boolean;
    columns?: {
        base?: number;
        sm?: number;
        lg?: number;
        xl?: number;
    };
};

export function ProductCollectionSection({
    title,
    description,
    products,
    href,
    buttonLabel,
    limit = 4,
    withContainer = true,
    mobileSlider = false,
    columns = { base: 1, sm: 2, lg: 4 },
}: ProductCollectionSectionProps) {
    if (products.length === 0) {
        return null;
    }

    const content = (
        <>
            <Group justify="space-between" align="end" mb="xl" gap="lg">
                <Stack gap={6}>
                    <Title order={2}>{title}</Title>
                    <Text c="dimmed">{description}</Text>
                </Stack>
                <Button component="a" href={href} variant="filled" className="latest-products-section__button" rightSection={<IconArrowRight size={18} />}>
                    {buttonLabel}
                </Button>
            </Group>
            <SimpleGrid className={mobileSlider ? 'latest-products-section__grid' : undefined} cols={columns} spacing="lg">
                {products.slice(0, limit).map((product) => (
                    <ProductCard key={product.id} product={product} />
                ))}
            </SimpleGrid>
            {mobileSlider ? (
                <div className="latest-products-section__mobile-slider">
                    <MobileProductSlider products={products.slice(0, limit)} />
                </div>
            ) : null}
        </>
    );

    return (
        <section className="content-section content-section--tight-top latest-products-section">
            {withContainer ? <Container size="xl">{content}</Container> : content}
        </section>
    );
}
