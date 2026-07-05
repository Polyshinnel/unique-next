export type CatalogPrice = {
    amount: number | string | null;
    isPublished: boolean;
    isReserve?: boolean;
    isSold?: boolean;
    label?: string | null;
    comment?: string | null;
};

export function formatCatalogPrice(price: CatalogPrice | null | undefined): string {
    if (price?.isSold) {
        return 'Продано';
    }

    if (price?.label) {
        return price.label;
    }

    if (price?.isReserve) {
        return 'Резерв';
    }

    if (!price?.isPublished || price.amount === null) {
        return 'По запросу';
    }

    const amount = Math.trunc(Number(price.amount));

    if (!Number.isFinite(amount)) {
        return 'По запросу';
    }

    return `${String(amount).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')} ₽`;
}

export function formatCatalogCardTitle(title: string): string {
    const maxLength = 50;
    const characters = Array.from(title);

    if (characters.length <= maxLength) {
        return title;
    }

    return `${characters.slice(0, maxLength).join('').trimEnd()}...`;
}
