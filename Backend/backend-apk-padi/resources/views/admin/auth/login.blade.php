<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1b5e20">

    <title>{{ $title ?? 'Login Admin' }} | P.A.D.I.</title>

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('css/admin/auth.css') }}">
</head>

<body class="admin-auth">
    <main class="admin-auth__shell">
        <section class="admin-auth__panel" aria-labelledby="login-title">
            <header class="admin-auth__header">
                <div class="admin-auth__brand">
                    <img
                        src="{{ asset('images/padi-logo.jpeg') }}"
                        alt="P.A.D.I. Smart Farming"
                        class="admin-auth__logo"
                    >
                    <div>
                        <p>P.A.D.I. Smart Farming</p>
                        <h1 id="login-title">Admin Console</h1>
                    </div>
                </div>

                <p class="admin-auth__intro">
                    Masuk dengan akun admin aktif untuk mengelola operasional P.A.D.I.
                </p>
            </header>

            @if (session('status'))
                <div class="admin-auth__alert" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="admin-auth__alert admin-auth__alert--error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="admin-auth__form">
                @csrf

                <label class="admin-auth__field" for="email">
                    <span>Email admin</span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        inputmode="email"
                        placeholder="admin@padi.id"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        @error('email') aria-describedby="email-error" @enderror
                        required
                        autofocus
                    >
                </label>
                @error('email')
                    <p id="email-error" class="admin-auth__field-error">{{ $message }}</p>
                @enderror

                <label class="admin-auth__field" for="password">
                    <span>Password</span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        @error('password') aria-describedby="password-error" @enderror
                        required
                    >
                </label>
                @error('password')
                    <p id="password-error" class="admin-auth__field-error">{{ $message }}</p>
                @enderror

                <label class="admin-auth__remember">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                    >
                    <span>Ingat sesi admin</span>
                </label>

                <button type="submit" class="admin-auth__submit">Masuk Admin</button>
            </form>

            <footer class="admin-auth__footer">
                Akses internal P.A.D.I. Smart Farming
            </footer>
        </section>
    </main>
</body>

</html>
