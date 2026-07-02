import type { Metadata } from 'next';
import type { ReactNode } from 'react';

import { CookieConsentBanner } from '@/components/layout/CookieConsentBanner';
import { siteConfig } from '@/lib/site-config';
import { Providers } from './providers';
import './globals.css';

export const metadata: Metadata = {
    metadataBase: new URL(siteConfig.appUrl),
    title: 'ЮНИК С - каталог промышленного оборудования',
    description: 'Продажа, подбор и сопровождение сделок с промышленным оборудованием.',
    icons: {
        icon: '/favicon.png',
        shortcut: '/favicon.png',
        apple: '/favicon.png',
    },
};

type RootLayoutProps = {
    children: ReactNode;
};

export default function RootLayout({ children }: RootLayoutProps) {
    return (
        <html lang="ru" suppressHydrationWarning>
            <body>
                <Providers>
                    {children}
                    <CookieConsentBanner />
                </Providers>
            </body>
        </html>
    );
}
