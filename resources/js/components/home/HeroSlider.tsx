'use client';

import { useEffect, useState } from 'react';
import { Container, Group, UnstyledButton } from '@mantine/core';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-react';
import { HeroBanner } from '@/components/home/HeroBanner';
import type { HeroSlide } from '@/lib/banners';

type HeroSliderProps = {
    slides: HeroSlide[];
};

export function HeroSlider({ slides }: HeroSliderProps) {
    const [active, setActive] = useState(0);
    const slide = slides[active] ?? slides[0];

    useEffect(() => {
        if (slides.length <= 1) {
            return undefined;
        }

        const timer = window.setInterval(() => {
            setActive((current) => (current + 1) % slides.length);
        }, 7000);

        return () => window.clearInterval(timer);
    }, [slides.length]);

    if (!slide) {
        return null;
    }

    const previous = () => setActive((current) => (current - 1 + slides.length) % slides.length);
    const next = () => setActive((current) => (current + 1) % slides.length);

    return (
        <section className="hero-slider">
            <div className="hero-slider__media" style={slide.image ? { backgroundImage: `url(${slide.image})` } : undefined} />
            <Container size="xl" className="hero-slider__content">
                <HeroBanner slide={slide} />

                {slides.length > 1 ? (
                    <Group className="hero-slider__controls">
                        <UnstyledButton className="slider-arrow" onClick={previous} aria-label="Предыдущий баннер">
                            <IconChevronLeft size={24} />
                        </UnstyledButton>
                        <Group gap={8}>
                            {slides.map((item, index) => (
                                <UnstyledButton
                                    key={item.id}
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
                ) : null}
            </Container>
        </section>
    );
}
