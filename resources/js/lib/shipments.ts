import 'server-only';

import { api } from '@/lib/api';

export type Shipment = {
    id: number;
    title: string;
    slug: string | null;
    seoTitle: string | null;
    seoDescription: string | null;
    date: string;
    updatedAt: string | null;
    location: string | null;
    image: string;
    summary: string;
    tags: string[];
    galleryImages: string[];
    content: string[];
};

export type ShipmentsPagination = {
    currentPage: number;
    perPage: number;
    total: number;
    totalPages: number;
};

export type ShipmentsPageResponse = {
    shipments: Shipment[];
    pagination: ShipmentsPagination;
};

type ApiPaginatedResponse<T> = {
    data: T[];
    meta: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
};

export async function getShipmentsPage(page: number | string = 1): Promise<ShipmentsPageResponse> {
    const response = await api.server.get<ApiPaginatedResponse<Shipment>>('/shipments', {
        cache: 'no-store',
        params: {
            page,
            per_page: 8,
        },
    });

    return {
        shipments: response.data,
        pagination: {
            currentPage: response.meta.current_page,
            perPage: response.meta.per_page,
            total: response.meta.total,
            totalPages: response.meta.last_page,
        },
    };
}

export function getShipment(id: number | string): Promise<Shipment> {
    return api.server.get<Shipment>(`/shipments/${id}`, {
        cache: 'no-store',
    });
}

export function getShipmentHref(shipment: Pick<Shipment, 'id' | 'slug'>): string {
    return `/otgruzki/${shipment.slug ?? shipment.id}`;
}
