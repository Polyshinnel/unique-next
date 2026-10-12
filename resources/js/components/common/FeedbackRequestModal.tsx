'use client';

import { useState, type FormEvent, type ReactNode } from 'react';
import Link from 'next/link';
import { Button, Modal, Stack, Text, TextInput, Textarea, Title } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { IconSearch } from '@tabler/icons-react';
import { submitFeedback } from '@/lib/feedback';

type FeedbackRequestModalProps = {
    buttonLabel?: string;
    description?: string;
    modalTitle?: string;
    size?: 'compact' | 'md' | 'lg';
    buttonColor?: string;
    buttonVariant?: string;
    buttonClassName?: string;
    buttonLeftSection?: ReactNode;
    buttonRightSection?: ReactNode;
    initialMessage?: string;
};

export function FeedbackRequestModal({
    buttonLabel = 'Оставить заявку',
    description = 'Заполните форму, и мы свяжемся с вами для уточнения деталей.',
    modalTitle = 'Форма обратной связи',
    size = 'lg',
    buttonColor,
    buttonVariant,
    buttonClassName,
    buttonLeftSection = <IconSearch size={19} />,
    buttonRightSection,
    initialMessage,
}: FeedbackRequestModalProps) {
    const [opened, { open, close }] = useDisclosure(false);
    const [message, setMessage] = useState(initialMessage ?? '');
    const [pending, setPending] = useState(false);
    const [notice, setNotice] = useState('');

    const handleOpen = () => {
        setMessage(initialMessage ?? '');
        setNotice('');
        open();
    };

    const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setPending(true);
        setNotice('');
        const data = new FormData(event.currentTarget);
        try {
            await submitFeedback('Обратная связь', {
                'ФИО': String(data.get('name') ?? ''),
                'Телефон': String(data.get('phone') ?? ''),
                'Почта': String(data.get('email') ?? ''),
                'Сообщение': String(data.get('message') ?? ''),
            });
            setNotice('Заявка отправлена. Мы свяжемся с вами.');
            window.setTimeout(close, 1400);
        } catch (error) {
            setNotice(error instanceof Error ? error.message : 'Не удалось отправить заявку.');
        } finally {
            setPending(false);
        }
    };

    return (
        <>
            <Button
                size={size}
                color={buttonColor}
                variant={buttonVariant}
                className={buttonClassName}
                leftSection={buttonLeftSection}
                rightSection={buttonRightSection}
                onClick={handleOpen}
            >
                {buttonLabel}
            </Button>

            <Modal
                opened={opened}
                onClose={close}
                centered
                radius="lg"
                size="md"
                zIndex={10000}
                classNames={{
                    content: 'feedback-modal',
                    header: 'feedback-modal__header',
                    body: 'feedback-modal__body',
                    close: 'feedback-modal__close',
                }}
            >
                <form
                    className="feedback-form"
                    onSubmit={handleSubmit}
                >
                    <Stack gap="md">
                        <Stack gap="xs">
                            <Title order={3}>{modalTitle}</Title>
                            <Text c="dimmed" className="feedback-form__description">{description}</Text>
                        </Stack>

                        <TextInput name="name" label="ФИО" placeholder="Как к вам обращаться" required withAsterisk />
                        <TextInput name="phone" label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" required withAsterisk />
                        <TextInput name="email" label="Почта" placeholder="example@mail.ru" type="email" required withAsterisk />
                        <Textarea
                            name="message"
                            label="Сообщение"
                            placeholder="Опишите, какое оборудование или услуга вас интересует"
                            minRows={5}
                            value={message}
                            onChange={(event) => setMessage(event.currentTarget.value)}
                            required
                            withAsterisk
                        />

                        <Stack gap="sm">
                            {notice && <Text role="status" c={notice.startsWith('Не удалось') ? 'red' : 'green'}>{notice}</Text>}
                            <Button type="submit" size="lg" fullWidth loading={pending} disabled={pending}>
                                Отправить заявку
                            </Button>
                            <Text size="sm" c="dimmed" className="feedback-form__hint">
                                Нажимая кнопку, вы соглашаетесь с нашей{' '}
                                <Link href="/private-policy" className="feedback-form__link">
                                    Политикой конфиденциальности
                                </Link>
                                .
                            </Text>
                        </Stack>
                    </Stack>
                </form>
            </Modal>
        </>
    );
}
