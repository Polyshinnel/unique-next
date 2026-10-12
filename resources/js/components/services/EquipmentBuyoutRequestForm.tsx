import Link from 'next/link';
import { Button, Stack, Text, TextInput, Textarea, Title } from '@mantine/core';
import type { FormEventHandler } from 'react';
import { useState } from 'react';
import { submitFeedback } from '@/lib/feedback';

type EquipmentBuyoutRequestFormProps = {
    title?: string;
    description?: string;
    submitLabel?: string;
    className?: string;
    titleId?: string;
};

export function EquipmentBuyoutRequestForm({
    title = 'Оставьте данные по оборудованию',
    description = 'Перезвоним, уточним детали и предложим удобный формат: выкуп или реализация по агентской схеме.',
    submitLabel = 'Отправить заявку',
    className,
    titleId,
}: EquipmentBuyoutRequestFormProps) {
    const [pending, setPending] = useState(false);
    const [notice, setNotice] = useState('');
    const handleSubmit: FormEventHandler<HTMLFormElement> = async (event) => {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        setPending(true);
        setNotice('');
        try {
            await submitFeedback('Заявка на выкуп', {
                'Имя': String(data.get('name') ?? ''),
                'Телефон': String(data.get('phone') ?? ''),
                'Регион': String(data.get('region') ?? ''),
                'Оборудование': String(data.get('equipment') ?? ''),
            });
            setNotice('Заявка отправлена. Мы свяжемся с вами.');
        } catch (error) {
            setNotice(error instanceof Error ? error.message : 'Не удалось отправить заявку.');
        } finally {
            setPending(false);
        }
    };
    return (
        <form className={className} onSubmit={handleSubmit}>
            <Stack gap="md">
                <Stack gap="md">
                    <Text className="contact-card__eyebrow">Заявка на оценку</Text>
                    <Title order={3} id={titleId}>
                        {title}
                    </Title>
                    <Text c="dimmed">{description}</Text>
                </Stack>

                <Stack gap="sm" mt="md">
                    <TextInput name="name" label="Ваше имя" placeholder="Как к вам обращаться" />
                    <TextInput name="phone" label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" />
                    <TextInput name="region" label="Регион" placeholder="Город или область" />
                    <Textarea
                        name="equipment"
                        label="Что хотите реализовать"
                        placeholder="Кратко опишите оборудование, количество, состояние"
                        minRows={5}
                    />
                </Stack>

                <Stack gap="sm" mt="md">
                    {notice && <Text role="status" c={notice.startsWith('Не удалось') ? 'red' : 'green'}>{notice}</Text>}
                    <Button type="submit" fullWidth size="lg" loading={pending} disabled={pending}>
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
