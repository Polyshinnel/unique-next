import { siteContacts } from '@/lib/site-content';

const MAPBOX_STYLE_ID = 'mapbox/streets-v12';
const MAPBOX_IMAGE_SIZE = '1200x640';
const MAPBOX_ZOOM = 15;
const MAPBOX_BEARING = 0;
const MAPBOX_PITCH = 0;
const MAPBOX_MARKER_COLOR = 'f0a32f';

export const siteConfig = {
    mapboxAccessToken: process.env.NEXT_PUBLIC_MAPBOX_ACCESS_TOKEN || process.env.MAP_BOX_ACCESS_TOKEN || '',
};

export function getOfficeMapImageUrl() {
    if (!siteConfig.mapboxAccessToken) {
        return null;
    }

    const { longitude, latitude } = siteContacts.coordinates;

    return `https://api.mapbox.com/styles/v1/${MAPBOX_STYLE_ID}/static/pin-s+${MAPBOX_MARKER_COLOR}(${longitude},${latitude})/${longitude},${latitude},${MAPBOX_ZOOM},${MAPBOX_BEARING},${MAPBOX_PITCH}/${MAPBOX_IMAGE_SIZE}?access_token=${siteConfig.mapboxAccessToken}`;
}

export function getOfficeMapLink() {
    const { latitude, longitude } = siteContacts.coordinates;

    return `https://www.google.com/maps?q=${latitude},${longitude}`;
}

export function getOfficeMapEmbedUrl() {
    const { latitude, longitude } = siteContacts.coordinates;
    const delta = 0.008;
    const left = longitude - delta;
    const right = longitude + delta;
    const top = latitude + delta;
    const bottom = latitude - delta;

    return `https://www.openstreetmap.org/export/embed.html?bbox=${left},${bottom},${right},${top}&layer=mapnik&marker=${latitude},${longitude}`;
}
