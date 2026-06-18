import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
    output: 'standalone',
    reactStrictMode: true,
    env: {
        NEXT_PUBLIC_MAPBOX_ACCESS_TOKEN: process.env.MAP_BOX_ACCESS_TOKEN,
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
