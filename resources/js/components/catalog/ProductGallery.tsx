'use client';

import { useEffect, useId, useMemo, useRef, useState } from 'react';
import { ActionIcon, Image } from '@mantine/core';
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-react';
import { Fancybox } from '@fancyapps/ui/dist/fancybox/';
import '@fancyapps/ui/dist/fancybox/fancybox.css';

type ProductGalleryProps = {
    title: string;
    images: string[];
};

export function ProductGallery({ title, images }: ProductGalleryProps) {
    const normalizedImages = useMemo(() => Array.from(new Set(images.filter(Boolean))), [images]);
    const [activeIndex, setActiveIndex] = useState(0);
    const [isDragging, setIsDragging] = useState(false);
    const galleryRef = useRef<HTMLDivElement | null>(null);
    const galleryId = useId();
    const dragStateRef = useRef({
        isDragging: false,
        startX: 0,
        startScrollLeft: 0,
        moved: false,
        pointerId: -1,
    });
    const suppressClickRef = useRef(false);

    useEffect(() => {
        const gallery = galleryRef.current;

        if (!gallery) {
            return;
        }

        Fancybox.bind(gallery, '[data-fancybox]', {
            groupAll: true,
        });

        return () => {
            Fancybox.unbind(gallery);
        };
    }, []);

    const handlePointerDown = (event: React.PointerEvent<HTMLDivElement>) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        const viewport = event.currentTarget;

        dragStateRef.current = {
            isDragging: true,
            startX: event.clientX,
            startScrollLeft: viewport.scrollLeft,
            moved: false,
            pointerId: event.pointerId,
        };
        setIsDragging(true);
        viewport.setPointerCapture(event.pointerId);
    };

    const handlePointerMove = (event: React.PointerEvent<HTMLDivElement>) => {
        const dragState = dragStateRef.current;

        if (!dragState.isDragging) {
            return;
        }

        const deltaX = event.clientX - dragState.startX;

        if (Math.abs(deltaX) > 6) {
            dragState.moved = true;
            event.currentTarget.scrollLeft = dragState.startScrollLeft - deltaX;
        }
    };

    const finishDragging = () => {
        if (dragStateRef.current.moved) {
            suppressClickRef.current = true;
            window.setTimeout(() => {
                suppressClickRef.current = false;
            }, 80);
        }

        dragStateRef.current = {
            isDragging: false,
            startX: 0,
            startScrollLeft: 0,
            moved: false,
            pointerId: -1,
        };
        setIsDragging(false);
    };

    if (normalizedImages.length === 0) {
        return null;
    }

    const safeActiveIndex = Math.min(activeIndex, normalizedImages.length - 1);

    const goToThumb = (direction: 1 | -1) => {
        setActiveIndex((currentIndex) => Math.min(
            normalizedImages.length - 1,
            Math.max(0, currentIndex + direction),
        ));
    };

    const handlePointerUp = (event: React.PointerEvent<HTMLDivElement>) => {
        if (event.currentTarget.hasPointerCapture(dragStateRef.current.pointerId)) {
            event.currentTarget.releasePointerCapture(dragStateRef.current.pointerId);
        }

        finishDragging();
    };

    const handleThumbClick = (index: number) => {
        if (!suppressClickRef.current) {
            setActiveIndex(index);
        }
    };

    return (
        <div ref={galleryRef} className="product-show-gallery">
            <div className="product-show-gallery__main">
                {normalizedImages.map((image, index) => (
                    <a
                        key={image}
                        href={image}
                        data-fancybox={galleryId}
                        data-caption={`${title}: фото ${index + 1}`}
                        className={`product-show-gallery__main-link${index === safeActiveIndex ? ' product-show-gallery__main-link--active' : ''}`}
                        aria-label={`Открыть фото товара ${title}`}
                        tabIndex={index === safeActiveIndex ? 0 : -1}
                    >
                        <Image src={image} alt={title} />
                    </a>
                ))}
            </div>

            <div className="product-show-gallery__carousel">
                <div className="product-show-gallery__nav">
                    <ActionIcon variant="default" size="lg" radius="xl" onClick={() => goToThumb(-1)} aria-label="Предыдущее фото" disabled={safeActiveIndex === 0}>
                        <IconChevronLeft size={18} />
                    </ActionIcon>
                </div>

                <div
                    className={`product-show-gallery__thumbs-viewport${isDragging ? ' is-dragging' : ''}`}
                    onPointerDown={handlePointerDown}
                    onPointerMove={handlePointerMove}
                    onPointerUp={handlePointerUp}
                    onPointerCancel={finishDragging}
                >
                    <div className="product-show-gallery__thumbs">
                        {normalizedImages.map((image, index) => (
                            <button key={`${image}-${index}`} type="button" className={`product-show-gallery__thumb ${index === safeActiveIndex ? 'product-show-gallery__thumb--active' : ''}`} onClick={() => handleThumbClick(index)} aria-label={`Показать фото ${index + 1}`} aria-pressed={index === safeActiveIndex}>
                                <Image src={image} alt={`${title}: фото ${index + 1}`} />
                            </button>
                        ))}
                    </div>
                </div>

                <div className="product-show-gallery__nav">
                    <ActionIcon variant="default" size="lg" radius="xl" onClick={() => goToThumb(1)} aria-label="Следующее фото" disabled={safeActiveIndex === normalizedImages.length - 1}>
                        <IconChevronRight size={18} />
                    </ActionIcon>
                </div>
            </div>
        </div>
    );
}
