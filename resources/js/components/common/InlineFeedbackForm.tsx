'use client';

import { useState, type FormEvent } from 'react';
import { Button, Stack, Text, TextInput, Textarea } from '@mantine/core';
import { submitFeedback } from '@/lib/feedback';

export function InlineFeedbackForm() {
    const [pending, setPending] = useState(false);
    const [notice, setNotice] = useState('');

    const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const formElement = event.currentTarget;
        const data = new FormData(formElement);
        setPending(true);
        setNotice('');
        try {
            await submitFeedback('Обратная связь', {
                'Имя': String(data.get('name') ?? ''),
                'Email': String(data.get('email') ?? ''),
                'Телефон': String(data.get('phone') ?? ''),
                'Сообщение': String(data.get('message') ?? ''),
            });
            setNotice('Сообщение отправлено. Мы свяжемся с вами.');
            formElement.reset();
        } catch (error) {
            setNotice(error instanceof Error ? error.message : 'Не удалось отправить заявку.');
        } finally {
            setPending(false);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <Stack gap="sm" mt="xl">
                <TextInput name="name" label="Имя" placeholder="Как к вам обращаться" required />
                <TextInput name="email" label="Email" placeholder="example@mail.ru" type="email" />
                <TextInput name="phone" label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" />
                <Textarea name="message" label="Сообщение" placeholder="Напишите, какое оборудование вас интересует и какие вопросы нужно решить" minRows={5} required />
            </Stack>
            {notice && <Text role="status" mt="sm" c={notice.startsWith('Не удалось') ? 'red' : 'green'}>{notice}</Text>}
            <Button type="submit" size="lg" mt="xl" loading={pending} disabled={pending}>
                Отправить сообщение
            </Button>
        </form>
    );
}
