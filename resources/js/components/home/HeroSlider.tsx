'use client';

import { useEffect, useState } from 'react';
import { Button, Container, Group, Stack, Text, Title, UnstyledButton } from '@mantine/core';
import { IconArrowRight, IconChevronLeft, IconChevronRight } from '@tabler/icons-react';

const heroSlides = [
    {
        title: 'Продажа б/у оборудования',
        text: 'Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ.',
        action: 'В каталог',
        href: '/catalog',
        image: '/assets/img/slide-1.jpeg',
    },
    {
        title: 'Выкуп б/у оборудования',
        text: 'Осуществляем выкуп или берем на реализацию любое промышленное оборудование, производственные линии, станки с заводов, цехов, предприятий. Отталкиваемся от Ваших ценовых ожиданий. Производим оперативную оценку стоимости.',
        action: 'Подробнее',
        href: '/services',
        image: '/assets/img/slide-2.jpg',
    },
    {
        title: 'Поставка импортного оборудования',
        text: 'Ввоз и растаможка оборудования из стран ЕС и Азии, подготовка и оформление всех необходимых документов, организация транспорта. Мы - ваш российский поставщик оборудования.',
        action: 'Подробнее',
        href: '/services',
        image: '/assets/img/slide-3.jpg',
    },
    {
        title: 'Продажа инструмента',
        text: 'Оптовые поставки промышленного инструмента: металлорежущего, алмазного, абразивного, слесарного и прочего со склада. Профессиональная консультация. Отправка по всей территории РФ.',
        action: 'Подробнее',
        href: '/services',
        image: '/assets/img/slide-4.jpg',
    },
];

export function HeroSlider() {
    const [active, setActive] = useState(0);
    const slide = heroSlides[active];

    useEffect(() => {
        const timer = window.setInterval(() => {
            setActive((current) => (current + 1) % heroSlides.length);
        }, 7000);

        return () => window.clearInterval(timer);
    }, []);

    const previous = () => setActive((current) => (current - 1 + heroSlides.length) % heroSlides.length);
    const next = () => setActive((current) => (current + 1) % heroSlides.length);

    return (
        <section className="hero-slider">
            <div className="hero-slider__media" style={{ backgroundImage: `url(${slide.image})` }} />
            <Container size="xl" className="hero-slider__content">
                <Stack gap="lg" maw={760}>
                    <Title order={1}>{slide.title}</Title>
                    <Text>{slide.text}</Text>
                    <Group gap="sm" className="hero-slider__actions">
                        <Button component="a" href={slide.href} size="lg" rightSection={<IconArrowRight size={19} />}>
                            {slide.action}
                        </Button>
                        <Button component="a" href="/contacts" size="lg" variant="white">
                            Получить консультацию
                        </Button>
                    </Group>
                </Stack>

                <Group className="hero-slider__controls">
                    <UnstyledButton className="slider-arrow" onClick={previous} aria-label="Предыдущий баннер">
                        <IconChevronLeft size={24} />
                    </UnstyledButton>
                    <Group gap={8}>
                        {heroSlides.map((item, index) => (
                            <UnstyledButton
                                key={item.title}
                                className={`slider-dot ${index === active ? 'slider-dot--active' : ''}`}
                                onClick={() => setActive(index)}
                                aria-label={`Баннер ${index + 1}`}
                            />
                        ))}
                    </Group>
                    <UnstyledButton className="slider-arrow" onClick={next} aria-label="Следующий баннер">
                        <IconChevronRight size={24} />
                    </UnstyledButton>
                </Group>
            </Container>
        </section>
    );
}
