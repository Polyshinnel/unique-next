'use client';

import type { ComponentProps, ReactNode } from 'react';
import { Button, Modal, SimpleGrid, Stack, Text, TextInput, Textarea, Title } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { IconSearch } from '@tabler/icons-react';

type FeedbackRequestModalProps = {
    buttonLabel?: string;
    description?: string;
    modalTitle?: string;
    size?: 'compact' | 'md' | 'lg';
    buttonColor?: ComponentProps<typeof Button>['color'];
    buttonVariant?: ComponentProps<typeof Button>['variant'];
    buttonLeftSection?: ReactNode;
};

export function FeedbackRequestModal({
    buttonLabel = 'Оставить заявку',
    description = 'Заполните форму, и мы свяжемся с вами для уточнения деталей.',
    modalTitle = 'Форма обратной связи',
    size = 'lg',
    buttonColor,
    buttonVariant,
    buttonLeftSection = <IconSearch size={19} />,
}: FeedbackRequestModalProps) {
    const [opened, { open, close }] = useDisclosure(false);

    return (
        <>
            <Button size={size} color={buttonColor} variant={buttonVariant} leftSection={buttonLeftSection} onClick={open}>
                {buttonLabel}
            </Button>

            <Modal
                opened={opened}
                onClose={close}
                centered
                radius="lg"
                size="lg"
                title={<Text fw={800}>Обратная связь</Text>}
                classNames={{
                    content: 'feedback-modal',
                    header: 'feedback-modal__header',
                    body: 'feedback-modal__body',
                }}
            >
                <form
                    className="feedback-form"
                    onSubmit={(event) => {
                        event.preventDefault();
                        close();
                    }}
                >
                    <Stack gap="xl">
                        <Stack gap="xs">
                            <Title order={3}>{modalTitle}</Title>
                            <Text c="dimmed">{description}</Text>
                        </Stack>

                        <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
                            <TextInput label="ФИО" placeholder="Как к вам обращаться" withAsterisk />
                            <TextInput label="Телефон" placeholder="+7 (___) ___-__-__" type="tel" withAsterisk />
                        </SimpleGrid>

                        <TextInput label="Почта" placeholder="example@mail.ru" type="email" withAsterisk />
                        <Textarea
                            label="Сообщение"
                            placeholder="Опишите, какое оборудование или услуга вас интересует"
                            minRows={5}
                            withAsterisk
                        />

                        <Stack gap="sm">
                            <Button type="submit" size="lg" fullWidth>
                                Отправить заявку
                            </Button>
                            <Text size="sm" c="dimmed" className="feedback-form__hint">
                                Нажимая кнопку, вы соглашаетесь на обработку персональных данных.
                            </Text>
                        </Stack>
                    </Stack>
                </form>
            </Modal>
        </>
    );
}
