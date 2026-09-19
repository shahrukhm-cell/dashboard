<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Servico') }}</title>

        <link rel="stylesheet" href="{{ asset('css/backend.css') }}">
        <link rel="stylesheet" href="{{ asset('css/theme-enhancements.css') }}">
        <script src="{{ asset('js/backend.js') }}" defer></script>
    </head>
    <body data-tenant-color="#8b5cf6">
        <div class="auth-shell">
            <div class="auth-aside">
                <a class="brand" href="{{ url('/') }}"><span class="brand-mark">SF</span><span class="brand-name">Servico Fusion</span></a>
                <div class="auth-intro"><span class="eyebrow">The operating system for growth</span><h1>Build momentum<br><em>together.</em></h1><p>One calm, intelligent workspace for your team, customers, and next big move.</p></div>
            </div>
            <main class="auth-panel"><div class="auth-card">{{ $slot }}</div></main>
        </div>
    </body>
</html>
