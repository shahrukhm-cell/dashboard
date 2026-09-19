<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Servico Fusion' }} | {{ config('app.name', 'Servico Fusion') }}</title>
    <link rel="stylesheet" href="{{ asset('css/backend.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme-enhancements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/management.css') }}">
    <script src="{{ asset('js/backend.js') }}" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.css" integrity="sha512-x9WwyMYBnlXMNQ6kQ/Lyzu1NqIhLQKL5Oq6xByfXuRj7s9CskyCbLv/1IjqzJmXwFXWr0ov6jBV7Qbc0hh9nHg==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body data-tenant-id="{{ $currentTenant?->id ?? 'guest' }}" data-tenant-color="{{ $currentTenant?->themeColor() ?? '#8b5cf6' }}" data-tenant-secondary-color="{{ $currentTenant?->secondaryColor() ?? '#22c55e' }}">
    <div class="background-layer"></div>
    <button class="menu-toggle" type="button" aria-label="Open navigation" data-menu-toggle>
        <span></span><span></span><span></span>
    </button>
    <div class="overlay" data-overlay></div>

    <div class="app-shell">
        @include('layouts.backend.sidebar')
        <main class="main-content">
            @include('layouts.backend.header')
            {{ $slot }}
        </main>
    </div>
</body>
</html>

