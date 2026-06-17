'use client';

import type { ReactNode } from 'react';

import '@mantine/core/styles.css';
import '@mantine/notifications/styles.css';
import { MantineProvider, createTheme } from '@mantine/core';
import { ModalsProvider } from '@mantine/modals';
import { Notifications } from '@mantine/notifications';

const theme = createTheme({
    primaryColor: 'blue',
    fontFamily: 'Inter, sans-serif',
});

type ProvidersProps = {
    children: ReactNode;
};

export function Providers({ children }: ProvidersProps) {
    return (
        <MantineProvider theme={theme} defaultColorScheme="light">
            <ModalsProvider>
                <Notifications position="top-right" />
                {children}
            </ModalsProvider>
        </MantineProvider>
    );
}
