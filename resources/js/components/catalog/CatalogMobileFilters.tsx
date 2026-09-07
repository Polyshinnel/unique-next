'use client';

import type { ReactNode } from 'react';
import { Button, Drawer } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { IconAdjustmentsHorizontal } from '@tabler/icons-react';

type CatalogMobileFiltersProps = {
    children: ReactNode;
};

export function CatalogMobileFilters({ children }: CatalogMobileFiltersProps) {
    const [opened, { open, close }] = useDisclosure(false);

    return (
        <div className="catalog-mobile-filters">
            <Button
                type="button"
                className="catalog-mobile-filters__trigger"
                leftSection={<IconAdjustmentsHorizontal size={18} stroke={1.8} />}
                onClick={open}
            >
                Фильтр
            </Button>
            <Drawer
                opened={opened}
                onClose={close}
                position="left"
                title="Фильтры"
                size="min(88vw, 360px)"
                closeButtonProps={{ 'aria-label': 'Закрыть фильтры' }}
                classNames={{ body: 'catalog-mobile-filters__body' }}
            >
                {children}
            </Drawer>
        </div>
    );
}
