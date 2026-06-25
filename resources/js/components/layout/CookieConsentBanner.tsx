'use client';

import { useEffect, useState } from 'react';

import { Anchor, Button, Text } from '@mantine/core';

const COOKIE_NAME = 'cookie_consent_accepted';
const COOKIE_MAX_AGE_SECONDS = 60 * 60 * 24 * 61;

function hasCookieConsent(): boolean {
    if (typeof document === 'undefined') {
        return false;
    }

    return document.cookie
        .split('; ')
        .some((cookie) => cookie.startsWith(`${COOKIE_NAME}=`));
}

function persistCookieConsent() {
    document.cookie = `${COOKIE_NAME}=1; max-age=${COOKIE_MAX_AGE_SECONDS}; path=/; samesite=lax`;
}

export function CookieConsentBanner() {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        setIsVisible(!hasCookieConsent());
    }, []);

    const handleAccept = () => {
        persistCookieConsent();
        setIsVisible(false);
    };

    if (!isVisible) {
        return null;
    }

    return (
        <div className="cookie-consent-banner" role="dialog" aria-live="polite" aria-label="Уведомление об использовании cookie">
            <Text size="sm" className="cookie-consent-banner__text">
                Сайт использует cookie-файлы. Пользуясь сайтом, вы принимаете{' '}
                <Anchor href="/private-policy" className="cookie-consent-banner__link">
                    Политику конфиденциальности
                </Anchor>
            </Text>
            <Button size="sm" className="cookie-consent-banner__action" onClick={handleAccept}>
                Принимаю
            </Button>
        </div>
    );
}
