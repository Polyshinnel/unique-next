'use client';

import { useState, type FormEvent } from 'react';
import { Button, Text, TextInput } from '@mantine/core';
import { submitFeedback } from '@/lib/feedback';

export function FooterCallbackForm() {
    const [pending, setPending] = useState(false);
    const [notice, setNotice] = useState('');

    const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const formElement = event.currentTarget;
        const data = new FormData(formElement);
        setPending(true);
        setNotice('');
        try {
            await submitFeedback('Обратный звонок', { 'Телефон': String(data.get('phone') ?? '') });
            setNotice('Заявка отправлена. Мы вам позвоним.');
            formElement.reset();
        } catch (error) {
            setNotice(error instanceof Error ? error.message : 'Не удалось отправить заявку.');
        } finally {
            setPending(false);
        }
    };

    return (
        <>
            <form className="footer-request" onSubmit={handleSubmit}>
                <TextInput name="phone" placeholder="Ваш телефон" aria-label="Телефон для обратного звонка" type="tel" required />
                <Button type="submit" loading={pending} disabled={pending}>Позвоните мне</Button>
            </form>
            {notice && <Text size="sm" role="status" c={notice.startsWith('Не удалось') ? 'red' : 'green'}>{notice}</Text>}
        </>
    );
}
