const API_URL = process.env.NEXT_PUBLIC_API_URL || '/api';
const BACKEND_URL = process.env.BACKEND_URL || 'http://127.0.0.1';

type ApiParamValue = string | number | boolean | null | undefined;
type ApiRequestBody = BodyInit | Record<string, unknown> | null | undefined;

export interface ApiOptions extends Omit<RequestInit, 'body'> {
    body?: ApiRequestBody;
    params?: Record<string, ApiParamValue>;
}

// SSR-запросы идут на nginx внутри app-контейнера.
export function getServerApiUrl(): string {
    return `${BACKEND_URL}/api`;
}

// Браузерные запросы используют same-origin proxy.
export function getClientApiUrl(): string {
    return API_URL;
}

function buildApiUrl(baseUrl: string, endpoint: string): URL {
    const url = `${baseUrl}${endpoint}`;

    if (url.startsWith('http://') || url.startsWith('https://')) {
        return new URL(url);
    }

    const origin =
        typeof window === 'undefined' ? 'http://localhost:28080' : window.location.origin;

    return new URL(url, origin);
}

function buildRequestBody(body: ApiRequestBody): BodyInit | undefined {
    if (body == null) {
        return undefined;
    }

    if (
        typeof body === 'string' ||
        body instanceof FormData ||
        body instanceof URLSearchParams ||
        body instanceof Blob ||
        body instanceof ArrayBuffer
    ) {
        return body;
    }

    return JSON.stringify(body);
}

function shouldSetJsonContentType(body: ApiRequestBody): boolean {
    return !(body == null || body instanceof FormData || body instanceof URLSearchParams);
}

async function parseResponse<T>(response: Response): Promise<T> {
    if (response.status === 204) {
        return undefined as T;
    }

    const contentType = response.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
        return (await response.json()) as T;
    }

    return (await response.text()) as T;
}

// Получить CSRF-cookie от Sanctum перед мутирующим запросом.
export async function getCsrfToken(): Promise<void> {
    const response = await fetch('/sanctum/csrf-cookie', {
        credentials: 'include',
    });

    if (!response.ok) {
        throw new Error(`CSRF Error: ${response.status} ${response.statusText}`);
    }
}

export async function apiRequest<T>(
    endpoint: string,
    options: ApiOptions = {},
    isServer = false,
): Promise<T> {
    const { params, headers, body, ...requestInit } = options;
    const baseUrl = isServer ? getServerApiUrl() : getClientApiUrl();
    const url = buildApiUrl(baseUrl, endpoint);

    if (params) {
        Object.entries(params).forEach(([key, value]) => {
            if (value != null) {
                url.searchParams.append(key, String(value));
            }
        });
    }

    const preparedBody = buildRequestBody(body);
    const requestHeaders = new Headers(headers);

    if (!requestHeaders.has('Accept')) {
        requestHeaders.set('Accept', 'application/json');
    }

    if (shouldSetJsonContentType(body) && !requestHeaders.has('Content-Type')) {
        requestHeaders.set('Content-Type', 'application/json');
    }

    const response = await fetch(url.toString(), {
        ...requestInit,
        body: preparedBody,
        credentials: isServer ? 'omit' : 'include',
        headers: requestHeaders,
    });

    if (!response.ok) {
        throw new Error(`API Error: ${response.status} ${response.statusText}`);
    }

    return parseResponse<T>(response);
}

export const api = {
    get: <T>(endpoint: string, options?: ApiOptions) =>
        apiRequest<T>(endpoint, { ...options, method: 'GET' }),

    post: <T>(
        endpoint: string,
        data?: ApiRequestBody,
        options?: ApiOptions,
    ) => apiRequest<T>(endpoint, { ...options, method: 'POST', body: data }),

    put: <T>(
        endpoint: string,
        data?: ApiRequestBody,
        options?: ApiOptions,
    ) => apiRequest<T>(endpoint, { ...options, method: 'PUT', body: data }),

    patch: <T>(
        endpoint: string,
        data?: ApiRequestBody,
        options?: ApiOptions,
    ) => apiRequest<T>(endpoint, { ...options, method: 'PATCH', body: data }),

    delete: <T>(endpoint: string, options?: ApiOptions) =>
        apiRequest<T>(endpoint, { ...options, method: 'DELETE' }),

    server: {
        get: <T>(endpoint: string, options?: ApiOptions) =>
            apiRequest<T>(endpoint, { ...options, method: 'GET' }, true),
    },
};
