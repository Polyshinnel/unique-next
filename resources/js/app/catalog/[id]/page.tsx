'use client';

import Link from 'next/link';
import { useParams } from 'next/navigation';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { formatCatalogPrice, getCatalogProduct } from '@/lib/catalog-products';
import { phoneHref } from '@/lib/site-content';
import {
    Anchor,
    Badge,
    Button,
    Container,
    Divider,
    Group,
    Image,
    SimpleGrid,
    Stack,
    Text,
    Title,
} from '@mantine/core';
import { IconArrowLeft, IconArrowRight, IconCheck, IconPhone } from '@tabler/icons-react';

export default function ProductShowPage() {
    const params = useParams<{ id: string }>();
    const product = getCatalogProduct(params.id);

    if (!product) {
        return (
            <>
                <Header />
                <main>
                    <section className="content-section product-show-section">
                        <Container size="xl">
                            <div className="product-show-empty">
                                <Badge variant="light" color="orange">Каталог</Badge>
                                <Title order={1}>Товар не найден</Title>
                                <Text c="dimmed">Позиция могла быть снята с публикации или перемещена.</Text>
                                <Button component={Link} href="/catalog" leftSection={<IconArrowLeft size={18} />}>
                                    Вернуться в каталог
                                </Button>
                            </div>
                        </Container>
                    </section>
                </main>
                <Footer />
            </>
        );
    }

    return (
        <>
            <Header />
            <main>
                <section className="page-hero catalog-hero product-show-hero">
                    <Container size="xl">
                        <div className="catalog-breadcrumbs">
                            <Link href="/">Главная</Link>
                            <span>/</span>
                            <Link href="/catalog">Каталог оборудования</Link>
                            <span>/</span>
                            <span>{product.title}</span>
                        </div>
                        <Badge className="hero-slider__badge">{product.sku}</Badge>
                        <Title order={1}>{product.title}</Title>
                        <Text size="lg">{product.summary}</Text>
                    </Container>
                </section>

                <section className="content-section product-show-section">
                    <Container size="xl">
                        <div className="product-show-layout">
                            <div className="product-show-main">
                                <div className="product-show-gallery">
                                    <div className="product-show-gallery__main">
                                        <Image src={product.imageUrl} alt={product.title} />
                                    </div>
                                    <SimpleGrid cols={{ base: 2, sm: 4 }} spacing="sm">
                                        {product.galleryImages.map((image, index) => (
                                            <div key={image} className={`product-show-gallery__thumb ${index === 0 ? 'product-show-gallery__thumb--active' : ''}`}>
                                                <Image src={image} alt={`${product.title}: фото ${index + 1}`} />
                                            </div>
                                        ))}
                                    </SimpleGrid>
                                </div>

                                {product.characteristicBlocks.map((block) => (
                                    <section key={block.title} className="product-info-block">
                                        <Title order={2}>{block.title}</Title>
                                        <div className="product-info-block__content">
                                            {block.items.map((item) => (
                                                <div key={item} className="product-info-row">
                                                    <IconCheck size={18} />
                                                    <Text>{item}</Text>
                                                </div>
                                            ))}
                                        </div>
                                    </section>
                                ))}

                                <section className="product-info-block">
                                    <Title order={2}>Теги товара</Title>
                                    <Group gap="xs">
                                        {product.tags.map((tag) => (
                                            <Link key={tag} href={`/catalog?search=${encodeURIComponent(tag)}`} className="product-tag">
                                                {tag}
                                            </Link>
                                        ))}
                                    </Group>
                                </section>
                            </div>

                            <aside className="product-show-aside">
                                <div className="product-show-panel">
                                    <Stack gap="md">
                                        <Badge variant="light" color="blue">{product.availability}</Badge>
                                        <div>
                                            <Text c="dimmed" size="sm" fw={700}>Цена</Text>
                                            <div className="product-show-price">{formatCatalogPrice(product.price)}</div>
                                        </div>
                                        <Divider />
                                        <div className="product-show-details">
                                            <div className="product-show-detail"><span>Название:</span><b>{product.title}</b></div>
                                            <div className="product-show-detail"><span>Артикул:</span><b>{product.sku}</b></div>
                                            <div className="product-show-detail"><span>Состояние:</span><b>{product.condition}</b></div>
                                            <div className="product-show-detail"><span>Наличие:</span><b>{product.availability}</b></div>
                                            <div className="product-show-detail"><span>Локация:</span><b>{product.location}</b></div>
                                            <div className="product-show-detail"><span>Категория:</span><b>{product.category}</b></div>
                                            {product.details.map((detail) => (
                                                <div key={detail.label} className="product-show-detail"><span>{detail.label}:</span><b>{detail.value}</b></div>
                                            ))}
                                        </div>
                                        <Divider />
                                        <div className="product-manager-card">
                                            <Text fw={800}>Контакты менеджера</Text>
                                            <Text c="dimmed" size="sm">{product.manager.name}</Text>
                                            <Anchor href={phoneHref(product.manager.phone)} className="contact-link">
                                                <IconPhone size={18} />
                                                <span>{product.manager.phone}</span>
                                            </Anchor>
                                        </div>
                                        <Button component="a" href={phoneHref(product.manager.phone)} size="lg" leftSection={<IconPhone size={19} />}>
                                            Позвонить
                                        </Button>
                                        <Button component={Link} href="/contacts" size="lg" variant="default" rightSection={<IconArrowRight size={18} />}>
                                            Задать вопрос
                                        </Button>
                                    </Stack>
                                </div>
                            </aside>
                        </div>
                    </Container>
                </section>
            </main>
            <Footer />
        </>
    );
}
