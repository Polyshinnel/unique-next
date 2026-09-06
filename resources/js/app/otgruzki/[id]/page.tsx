import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { ShipmentDetailPageView } from '@/components/shipments/ShipmentDetailPageView';
import { canonicalUrl, withSocialMetadata } from '@/lib/seo';
import { getShipment, getShipmentHref } from '@/lib/shipments';
import { PageStructuredData } from '@/components/seo/OrganizationJsonLd';

type ShipmentPageProps = {
    params: Promise<{
        id: string;
    }>;
};

export async function generateMetadata({ params }: ShipmentPageProps): Promise<Metadata> {
    const { id } = await params;
    const shipment = await getShipment(id).catch(() => null);

    if (!shipment) {
        return withSocialMetadata({
            title: 'Отгрузка не найдена | ЮНИК С',
            description: 'Запрошенная страница отгрузки не найдена.',
            alternates: {
                canonical: canonicalUrl(`/otgruzki/${id}`),
            },
        });
    }

    return withSocialMetadata({
        title: `${shipment.seoTitle ?? shipment.title} | ЮНИК С`,
        description: shipment.seoDescription ?? shipment.summary,
        alternates: {
            canonical: canonicalUrl(getShipmentHref(shipment)),
        },
    });
}

export default async function ShipmentPage({ params }: ShipmentPageProps) {
    const { id } = await params;
    const shipment = await getShipment(id).catch(() => null);

    if (!shipment) {
        notFound();
    }

    const title = `${shipment.seoTitle ?? shipment.title} | ЮНИК С`;
    const description = shipment.seoDescription ?? shipment.summary;
    const publishedAt = shipment.date.split('.').reverse().join('-');

    return <><PageStructuredData path={getShipmentHref(shipment)} fallbackTitle={title} fallbackDescription={description} article={{
        headline: shipment.title,
        description: shipment.summary,
        image: canonicalUrl(shipment.image),
        datePublished: publishedAt,
        dateModified: shipment.updatedAt ?? publishedAt,
    }} /><ShipmentDetailPageView shipment={shipment} /></>;
}
