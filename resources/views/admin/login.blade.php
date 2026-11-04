<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login khusus admin dan staf BENBEN CO. Bakery.">
    <title>Login Admin &amp; Staf — BENBEN CO. Bakery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="icon" href="{{ route('media.show', ['path' => 'images/logo.png']) }}" type="image/png">
</head>
<body class="bb-staff-login">
    <main class="bb-staff-login-layout">
        <section class="bb-staff-login-story" aria-label="BENBEN CO. Bakery">
            <div class="bb-staff-login-photo" role="img" aria-label="Cheese roll buatan BENBEN CO."
                 style="background-image:linear-gradient(180deg,rgba(36,20,14,.08) 10%,rgba(36,20,14,.78) 100%),url('{{ route('media.show', ['path' => 'images/chesee-roll.png']) }}');">
                <a href="{{ route('home') }}" class="bb-staff-login-brand">
                    <span class="bb-staff-login-mark"><i class="bi bi-egg-fried" aria-hidden="true"></i></span>
                    <span>BENBEN CO.<small>BAKERY · ANTAPANI</small></span>
                </a>
                <div class="bb-staff-login-story-copy">
                    <span class="bb-staff-login-eyebrow">Ruang kerja BENBEN CO.</span>
                    <h1>Mengelola usaha,<br><em>dibuat lebih mudah.</em></h1>
                    <p>Kelola produk, pesanan, stok, dan transaksi toko dalam satu tempat.</p>
                    <span class="bb-staff-login-caption"><i class="bi bi-geo-alt" aria-hidden="true"></i> Antapani, Bandung</span>
                </div>
            </div>
        </section>

        <section class="bb-staff-login-main">
            <div class="bb-staff-login-card">
                <a href="{{ route('home') }}" class="bb-staff-login-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke website</a>

                <div class="bb-staff-login-heading">
                    <span class="bb-staff-login-eyebrow">Area khusus admin &amp; staf</span>
                    <h2>Selamat datang<br>kembali.</h2>
                    <p>Masuk untuk melanjutkan pengelolaan BENBEN CO. Bakery.</p>
                </div>

                @if ($errors->any() || session('error'))
                    <div class="bb-staff-login-alert" role="alert">
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                        <span>{{ session('error') ?? $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" class="bb-staff-login-form">
                    @csrf
                    <div class="bb-staff-login-field">
                        <label for="username">Email atau username</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <input id="username" type="text" name="username" value="{{ old('username') }}"
                                   placeholder="admin@example.com" autocomplete="username" required autofocus>
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
                        <span>Masuk ke dashboard</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="bb-staff-login-note"><i class="bi bi-shield-lock" aria-hidden="true"></i> Login ini hanya untuk admin dan staf BENBEN CO.</p>
            </div>

            <footer class="bb-staff-login-footer">&copy; {{ date('Y') }} BENBEN CO. Bakery <span>·</span> Antapani, Bandung</footer>
        </section>
    </main>
    <script>
        document.querySelector('.bb-staff-login-form').addEventListener('submit', function (event) {
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
