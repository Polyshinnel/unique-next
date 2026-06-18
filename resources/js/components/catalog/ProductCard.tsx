import Link from 'next/link';
import { Button, Group, Image, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';
import { catalogProducts, formatCatalogPrice } from '@/lib/catalog-products';

type Product = (typeof catalogProducts)[number];

export function ProductCard({ product }: { product: Product }) {
    return (
        <article className="product-card">
            <Link href={product.url} className="product-card__image">
                <Image src={product.imageUrl} alt={product.title} />
            </Link>
            <div className="product-card__body">
                <Text size="sm" fw={700} className="product-card__sku">
                    Арт: {product.sku}
                </Text>
                <Title order={3}>
                    <Link href={product.url}>{product.title}</Link>
                </Title>
                <Stack gap={4}>
                    <Text c="dimmed" size="sm">{product.category}</Text>
                    <Text c="dimmed" size="sm">{product.location}</Text>
                </Stack>
                <Group gap="xs" mt="sm">
                    <span className="product-tag">{product.condition}</span>
                    <span className="product-tag">{product.availability}</span>
                </Group>
                <span className="product-card__price">{formatCatalogPrice(product.price)}</span>
                <Button
                    component={Link}
                    href={product.url}
                    className="product-card__more"
                    rightSection={<IconArrowRight size={17} />}
                >
                    Подробнее
                </Button>
            </div>
        </article>
    );
}
