'use client';

import { type ReactNode } from 'react';
import { Button, Modal, type ButtonProps } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { EquipmentBuyoutRequestForm } from '@/components/services/EquipmentBuyoutRequestForm';

type EquipmentBuyoutRequestModalButtonProps = {
    buttonLabel?: string;
    size?: ButtonProps['size'];
    variant?: ButtonProps['variant'];
    color?: ButtonProps['color'];
    className?: string;
    leftSection?: ReactNode;
    rightSection?: ReactNode;
};

export function EquipmentBuyoutRequestModalButton({
    buttonLabel = 'Оставить заявку',
    size = 'lg',
    variant,
    color,
    className,
    leftSection,
    rightSection,
}: EquipmentBuyoutRequestModalButtonProps) {
    const [opened, { open, close }] = useDisclosure(false);

    return (
        <>
            <Button
                size={size}
                variant={variant}
                color={color}
                className={className}
                leftSection={leftSection}
                rightSection={rightSection}
                onClick={open}
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
                <EquipmentBuyoutRequestForm
                    onSubmit={(event) => {
                        event.preventDefault();
                        close();
                    }}
                />
            </Modal>
        </>
    );
}
