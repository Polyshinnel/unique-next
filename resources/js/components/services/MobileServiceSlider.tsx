'use client';

import { UnstyledButton } from '@mantine/core';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-react';
import { Navigation } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import { ServiceCard } from '@/components/services/ServiceCard';
import type { DemoService } from '@/lib/site-content';

export function MobileServiceSlider({ services }: { services: DemoService[] }) {
    return (
        <div className="services-mobile">
            <UnstyledButton className="services-slider__arrow services-slider__prev" aria-label="Предыдущая услуга">
                <IconChevronLeft size={20} />
            </UnstyledButton>
            <Swiper
                className="services-slider"
                modules={[Navigation]}
                slidesPerView={1}
                slidesPerGroup={1}
                loop
                navigation={{ nextEl: '.services-slider__next', prevEl: '.services-slider__prev' }}
            >
                {services.map((service) => (
                    <SwiperSlide key={service.id}>
                        <ServiceCard service={service} />
                    </SwiperSlide>
                ))}
            </Swiper>
            <UnstyledButton className="services-slider__arrow services-slider__next" aria-label="Следующая услуга">
                <IconChevronRight size={20} />
            </UnstyledButton>
        </div>
    );
}
