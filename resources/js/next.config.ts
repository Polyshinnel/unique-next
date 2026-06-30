import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
    output: 'standalone',
    reactStrictMode: true,
    env: {
        NEXT_PUBLIC_YANDEX_MAPS_API_KEY:
            process.env.YANDEX_MAPS_API_KEY || 'ddda0c18-95d3-493d-820b-a7304bc04e5c',
    },
    async rewrites() {
        return [
            {
                source: '/api/:path*',
                destination: `${process.env.BACKEND_URL || 'http://127.0.0.1'}/api/:path*`,
            },
            {
                source: '/sanctum/:path*',
                destination: `${process.env.BACKEND_URL || 'http://127.0.0.1'}/sanctum/:path*`,
            },
        ];
    },
};

export default nextConfig;
