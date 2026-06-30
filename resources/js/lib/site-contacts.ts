import 'server-only';

import { cache } from 'react';
import { api } from '@/lib/api';
import { siteContacts as fallbackSiteContacts } from '@/lib/site-content';

type SiteContactsResponse = Partial<{
    address: string | null;
    address2: string | null;
    phone: string | null;
    email: string | null;
    work_schedule: string | null;
    work_schedule2: string | null;
    latitude: number | string | null;
    longitude: number | string | null;
    telegram: string | null;
    vk: string | null;
    max: string | null;
    inn: string | null;
    ogrn: string | null;
}>;

export type SiteContacts = {
    address: string;
    address2: string;
    phone: string;
    email: string;
    workSchedule: string;
    workSchedule2: string;
    latitude: number;
    longitude: number;
    telegram: string;
    vk: string;
    max: string;
    inn: string;
    ogrn: string;
};

function toNumber(value: number | string | null | undefined, fallback: number): number {
    if (typeof value === 'number' && Number.isFinite(value)) {
        return value;
    }

    if (typeof value === 'string') {
        const normalized = Number(value.replace(',', '.'));

        if (Number.isFinite(normalized)) {
            return normalized;
        }
    }

    return fallback;
}

function normalizeContactValue(value: string | null | undefined, fallback: string): string {
    return value?.trim() || fallback;
}

function normalizeSiteContacts(contact: SiteContactsResponse | null | undefined): SiteContacts {
    return {
        address: normalizeContactValue(contact?.address, fallbackSiteContacts.address),
        address2: contact?.address2?.trim() || '',
        phone: normalizeContactValue(contact?.phone, fallbackSiteContacts.phone),
        email: normalizeContactValue(contact?.email, fallbackSiteContacts.email),
        workSchedule: normalizeContactValue(contact?.work_schedule, fallbackSiteContacts.workingHours.weekdays),
        workSchedule2: normalizeContactValue(contact?.work_schedule2, fallbackSiteContacts.workingHours.weekends),
        latitude: toNumber(contact?.latitude, fallbackSiteContacts.coordinates.latitude),
        longitude: toNumber(contact?.longitude, fallbackSiteContacts.coordinates.longitude),
        telegram: normalizeContactValue(contact?.telegram, fallbackSiteContacts.socialLinks.telegram),
        vk: normalizeContactValue(contact?.vk, fallbackSiteContacts.socialLinks.vk),
        max: normalizeContactValue(contact?.max, fallbackSiteContacts.socialLinks.max),
        inn: normalizeContactValue(contact?.inn, '4027139409'),
        ogrn: normalizeContactValue(contact?.ogrn, '1194027002861'),
    };
}

export const getSiteContacts = cache(async (): Promise<SiteContacts> => {
    try {
        const contact = await api.server.get<SiteContactsResponse>('/contacts', {
            cache: 'no-store',
        });

        return normalizeSiteContacts(contact);
    } catch {
        return normalizeSiteContacts(null);
    }
});

export function formatOfficeAddress(contacts: Pick<SiteContacts, 'address' | 'address2'>): string {
    return [contacts.address, contacts.address2].filter(Boolean).join(', ');
}

export function formatCoordinates(contacts: Pick<SiteContacts, 'longitude' | 'latitude'>): string {
    return `${contacts.longitude}, ${contacts.latitude}`;
}
