'use client';

import React, { useEffect, useState } from 'react';
import * as ReactDOM from 'react-dom';
import { siteConfig } from '@/lib/site-config';

declare global {
    interface Window {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        ymaps3?: any;
    }
}

const SCRIPT_ID = 'yandex-maps-v3';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function loadYmaps3(apiKey: string): Promise<any> {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('Yandex Maps can only be loaded in the browser'));
    }

    if (window.ymaps3) {
        return window.ymaps3.ready.then(() => window.ymaps3);
    }

    return new Promise((resolve, reject) => {
        let script = document.getElementById(SCRIPT_ID) as HTMLScriptElement | null;

        const handleLoad = () => {
            window.ymaps3.ready.then(() => resolve(window.ymaps3)).catch(reject);
        };

        if (!script) {
            script = document.createElement('script');
            script.id = SCRIPT_ID;
            script.src = `https://api-maps.yandex.ru/v3/?apikey=${apiKey}&lang=ru_RU`;
            script.async = true;
            script.addEventListener('load', handleLoad);
            script.addEventListener('error', () => reject(new Error('Failed to load Yandex Maps script')));
            document.head.appendChild(script);
        } else if (window.ymaps3) {
            handleLoad();
        } else {
            script.addEventListener('load', handleLoad);
            script.addEventListener('error', () => reject(new Error('Failed to load Yandex Maps script')));
        }
    });
}

type MapComponents = {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    YMap: any;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    YMapDefaultSchemeLayer: any;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    YMapDefaultFeaturesLayer: any;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    YMapMarker: any;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    reactify: any;
};

type YandexMapProps = {
    longitude: number;
    latitude: number;
    zoom?: number;
    title?: string;
    className?: string;
};

function YandexMapInner({
    components,
    longitude,
    latitude,
    zoom,
    title,
    className,
}: YandexMapProps & { components: MapComponents }) {
    const {
        YMap,
        YMapDefaultSchemeLayer,
        YMapDefaultFeaturesLayer,
        YMapMarker,
        reactify,
    } = components;

    const location = reactify.useDefault({ center: [longitude, latitude], zoom });
    const markerCoordinates = [longitude, latitude] as [number, number];

    return (
        <div className={className}>
            <YMap location={location} className="yandex-map">
                <YMapDefaultSchemeLayer />
                <YMapDefaultFeaturesLayer />
                <YMapMarker coordinates={markerCoordinates} zIndex={1}>
                    <div className="yandex-map__pin">
                        {/* eslint-disable-next-line @next/next/no-img-element */}
                        <img
                            className="yandex-map__marker"
                            src="/assets/img/map-pin.png"
                            alt={title ?? 'Метка на карте'}
                            title={title}
                        />
                    </div>
                </YMapMarker>
            </YMap>
        </div>
    );
}

export function YandexMap(props: YandexMapProps) {
    const { className } = props;
    const apiKey = siteConfig.yandexMapsApiKey;
    const [components, setComponents] = useState<MapComponents | null>(null);
    const [hasError, setHasError] = useState(false);

    useEffect(() => {
        if (!apiKey) {
            return;
        }

        let active = true;

        loadYmaps3(apiKey)
            .then(async (ymaps3) => {
                const ymaps3React = await ymaps3.import('@yandex/ymaps3-reactify');
                const reactify = ymaps3React.reactify.bindTo(React, ReactDOM);
                const mod = reactify.module(ymaps3);

                if (active) {
                    setComponents({ ...mod, reactify });
                }
            })
            .catch(() => {
                if (active) {
                    setHasError(true);
                }
            });

        return () => {
            active = false;
        };
    }, [apiKey]);

    if (hasError || !apiKey) {
        return (
            <div className={className}>
                <div className="yandex-map__fallback">Не удалось загрузить карту.</div>
            </div>
        );
    }

    if (!components) {
        return (
            <div className={className}>
                <div className="yandex-map__loading">Загрузка карты…</div>
            </div>
        );
    }

    return <YandexMapInner {...props} components={components} />;
}
