<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'uniqset2') }}</title>
        <style>
            :root {
                color-scheme: light;
                font-family: Arial, sans-serif;
            }

            body {
                margin: 0;
                background: #f5f3ef;
                color: #1d1b18;
            }

            main {
                max-width: 720px;
                margin: 0 auto;
                padding: 64px 24px;
            }

            h1 {
                margin: 0 0 16px;
                font-size: 40px;
                line-height: 1.1;
            }

            p {
                margin: 0 0 12px;
                font-size: 18px;
                line-height: 1.6;
            }

            code {
                padding: 2px 6px;
                border-radius: 6px;
                background: #e7e1d8;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>uniqset2 backend is running</h1>
            <p>Laravel serves API and infrastructure endpoints from this container.</p>
            <p>The Next.js frontend lives in <code>resources/js</code> and runs via <code>npm run dev -- --hostname 0.0.0.0 --port 3000</code>.</p>
        </main>
    </body>
</html>
