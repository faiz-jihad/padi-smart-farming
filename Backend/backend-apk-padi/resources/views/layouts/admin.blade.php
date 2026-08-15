<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Dashboard Admin' }} - P.A.D.I.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/admin/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
</head>

<body class="admin-body">

    @include('components.admin-sidebar')

    <div class="admin-main">

        @include('components.admin-navbar')

        <main class="admin-content">
            @yield('content')
        </main>

    </div>

</body>

</html>
