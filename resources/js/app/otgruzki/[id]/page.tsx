import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { ShipmentDetailPageView } from '@/components/shipments/ShipmentDetailPageView';
import { canonicalUrl } from '@/lib/seo';
import { getShipment, getShipmentHref } from '@/lib/shipments';

type ShipmentPageProps = {
    params: Promise<{
        id: string;
    }>;
};

export async function generateMetadata({ params }: ShipmentPageProps): Promise<Metadata> {
    const { id } = await params;
    const shipment = await getShipment(id).catch(() => null);

    if (!shipment) {
        return {
            title: 'Отгрузка не найдена | ЮНИК С',
            description: 'Запрошенная страница отгрузки не найдена.',
        };
    }

    return {
        title: `${shipment.seoTitle ?? shipment.title} | ЮНИК С`,
        description: shipment.seoDescription ?? shipment.summary,
        alternates: {
            canonical: canonicalUrl(getShipmentHref(shipment)),
        },
    };
}

export default async function ShipmentPage({ params }: ShipmentPageProps) {
    const { id } = await params;
    const shipment = await getShipment(id).catch(() => null);

    if (!shipment) {
        notFound();
    }

    return <ShipmentDetailPageView shipment={shipment} />;
}
