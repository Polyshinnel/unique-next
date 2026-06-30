import { Button, Group, Stack, Text, Title } from '@mantine/core';
import { IconArrowRight } from '@tabler/icons-react';
import type { HeroSlide } from '@/lib/banners';

type HeroBannerProps = {
    slide: HeroSlide;
};

export function HeroBanner({ slide }: HeroBannerProps) {
    return (
        <>
            <Stack gap="lg" maw={760}>
                {slide.title ? <Title order={1}>{slide.title}</Title> : null}
                {slide.text ? <Text>{slide.text}</Text> : null}
                {slide.primaryButton || slide.secondaryButton ? (
                    <Group gap="sm" className="hero-slider__actions">
                        {slide.primaryButton ? (
                            <Button component="a" href={slide.primaryButton.href} size="lg" rightSection={<IconArrowRight size={19} />}>
                                {slide.primaryButton.text}
                            </Button>
                        ) : null}
                        {slide.secondaryButton ? (
                            <Button component="a" href={slide.secondaryButton.href} size="lg" variant="white">
                                {slide.secondaryButton.text}
                            </Button>
                        ) : null}
                    </Group>
                ) : null}
            </Stack>
        </>
    );
}
