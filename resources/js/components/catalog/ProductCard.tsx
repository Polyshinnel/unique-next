import Link from 'next/link';
import { Button, Group, Image, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';
import type { CatalogProductCard } from '@/lib/catalog-api';
import { formatCatalogCardTitle, formatCatalogPrice } from '@/lib/catalog-format';

export function ProductCard({ product }: { product: CatalogProductCard }) {
    const cardTitle = formatCatalogCardTitle(product.title);

    return (
        <article className="product-card">
            <Link href={product.href} className="product-card__image">
                <Image src={product.imageUrl} alt={product.title} fallbackSrc="/assets/img/catalog.jpeg" />
            </Link>
            <div className="product-card__body">
                <Text size="sm" fw={700} className="product-card__sku">
                    Арт: {product.sku ?? 'уточняется'}
                </Text>
                <Title order={3}>
                    <Link href={product.href} title={product.title}>{cardTitle}</Link>
                </Title>
                <Stack gap={4}>
                    {product.category ? <Text c="dimmed" size="sm">{product.category.name}</Text> : null}
                    {product.region ? <Text c="dimmed" size="sm">{product.region.name}</Text> : null}
                </Stack>
                <Group gap="xs" mt="sm">
                    {product.state ? <span className="product-tag">{product.state.name}</span> : null}
                    {product.availability ? <span className="product-tag">{product.availability.name}</span> : null}
                </Group>
                <span className="product-card__price">{formatCatalogPrice(product.price)}</span>
                <Button
                    component="a"
                    href={product.href}
                    className="product-card__more"
                    rightSection={<IconArrowRight size={17} />}
                >
                    Подробнее
                </Button>
            </div>
        </article>
    );
}
