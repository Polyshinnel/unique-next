import Link from 'next/link';
import { Button, Image, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';
import { DemoService } from '@/lib/site-content';

export function ServiceCard({ service }: { service: DemoService }) {
    const href = `/services/${service.slug}`;

    return (
        <article className="service-card">
            <Link href={href} className="service-card__image" aria-label={service.title}>
                <Image src={service.image} alt={service.title} />
            </Link>
            <div className="service-card__body">
                <Title order={3}>
                    <Link href={href}>{service.title}</Link>
                </Title>
                <Text c="dimmed">{service.excerpt}</Text>
                <Button
                    component={Link}
                    href={href}
                    className="product-card__more service-card__more"
                    rightSection={<IconArrowRight size={17} />}
                >
                    Подробнее
                </Button>
            </div>
        </article>
    );
}
