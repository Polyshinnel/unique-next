import { api } from '@/lib/api';

export type HeroSlideButton = {
    text: string;
    href: string;
};

export type HeroSlide = {
    id: number;
    title: string | null;
    text: string | null;
    image: string | null;
    primaryButton: HeroSlideButton | null;
    secondaryButton: HeroSlideButton | null;
};

type BannerResponse = {
    id: number;
    image: string | null;
    title: string | null;
    text: string | null;
    button_one_text: string | null;
    button_one_url: string | null;
    button_two_text: string | null;
    button_two_url: string | null;
};

function normalizeStoragePath(path: string | null): string | null {
    if (!path) {
        return null;
    }

    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) {
        return path;
    }

    return `/storage/${path.replace(/^\/+/, '')}`;
}

function toButton(text: string | null, href: string | null): HeroSlideButton | null {
    if (!text || !href) {
        return null;
    }

    return { text, href };
}

function toHeroSlide(banner: BannerResponse): HeroSlide {
    return {
        id: banner.id,
        title: banner.title,
        text: banner.text,
        image: normalizeStoragePath(banner.image),
        primaryButton: toButton(banner.button_one_text, banner.button_one_url),
        secondaryButton: toButton(banner.button_two_text, banner.button_two_url),
    };
}

export async function getHomeBanners(): Promise<HeroSlide[]> {
    try {
        const banners = await api.server.get<BannerResponse[]>('/banners', {
            cache: 'no-store',
        });

        return banners.map(toHeroSlide);
    } catch {
        return [];
    }
}
