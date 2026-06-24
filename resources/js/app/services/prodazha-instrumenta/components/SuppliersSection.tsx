import { Stack, Text, Title } from '@mantine/core';
import { IconBuildingFactory2 } from '@tabler/icons-react';

const suppliers = [
    'ООО НПК "Томский инструмент" (Томск)',
    'ООО ПО "Волжский инструмент" (Самара)',
    'ООО "Хоффманн Проф. Инструмент" (Санкт-Петербург)',
    'ООО "Белый медведь" (Нижний Новгород)',
    'ООО "Промцентр" (Чебоксары)',
    'ООО "Алмазный Инструмент" (Белгород)',
    'ОАО "Суксунский ОМЗ" (Пермский край)',
    'АО "Канашский завод резцов" (Канаш)',
    'АО "СТИЗ" (Ставрополь)',
    'ООО "Нэфис Косметик" (Казань)',
    'Волжский абразивный завод',
    'ООО "Техоснастка-С" (Саранск)',
    'ООО ПО "Инреко" (Йошкар-Ола)',
    'Электроинструмент марки "Интерскол"',
] as const;

export function SuppliersSection() {
    return (
        <section className="instrument-card">
            <Stack gap="xl">
                <div className="instrument-section-header">
                    <span className="sales-search-card__icon">
                        <IconBuildingFactory2 size={24} />
                    </span>
                    <Title order={2}>От самых популярных поставщиков</Title>
                </div>
                <Stack gap="md">
                    {suppliers.map((supplier) => (
                        <div key={supplier} className="sales-list-item sales-list-item--fullwidth">
                            <span className="sales-list-item__dot" />
                            <Text>{supplier}</Text>
                        </div>
                    ))}
                </Stack>
            </Stack>
        </section>
    );
}
