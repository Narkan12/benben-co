<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login pelanggan BENBEN CO. Bakery.">
    <title>Login Pelanggan — BENBEN CO. Bakery</title>
    <script src="{{ asset('js/theme.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="icon" href="{{ route('media.show', ['path' => 'images/logo.png']) }}" type="image/png">
</head>
<body class="bb-staff-login bb-customer-login">
    <x-theme-toggle />
    <main class="bb-staff-login-layout">
        <section class="bb-staff-login-story" aria-label="BENBEN CO. Bakery">
            <div class="bb-staff-login-photo" role="img" aria-label="Produk BENBEN CO. Bakery"
                 style="background-image:linear-gradient(180deg,rgba(36,20,14,.08) 10%,rgba(36,20,14,.78) 100%),url('{{ route('media.show', ['path' => 'images/bolen-lilit.png']) }}');">
                <a href="{{ route('home') }}" class="bb-staff-login-brand">
                    <span class="bb-staff-login-mark"><i class="bi bi-egg-fried" aria-hidden="true"></i></span>
                    <span>BENBEN CO.<small>BAKERY · ANTAPANI</small></span>
                </a>
                <div class="bb-staff-login-story-copy">
                    <span class="bb-staff-login-eyebrow">Area pelanggan BENBEN CO.</span>
                    <h1>Pesanan manis,<br><em>lebih dekat dengan Anda.</em></h1>
                    <p>Masuk untuk melihat riwayat pesanan, status pembayaran, dan perkembangan pesanan Anda.</p>
                    <span class="bb-staff-login-caption"><i class="bi bi-heart" aria-hidden="true"></i> Dibuat fresh dengan hati</span>
                </div>
            </div>
        </section>

        <section class="bb-staff-login-main">
            <div class="bb-staff-login-card">
                <a href="{{ route('home') }}" class="bb-staff-login-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke website</a>

                <div class="bb-staff-login-heading">
                    <span class="bb-staff-login-eyebrow">Akun pelanggan</span>
                    <h2>Selamat datang<br>kembali.</h2>
                    <p>Masuk untuk melanjutkan belanja dan memantau pesanan Anda.</p>
                </div>

                @if ($errors->any() || session('error') || session('success'))
                    <div class="bb-staff-login-alert {{ session('success') ? 'is-success' : '' }}" role="alert">
                        <i class="bi {{ session('success') ? 'bi-check-circle' : 'bi-exclamation-circle' }}" aria-hidden="true"></i>
                        <span>{{ session('success') ?? session('error') ?? $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="bb-staff-login-form">
                    @csrf
                    <div class="bb-staff-login-field">
                        <label for="username">Username atau Email</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <input id="username" type="text" name="username" value="{{ old('username') }}"
                                   placeholder="Masukkan username atau email" autocomplete="username" required autofocus>
                        </div>
                    </div>

                    <div class="bb-staff-login-field">
                        <label for="password">Password</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input id="password" type="password" name="password" placeholder="Masukkan password"
                                   autocomplete="current-password" required>
                            <button type="button" class="bb-password-toggle" data-password-toggle data-target="password" aria-label="Tampilkan password">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="bb-staff-login-submit" data-loading-button>
                        <span>Masuk sebagai pelanggan</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="bb-auth-divider" aria-label="atau"><span>OR</span></div>
                <a href="{{ route('google.redirect', ['intent' => 'customer']) }}" class="bb-google-button">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.35 12.2c0-.74-.07-1.45-.19-2.13H12v4.03h5.25a4.49 4.49 0 0 1-1.95 2.95v2.68h3.16c1.85-1.7 2.89-4.21 2.89-7.53Z"/><path fill="#34A853" d="M12 22c2.64 0 4.85-.88 6.47-2.27l-3.16-2.68c-.88.59-2 0.94-3.31.94-2.55 0-4.71-1.72-5.48-4.04H3.25v2.76A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.52 13.95A6 6 0 0 1 6.2 12c0-.68.12-1.34.32-1.95V7.29H3.25A10 10 0 0 0 2 12c0 1.61.38 3.13 1.25 4.71l3.27-2.76Z"/><path fill="#EA4335" d="M12 6.01c1.44 0 2.72.49 3.73 1.45l2.81-2.81A9.65 9.65 0 0 0 12 2a10 10 0 0 0-8.75 5.29l3.27 2.76C7.29 7.73 9.45 6.01 12 6.01Z"/></svg>
                    <span>Continue with Google</span>
                </a>

                <p class="bb-customer-login-register">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai pelanggan</a></p>
                <p class="bb-staff-login-note"><i class="bi bi-person-check" aria-hidden="true"></i> Halaman ini khusus akun pembeli.</p>
                <p class="bb-customer-login-staff-link">Admin atau staf? <a href="{{ route('admin.login') }}">Masuk ke area staff</a></p>
            </div>

        </section>
    </main>
    <script>
        document.querySelector('.bb-staff-login-form').addEventListener('submit', function () {
            const button = this.querySelector('[data-loading-button]');
            if (button) {
                button.disabled = true;
                button.querySelector('span').textContent = 'Memeriksa akun...';
            }
        });
        document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const input = document.getElementById(toggle.dataset.target);
                const icon = toggle.querySelector('i');
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                icon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
                toggle.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
            });
        });
    </script>
</body>
</html>
