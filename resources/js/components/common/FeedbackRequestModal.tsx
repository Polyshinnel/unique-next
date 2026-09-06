'use client';

import { useState, type ReactNode } from 'react';
import Link from 'next/link';
import { Button, Modal, Stack, Text, TextInput, Textarea, Title } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { IconSearch } from '@tabler/icons-react';

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

    const handleOpen = () => {
        setMessage(initialMessage ?? '');
        open();
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
                classNames={{
                    content: 'feedback-modal',
                    header: 'feedback-modal__header',
                    body: 'feedback-modal__body',
                    close: 'feedback-modal__close',
                }}
            >
                <form
                    className="feedback-form"
                    onSubmit={(event) => {
                        event.preventDefault();
                        close();
                    }}
                >
                    <Stack gap="md">
                        <Stack gap="xs">
                            <Title order={3}>{modalTitle}</Title>
                            <Text c="dimmed" className="feedback-form__description">{description}</Text>
                        </Stack>

                        <TextInput label="ФИО" placeholder="Как к вам обращаться" withAsterisk />
                        <TextInput label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" withAsterisk />
                        <TextInput label="Почта" placeholder="example@mail.ru" type="email" withAsterisk />
                        <Textarea
                            label="Сообщение"
                            placeholder="Опишите, какое оборудование или услуга вас интересует"
                            minRows={5}
                            value={message}
                            onChange={(event) => setMessage(event.currentTarget.value)}
                            withAsterisk
                        />

                        <Stack gap="sm">
                            <Button type="submit" size="lg" fullWidth>
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
