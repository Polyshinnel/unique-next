import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { ShipmentDetailPageView } from '@/components/shipments/ShipmentDetailPageView';
import { shipments } from '@/lib/shipments';

type ShipmentPageProps = {
    params: Promise<{
        id: string;
    }>;
};

export function generateStaticParams() {
    return shipments.map((shipment) => ({ id: shipment.id }));
}

export async function generateMetadata({ params }: ShipmentPageProps): Promise<Metadata> {
    const { id } = await params;
    const shipment = shipments.find((item) => item.id === id);

    if (!shipment) {
        return {
            title: 'Отгрузка не найдена | ЮНИК С',
            description: 'Запрошенная страница отгрузки не найдена.',
        };
    }

    return {
        title: `${shipment.title} | ЮНИК С`,
        description: shipment.summary,
    };
}

export default async function ShipmentPage({ params }: ShipmentPageProps) {
    const { id } = await params;
    const shipment = shipments.find((item) => item.id === id);

    if (!shipment) {
        notFound();
    }

    return <ShipmentDetailPageView shipment={shipment} />;
}
