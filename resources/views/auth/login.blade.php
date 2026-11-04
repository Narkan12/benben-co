<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login pelanggan BENBEN CO. Bakery.">
    <title>Login Pelanggan — BENBEN CO. Bakery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="icon" href="{{ route('media.show', ['path' => 'images/logo.png']) }}" type="image/png">
</head>
<body class="bb-staff-login bb-customer-login">
    <main class="bb-staff-login-layout">
        <section class="bb-staff-login-story" aria-label="BENBEN CO. Bakery">
            <div class="bb-staff-login-photo" role="img" aria-label="Produk BENBEN CO. Bakery"
                 style="background-image:linear-gradient(180deg,rgba(36,20,14,.08) 10%,rgba(36,20,14,.78) 100%),url('{{ route('media.show', ['path' => 'images/hero.jpg']) }}');">
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
    </script>
</body>
</html>
