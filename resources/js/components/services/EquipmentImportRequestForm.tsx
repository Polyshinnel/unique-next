import Link from 'next/link';
import { Button, Stack, Text, TextInput, Textarea, Title } from '@mantine/core';
import type { FormEventHandler } from 'react';

type EquipmentImportRequestFormProps = {
    title?: string;
    description?: string;
    submitLabel?: string;
    className?: string;
    titleId?: string;
    hint?: string;
    onSubmit?: FormEventHandler<HTMLFormElement>;
};

export function EquipmentImportRequestForm({
    title = 'Оставьте заявку',
    description = 'Напишите, какое оборудование нужно привезти, и мы свяжемся с вами для уточнения деталей по поставке.',
    submitLabel = 'Отправить заявку',
    className,
    titleId,
    hint,
    onSubmit,
}: EquipmentImportRequestFormProps) {
    return (
        <form className={className} onSubmit={onSubmit}>
            <Stack gap="md">
                <Stack gap="md">
                    <Text className="contact-card__eyebrow">Заявка на импорт</Text>
                    <Title order={3} id={titleId}>
                        {title}
                    </Title>
                    <Text c="dimmed">{description}</Text>
                </Stack>

                <Stack gap="sm" mt="md">
                    <TextInput label="Имя" placeholder="Как к вам обращаться" />
                    <TextInput label="Email" placeholder="example@mail.ru" type="email" />
                    <TextInput label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" />
                    <Textarea
                        label="Сообщение"
                        placeholder="Опишите оборудование, страну поставки, сроки или дополнительные пожелания"
                        minRows={5}
                    />
                </Stack>

                <Stack gap="sm" mt="md">
                    <Button type="submit" fullWidth size="lg">
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
