<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#2d5a43">

    <title>{{ $title ?? 'Login Admin' }} | P.A.D.I. Smart Farming</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('css/admin/auth.css') }}">
</head>

<body class="admin-auth">
    <main class="admin-auth__shell">
        <div class="admin-auth__frame">
            <!-- Left Side: Authentication Form -->
            <section class="admin-auth__form-side" aria-labelledby="login-title">
                <div>
                    <header class="admin-auth__brand-header">
                        <div class="admin-auth__logo-row">
                            <img
                                src="{{ asset('images/padi-logo.png') }}"
                                alt="P.A.D.I."
                                class="admin-auth__logo"
                                onerror="this.onerror=null;this.src='{{ asset('images/padi-logo.jpeg') }}';"
                            >
                            <span class="admin-auth__brand-name">padi.id</span>
                        </div>
                        <h1 id="login-title" class="admin-auth__title">Admin Console</h1>
                        <p class="admin-auth__subtitle">
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

                    <!-- Segmented Role Switcher -->
                    <div class="admin-auth__roles" role="tablist" aria-label="Pilihan Peran Pengguna">
                        <button
                            type="button"
                            id="roleAdminBtn"
                            class="admin-auth__role-tab is-active"
                            role="tab"
                            aria-selected="true"
                            onclick="switchLoginRole('admin')"
                        >
                            Masuk sebagai Admin
                        </button>
                        <button
                            type="button"
                            id="rolePplBtn"
                            class="admin-auth__role-tab"
                            role="tab"
                            aria-selected="false"
                            onclick="switchLoginRole('ppl')"
                        >
                            Masuk sebagai PPL / Penyuluh
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.login.submit') }}" class="admin-auth__form">
                        @csrf

                        <!-- Email Input -->
                        <div class="admin-auth__field">
                            <label class="admin-auth__field-label" for="email" id="emailLabel">Email Admin / PPL</label>
                            <div class="admin-auth__input-wrap">
                                <input
                                    id="email"
                                    class="admin-auth__input"
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
                            </div>
                            @error('email')
                                <p id="email-error" class="admin-auth__field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="admin-auth__field">
                            <label class="admin-auth__field-label" for="password">Password</label>
                            <div class="admin-auth__input-wrap">
                                <input
                                    id="password"
                                    class="admin-auth__input has-toggle"
                                    type="password"
                                    name="password"
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                    @error('password') aria-describedby="password-error" @enderror
                                    required
                                >
                                <button
                                    type="button"
                                    class="admin-auth__toggle-pw"
                                    id="togglePwBtn"
                                    onclick="togglePasswordVisibility()"
                                    aria-label="Tampilkan atau sembunyikan password"
                                >
                                    <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p id="password-error" class="admin-auth__field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="admin-auth__remember-row">
                            <label class="admin-auth__remember">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                >
                                <span>Ingat sesi masuk</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submitBtn" class="admin-auth__submit">Masuk sebagai Admin</button>
                    </form>
                </div>

                <footer class="admin-auth__footer">
                    Akses internal P.A.D.I. Smart Farming (Admin & Penyuluh)
                </footer>
            </section>

            <!-- Right Side: Lush Rice Terrace Landscape Visual with Floating Glass Pins -->
            <aside class="admin-auth__landscape-side" aria-hidden="true">
                <!-- Pin 1: Subak Jatiluwih Location Card -->
                <div class="admin-auth__pin-1">
                    <div class="admin-auth__glass-card">
                        <div class="admin-auth__glass-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </div>
                        <div>
                            <p class="admin-auth__glass-title">Hamparan Subak</p>
                            <p class="admin-auth__glass-value">Sawah Jatiluwih</p>
                        </div>
                    </div>
                    <div class="admin-auth__pin-line"></div>
                    <div class="admin-auth__pin-dot"></div>
                </div>

                <!-- Pin 2: Telemetry Telemetry Glass Box -->
                <div class="admin-auth__pin-2">
                    <div class="admin-auth__glass-card--large">
                        <p class="admin-auth__telemetry-value">12.450 Ha</p>
                        <p class="admin-auth__telemetry-sub">Terpantau Satelit Sentinel-2 & Telemetri IoT</p>
                    </div>
                    <div class="admin-auth__pin-line" style="height: 24px; margin-left: 24px;"></div>
                    <div class="admin-auth__pin-dot" style="margin-left: 21px;"></div>
                </div>

                <!-- Pin 3: Trail Pill Badge on Winding Path -->
                <div class="admin-auth__pin-3">
                    <div class="admin-auth__trail-pill">
                        <span class="admin-auth__trail-dot"></span>
                        <span>Jalur Monitoring PPL</span>
                    </div>
                    <div class="admin-auth__pin-line" style="height: 20px;"></div>
                    <div class="admin-auth__pin-dot"></div>
                </div>
            </aside>
        </div>
    </main>

    <script>
        function switchLoginRole(role) {
            const adminBtn = document.getElementById('roleAdminBtn');
            const pplBtn = document.getElementById('rolePplBtn');
            const emailInput = document.getElementById('email');
            const submitBtn = document.getElementById('submitBtn');

            if (role === 'ppl') {
                pplBtn.classList.add('is-active');
                pplBtn.setAttribute('aria-selected', 'true');
                adminBtn.classList.remove('is-active');
                adminBtn.setAttribute('aria-selected', 'false');
                emailInput.placeholder = 'penyuluh@padi.id';
                submitBtn.textContent = 'Masuk sebagai PPL / Penyuluh';
            } else {
                adminBtn.classList.add('is-active');
                adminBtn.setAttribute('aria-selected', 'true');
                pplBtn.classList.remove('is-active');
                pplBtn.setAttribute('aria-selected', 'false');
                emailInput.placeholder = 'admin@padi.id';
                submitBtn.textContent = 'Masuk sebagai Admin';
            }
        }

        function togglePasswordVisibility() {
            const pwInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const isPassword = pwInput.type === 'password';

            pwInput.type = isPassword ? 'text' : 'password';

            if (isPassword) {
                // Sembunyikan (Eye Off Icon)
                eyeIcon.innerHTML = `
                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                    <line x1="2" y1="2" x2="22" y2="22"></line>
                `;
            } else {
                // Tampilkan (Eye Icon)
                eyeIcon.innerHTML = `
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        }
    </script>
</body>

</html>
