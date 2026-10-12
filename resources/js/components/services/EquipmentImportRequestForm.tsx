import Link from 'next/link';
import { Button, Stack, Text, TextInput, Textarea, Title } from '@mantine/core';
import type { FormEventHandler } from 'react';
import { useState } from 'react';
import { submitFeedback } from '@/lib/feedback';

type EquipmentImportRequestFormProps = {
    title?: string;
    description?: string;
    submitLabel?: string;
    className?: string;
    titleId?: string;
    hint?: string;
};

export function EquipmentImportRequestForm({
    title = 'Оставьте заявку',
    description = 'Напишите, какое оборудование нужно привезти, и мы свяжемся с вами для уточнения деталей по поставке.',
    submitLabel = 'Отправить заявку',
    className,
    titleId,
    hint,
}: EquipmentImportRequestFormProps) {
    const [pending, setPending] = useState(false);
    const [notice, setNotice] = useState('');
    const handleSubmit: FormEventHandler<HTMLFormElement> = async (event) => {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        setPending(true);
        setNotice('');
        try {
            await submitFeedback('Заявка на импорт', {
                'Имя': String(data.get('name') ?? ''),
                'Email': String(data.get('email') ?? ''),
                'Телефон': String(data.get('phone') ?? ''),
                'Сообщение': String(data.get('message') ?? ''),
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
                    <Text className="contact-card__eyebrow">Заявка на импорт</Text>
                    <Title order={3} id={titleId}>
                        {title}
                    </Title>
                    <Text c="dimmed">{description}</Text>
                </Stack>

                <Stack gap="sm" mt="md">
                    <TextInput name="name" label="Имя" placeholder="Как к вам обращаться" />
                    <TextInput name="email" label="Email" placeholder="example@mail.ru" type="email" />
                    <TextInput name="phone" label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" />
                    <Textarea
                        name="message"
                        label="Сообщение"
                        placeholder="Опишите оборудование, страну поставки, сроки или дополнительные пожелания"
                        minRows={5}
                    />
                </Stack>

                <Stack gap="sm" mt="md">
                    {notice && <Text role="status" c={notice.startsWith('Не удалось') ? 'red' : 'green'}>{notice}</Text>}
                    <Button type="submit" fullWidth size="lg" loading={pending} disabled={pending}>
                        {submitLabel}
                    </Button>
                    <Text size="sm" c="dimmed" className="buyout-form-card__hint">
                        {hint ?? (
                            <>
                                Нажимая кнопку, вы соглашаетесь с нашей{' '}
                                <Link href="/private-policy" className="feedback-form__link">
                                    Политикой конфиденциальности
                                </Link>
                                .
                            </>
                        )}
                    </Text>
                </Stack>
            </Stack>
        </form>
    );
}
