<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="admin-user-id" content="{{ auth()->id() }}">

    <title>{{ $title ?? 'Dashboard Admin' }} - P.A.D.I.</title>

    <link rel="icon" type="image/png" href="{{ asset('images/padi-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/padi-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/padi-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/admin/sidebar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin/navbar.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin/theme.css') }}?v={{ time() }}">
    <script src="{{ asset('js/pwa-notification-sw.js') }}?v={{ time() }}"></script>

    @stack('styles')
</head>

<body class="admin-body">

    @include('components.admin-sidebar')

    <div class="admin-main">

        @include('components.admin-navbar')

        <main class="admin-content">
            @yield('content')
        </main>

    </div>

    @stack('scripts')
</body>

</html>
