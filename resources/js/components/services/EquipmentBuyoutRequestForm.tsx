import Link from 'next/link';
import { Button, Stack, Text, TextInput, Textarea, Title } from '@mantine/core';
import type { FormEventHandler } from 'react';

type EquipmentBuyoutRequestFormProps = {
    title?: string;
    description?: string;
    submitLabel?: string;
    className?: string;
    titleId?: string;
    onSubmit?: FormEventHandler<HTMLFormElement>;
};

export function EquipmentBuyoutRequestForm({
    title = 'Оставьте данные по оборудованию',
    description = 'Перезвоним, уточним детали и предложим удобный формат: выкуп или реализация по агентской схеме.',
    submitLabel = 'Отправить заявку',
    className,
    titleId,
    onSubmit,
}: EquipmentBuyoutRequestFormProps) {
    return (
        <form className={className} onSubmit={onSubmit}>
            <Stack gap="md">
                <Stack gap="md">
                    <Text className="contact-card__eyebrow">Заявка на оценку</Text>
                    <Title order={3} id={titleId}>
                        {title}
                    </Title>
                    <Text c="dimmed">{description}</Text>
                </Stack>

                <Stack gap="sm" mt="md">
                    <TextInput label="Ваше имя" placeholder="Как к вам обращаться" />
                    <TextInput label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" />
                    <TextInput label="Регион" placeholder="Город или область" />
                    <Textarea
                        label="Что хотите реализовать"
                        placeholder="Кратко опишите оборудование, количество, состояние"
                        minRows={5}
                    />
                </Stack>

                <Stack gap="sm" mt="md">
                    <Button type="submit" fullWidth size="lg">
                        {submitLabel}
                    </Button>
                    <Text size="sm" c="dimmed" className="buyout-form-card__hint">
                        Нажимая кнопку, вы соглашаетесь с нашей{' '}
                        <Link href="/private-policy" className="feedback-form__link">
                            Политикой конфиденциальности
                        </Link>
                        .
                    </Text>
                </Stack>
            </Stack>
        </form>
    );
}
