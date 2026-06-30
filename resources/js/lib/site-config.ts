const YANDEX_MAPS_ZOOM = 17;

export const siteConfig = {
    yandexMapsApiKey:
        process.env.NEXT_PUBLIC_YANDEX_MAPS_API_KEY || process.env.YANDEX_MAPS_API_KEY || '',
    yandexMapsZoom: YANDEX_MAPS_ZOOM,
};

export function getOfficeMapLink({ latitude, longitude }: { latitude: number; longitude: number }) {
    return `https://yandex.ru/maps/?pt=${longitude},${latitude}&z=${YANDEX_MAPS_ZOOM}&l=map`;
}
