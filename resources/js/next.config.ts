import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
    output: 'standalone',
    reactStrictMode: true,
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
