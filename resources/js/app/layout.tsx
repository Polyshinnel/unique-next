import type { Metadata } from 'next';
import type { ReactNode } from 'react';
import Script from 'next/script';

import { CookieConsentBanner } from '@/components/layout/CookieConsentBanner';
import { siteConfig } from '@/lib/site-config';
import { withSocialMetadata } from '@/lib/seo';
import { Providers } from './providers';
import './globals.css';

export const metadata: Metadata = withSocialMetadata({
    metadataBase: new URL(siteConfig.appUrl),
    title: 'ЮНИК С - каталог промышленного оборудования',
    description: 'Продажа, подбор и сопровождение сделок с промышленным оборудованием.',
    icons: {
        icon: '/favicon.png',
        shortcut: '/favicon.png',
        apple: '/favicon.png',
    },
});

type RootLayoutProps = {
    children: ReactNode;
};

export default function RootLayout({ children }: RootLayoutProps) {
    return (
        <html lang="ru" suppressHydrationWarning>
            <body>
                <noscript><div><img src="https://mc.yandex.ru/watch/113672775" style={{ position: 'absolute', left: '-9999px' }} alt="" /></div></noscript>
                <Script id="yandex-metrika" strategy="beforeInteractive">
                    {`(function(m,e,t,r,i,k,a){
                        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                        m[i].l=1*new Date();
                        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
                        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
                    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=113672775', 'ym');

                    ym(113672775, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});`}
                </Script>
                <Providers>
                    {children}
                    <CookieConsentBanner />
                </Providers>
            </body>
        </html>
    );
}
