'use client';

import { UnstyledButton } from '@mantine/core';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-react';
import { Navigation } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import { ProductCard } from '@/components/catalog/ProductCard';
import type { CatalogProductCard } from '@/lib/catalog-api';

export function MobileProductSlider({ products }: { products: CatalogProductCard[] }) {
    return (
        <div className="latest-products-mobile">
            <UnstyledButton className="latest-products-slider__arrow latest-products-slider__prev" aria-label="Предыдущий товар">
                <IconChevronLeft size={20} />
            </UnstyledButton>
            <Swiper
                className="latest-products-slider"
                modules={[Navigation]}
                slidesPerView={1}
                slidesPerGroup={1}
                loop
                navigation={{ nextEl: '.latest-products-slider__next', prevEl: '.latest-products-slider__prev' }}
            >
                {products.map((product) => (
                    <SwiperSlide key={product.id}>
                        <ProductCard product={product} />
                    </SwiperSlide>
                ))}
            </Swiper>
            <UnstyledButton className="latest-products-slider__arrow latest-products-slider__next" aria-label="Следующий товар">
                <IconChevronRight size={20} />
            </UnstyledButton>
        </div>
    );
}
