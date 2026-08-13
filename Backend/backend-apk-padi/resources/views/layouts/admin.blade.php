<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Dashboard Admin' }} - P.A.D.I.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-padi-50 font-sans text-slate-800">

    @include('components.admin-sidebar')

    <div class="ml-64 min-h-screen">

        @include('components.admin-navbar')

        <main class="p-6 lg:p-8">
            @yield('content')
        </main>

    </div>

</body>
</html>
