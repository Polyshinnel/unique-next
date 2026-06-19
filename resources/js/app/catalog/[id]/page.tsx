'use client';

import Link from 'next/link';
import { useParams } from 'next/navigation';
import { ProductGallery } from '@/components/catalog/ProductGallery';
import { Footer } from '@/components/layout/Footer';
import { Header } from '@/components/layout/Header';
import { formatCatalogPrice, getCatalogProduct } from '@/lib/catalog-products';
import { emailHref, phoneHref } from '@/lib/site-content';
import {
    Anchor,
    Badge,
    Button,
    Container,
    Divider,
    Group,
    Stack,
    Text,
    Title,
} from '@mantine/core';
import { IconArrowLeft, IconArrowRight, IconBrandTelegram, IconBrandWhatsapp, IconCheck, IconMessageCircle, IconMail, IconPhone } from '@tabler/icons-react';

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
                        <Title order={1}>{product.title}</Title>
                        <Text size="lg">{product.summary}</Text>
                    </Container>
                </section>

                <section className="content-section product-show-section">
                    <Container size="xl">
                        <div className="product-show-layout">
                            <div className="product-show-main">
                                <ProductGallery
                                    title={product.title}
                                    images={[product.imageUrl, ...product.galleryImages]}
                                />

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
                                        </div>
                                        <Divider />
                                        <div className="product-manager-card">
                                            <Text fw={800}>Контакты менеджера</Text>
                                            <Text c="dimmed" size="sm">{product.manager.name}</Text>
                                            <Anchor href={phoneHref(product.manager.phone)} className="contact-link">
                                                <IconPhone size={18} />
                                                <span>{product.manager.phone}</span>
                                            </Anchor>
                                            <Anchor href={emailHref(product.manager.email)} className="contact-link">
                                                <IconMail size={18} />
                                                <span>{product.manager.email}</span>
                                            </Anchor>
                                            <Group gap="xs" mt="xs">
                                                <Button
                                                    component="a"
                                                    href={product.manager.socialLinks.whatsapp}
                                                    size="sm"
                                                    variant="light"
                                                    leftSection={<IconBrandWhatsapp size={16} />}
                                                >
                                                    WA
                                                </Button>
                                                <Button
                                                    component="a"
                                                    href={product.manager.socialLinks.telegram}
                                                    size="sm"
                                                    variant="light"
                                                    leftSection={<IconBrandTelegram size={16} />}
                                                >
                                                    TG
                                                </Button>
                                                <Button
                                                    component="a"
                                                    href={product.manager.socialLinks.max}
                                                    size="sm"
                                                    variant="light"
                                                    leftSection={<IconMessageCircle size={16} />}
                                                >
                                                    MAX
                                                </Button>
                                            </Group>
                                        </div>
                                        <Button
                                            component="a"
                                            href={phoneHref(product.manager.phone)}
                                            size="lg"
                                            leftSection={<IconPhone size={19} />}
                                            className="product-show-call-button"
                                        >
                                            Позвонить
                                        </Button>
                                        <Button
                                            component={Link}
                                            href="/contacts"
                                            size="lg"
                                            variant="default"
                                            rightSection={<IconArrowRight size={18} />}
                                            className="product-show-contact-button"
                                        >
                                            Свяжитесь со мной
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
