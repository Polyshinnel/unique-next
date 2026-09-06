'use client';

import { Container, Group, UnstyledButton } from '@mantine/core';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-react';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import { HeroBanner } from '@/components/home/HeroBanner';
import type { HeroSlide } from '@/lib/banners';

type HeroSliderProps = {
    slides: HeroSlide[];
};

export function HeroSlider({ slides }: HeroSliderProps) {
    if (slides.length === 0) {
        return null;
    }

    return (
        <section className="hero-slider">
            <Swiper
                className="hero-slider__swiper"
                modules={[Autoplay, Navigation, Pagination]}
                loop={slides.length > 1}
                autoplay={slides.length > 1 ? { delay: 7000, disableOnInteraction: false } : false}
                navigation={slides.length > 1 ? { nextEl: '.hero-slider__next', prevEl: '.hero-slider__prev' } : false}
                pagination={
                    slides.length > 1
                        ? {
                              el: '.hero-slider__pagination',
                              clickable: true,
                              renderBullet: (index, className) =>
                                  `<button type="button" class="${className} slider-dot" aria-label="Баннер ${index + 1}"></button>`,
                          }
                        : false
                }
            >
                {slides.map((slide) => (
                    <SwiperSlide key={slide.id} className="hero-slider__slide">
                        <div className="hero-slider__media" style={slide.image ? { backgroundImage: `url(${slide.image})` } : undefined} />
                        <Container size="xl" className="hero-slider__content">
                            <HeroBanner slide={slide} />
                        </Container>
                    </SwiperSlide>
                ))}
            </Swiper>

            {slides.length > 1 ? (
                <Container size="xl" className="hero-slider__controls-container">
                    <Group className="hero-slider__controls">
                        <UnstyledButton className="slider-arrow hero-slider__prev" aria-label="Предыдущий баннер">
                            <IconChevronLeft size={24} />
                        </UnstyledButton>
                        <div className="hero-slider__pagination" />
                        <UnstyledButton className="slider-arrow hero-slider__next" aria-label="Следующий баннер">
                            <IconChevronRight size={24} />
                        </UnstyledButton>
                    </Group>
                </Container>
            ) : null}
        </section>
    );
}
