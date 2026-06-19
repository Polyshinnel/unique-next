'use client';

import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { ActionIcon, Image, Modal, Text } from '@mantine/core';
import { IconChevronLeft, IconChevronRight, IconMinus, IconPlus, IconZoomReset } from '@tabler/icons-react';

type ProductGalleryProps = {
    title: string;
    images: string[];
};

const MIN_ZOOM = 1;
const MAX_ZOOM = 3;
const ZOOM_STEP = 0.25;

export function ProductGallery({ title, images }: ProductGalleryProps) {
    const normalizedImages = useMemo(() => Array.from(new Set(images.filter(Boolean))), [images]);
    const [activeIndex, setActiveIndex] = useState(0);
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);
    const [isDragging, setIsDragging] = useState(false);
    const [zoom, setZoom] = useState(MIN_ZOOM);
    const viewportRef = useRef<HTMLDivElement | null>(null);
    const dragStateRef = useRef({
        isDragging: false,
        startX: 0,
        startScrollLeft: 0,
        moved: false,
        pointerId: -1,
    });
    const suppressClickRef = useRef(false);

    const goToImage = useCallback((direction: 1 | -1) => {
        setZoom(MIN_ZOOM);
        setActiveIndex((currentIndex) => {
            const nextIndex = (currentIndex + direction + normalizedImages.length) % normalizedImages.length;
            return nextIndex;
        });
    }, [normalizedImages.length]);

    useEffect(() => {
        if (!isPreviewOpen) {
            return;
        }

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goToImage(-1);
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                goToImage(1);
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => {
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [goToImage, isPreviewOpen]);

    useEffect(() => {
        const viewport = viewportRef.current;
        const activeThumb = viewport?.querySelector<HTMLButtonElement>('[aria-pressed="true"]');

        activeThumb?.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center',
        });
    }, [activeIndex]);

    if (normalizedImages.length === 0) {
        return null;
    }

    const activeImage = normalizedImages[activeIndex] ?? normalizedImages[0];

    const goToThumb = (direction: 1 | -1) => {
        setActiveIndex((currentIndex) => {
            const nextIndex = currentIndex + direction;

            if (nextIndex < 0) {
                return 0;
            }

            if (nextIndex >= normalizedImages.length) {
                return normalizedImages.length - 1;
            }

            return nextIndex;
        });
    };

    const handlePointerDown = (event: React.PointerEvent<HTMLDivElement>) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        const viewport = viewportRef.current;

        if (!viewport) {
            return;
        }

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
        const viewport = viewportRef.current;
        const dragState = dragStateRef.current;

        if (!viewport || !dragState.isDragging) {
            return;
        }

        const deltaX = event.clientX - dragState.startX;

        if (Math.abs(deltaX) > 6) {
            dragState.moved = true;
            viewport.scrollLeft = dragState.startScrollLeft - deltaX;
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

    const handlePointerUp = () => {
        const viewport = viewportRef.current;

        if (viewport?.hasPointerCapture(dragStateRef.current.pointerId)) {
            viewport.releasePointerCapture(dragStateRef.current.pointerId);
        }

        finishDragging();
    };

    const handlePointerCancel = () => {
        finishDragging();
    };

    const handleThumbClick = (index: number) => {
        if (suppressClickRef.current) {
            return;
        }

        setZoom(MIN_ZOOM);
        setActiveIndex(index);
    };

    const updateZoom = (direction: 1 | -1) => {
        setZoom((currentZoom) => {
            const nextZoom = currentZoom + direction * ZOOM_STEP;
            return Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, Number(nextZoom.toFixed(2))));
        });
    };

    return (
        <>
            <div className="product-show-gallery">
                <button
                    type="button"
                    className="product-show-gallery__main"
                    onClick={() => {
                        setZoom(MIN_ZOOM);
                        setIsPreviewOpen(true);
                    }}
                    aria-label={`Открыть фото товара ${title}`}
                >
                    <Image src={activeImage} alt={title} />
                </button>

                <div className="product-show-gallery__carousel">
                    <div className="product-show-gallery__nav">
                        <ActionIcon
                            variant="default"
                            size="lg"
                            radius="xl"
                            onClick={() => goToThumb(-1)}
                            aria-label="Предыдущее фото"
                            disabled={activeIndex === 0}
                        >
                            <IconChevronLeft size={18} />
                        </ActionIcon>
                    </div>

                    <div
                        ref={viewportRef}
                        className={`product-show-gallery__thumbs-viewport${isDragging ? ' is-dragging' : ''}`}
                        onPointerDown={handlePointerDown}
                        onPointerMove={handlePointerMove}
                        onPointerUp={handlePointerUp}
                        onPointerCancel={handlePointerCancel}
                    >
                        <div className="product-show-gallery__thumbs">
                            {normalizedImages.map((image, index) => (
                                <button
                                    key={`${image}-${index}`}
                                    type="button"
                                    className={`product-show-gallery__thumb ${index === activeIndex ? 'product-show-gallery__thumb--active' : ''}`}
                                    onClick={() => handleThumbClick(index)}
                                    aria-label={`Показать фото ${index + 1}`}
                                    aria-pressed={index === activeIndex}
                                >
                                    <Image src={image} alt={`${title}: фото ${index + 1}`} />
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="product-show-gallery__nav">
                        <ActionIcon
                            variant="default"
                            size="lg"
                            radius="xl"
                            onClick={() => goToThumb(1)}
                            aria-label="Следующее фото"
                            disabled={activeIndex === normalizedImages.length - 1}
                        >
                            <IconChevronRight size={18} />
                        </ActionIcon>
                    </div>
                </div>
            </div>

            <Modal
                opened={isPreviewOpen}
                onClose={() => setIsPreviewOpen(false)}
                withCloseButton
                centered
                size="xl"
                overlayProps={{ backgroundOpacity: 0.82, blur: 4 }}
                classNames={{
                    body: 'product-gallery-modal__body',
                    content: 'product-gallery-modal__content',
                    header: 'product-gallery-modal__header',
                }}
            >
                <div className="product-gallery-modal">
                    <div className="product-gallery-modal__viewport">
                        <ActionIcon
                            variant="subtle"
                            color="gray"
                            size="xl"
                            radius="xl"
                            onClick={() => goToImage(-1)}
                            aria-label="Предыдущая фотография"
                            className="product-gallery-modal__side-control product-gallery-modal__side-control--prev"
                        >
                            <IconChevronLeft size={22} />
                        </ActionIcon>

                        <Image
                            src={activeImage}
                            alt={`${title}: увеличенный просмотр`}
                            className="product-gallery-modal__image"
                            style={{ transform: `scale(${zoom})` }}
                        />

                        <ActionIcon
                            variant="subtle"
                            color="gray"
                            size="xl"
                            radius="xl"
                            onClick={() => goToImage(1)}
                            aria-label="Следующая фотография"
                            className="product-gallery-modal__side-control product-gallery-modal__side-control--next"
                        >
                            <IconChevronRight size={22} />
                        </ActionIcon>
                    </div>

                    <div className="product-gallery-modal__footer">
                        <Text size="md" fw={700} className="product-gallery-modal__title">
                            {title}
                        </Text>
                        <div className="product-gallery-modal__actions">
                            <ActionIcon
                                variant="subtle"
                                color="gray"
                                size="lg"
                                radius="xl"
                                onClick={() => updateZoom(-1)}
                                aria-label="Уменьшить фото"
                                disabled={zoom <= MIN_ZOOM}
                            >
                                <IconMinus size={18} />
                            </ActionIcon>
                            <Text size="sm" fw={700} className="product-gallery-modal__zoom-value">
                                {Math.round(zoom * 100)}%
                            </Text>
                            <ActionIcon
                                variant="subtle"
                                color="gray"
                                size="lg"
                                radius="xl"
                                onClick={() => updateZoom(1)}
                                aria-label="Увеличить фото"
                                disabled={zoom >= MAX_ZOOM}
                            >
                                <IconPlus size={18} />
                            </ActionIcon>
                            <ActionIcon
                                variant="subtle"
                                color="gray"
                                size="lg"
                                radius="xl"
                                onClick={() => setZoom(MIN_ZOOM)}
                                aria-label="Сбросить масштаб"
                                disabled={zoom === MIN_ZOOM}
                            >
                                <IconZoomReset size={18} />
                            </ActionIcon>
                        </div>
                    </div>
                </div>
            </Modal>
        </>
    );
}
