import type { Metadata } from 'next';
import type { ReactNode } from 'react';

import { CookieConsentBanner } from '@/components/layout/CookieConsentBanner';
import { Providers } from './providers';
import './globals.css';

const metadataBaseUrl = process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:28080';

export const metadata: Metadata = {
    metadataBase: new URL(metadataBaseUrl),
    title: 'ЮНИК С - каталог промышленного оборудования',
    description: 'Продажа, подбор и сопровождение сделок с промышленным оборудованием.',
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
