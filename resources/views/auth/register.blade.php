<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Registrasi pelanggan BENBEN CO. Bakery.">
    <title>Daftar Pelanggan — BENBEN CO. Bakery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="icon" href="{{ route('media.show', ['path' => 'images/logo.png']) }}" type="image/png">
</head>
<body class="bb-staff-login bb-customer-login bb-customer-register-page">
    <main class="bb-staff-login-layout">
        <section class="bb-staff-login-story" aria-label="BENBEN CO. Bakery">
            <div class="bb-staff-login-photo" role="img" aria-label="Produk BENBEN CO. Bakery"
                 style="background-image:linear-gradient(180deg,rgba(36,20,14,.08) 10%,rgba(36,20,14,.78) 100%),url('{{ route('media.show', ['path' => 'images/chesee-roll.png']) }}');">
                <a href="{{ route('home') }}" class="bb-staff-login-brand">
                    <span class="bb-staff-login-mark"><i class="bi bi-egg-fried" aria-hidden="true"></i></span>
                    <span>BENBEN CO.<small>BAKERY · ANTAPANI</small></span>
                </a>
                <div class="bb-staff-login-story-copy">
                    <span class="bb-staff-login-eyebrow">Member BENBEN CO.</span>
                    <h1>Gabung untuk<br><em>momen yang lebih manis.</em></h1>
                    <p>Buat akun untuk memantau pesanan dan menikmati pengalaman belanja yang lebih praktis.</p>
                    <span class="bb-staff-login-caption"><i class="bi bi-gift" aria-hidden="true"></i> Nikmati kemudahan sebagai member</span>
                </div>
            </div>
        </section>

        <section class="bb-staff-login-main">
            <div class="bb-staff-login-card">
                <a href="{{ route('home') }}" class="bb-staff-login-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke website</a>

                <div class="bb-staff-login-heading">
                    <span class="bb-staff-login-eyebrow">Buat akun pelanggan</span>
                    <h2>Daftar<br>sekarang.</h2>
                    <p>Simpan informasi Anda dan pantau pesanan dalam satu akun.</p>
                </div>

                @if ($errors->any())
                    <div class="bb-staff-login-alert" role="alert">
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" class="bb-staff-login-form bb-customer-register-form">
                    @csrf
                    <div class="bb-staff-login-field">
                        <label for="nama">Nama lengkap</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <input id="nama" type="text" name="nama" value="{{ old('nama') }}" placeholder="Nama Anda" required maxlength="100" autocomplete="name">
                        </div>
                    </div>

                    <div class="bb-staff-login-field">
                        <label for="username">Username</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-at" aria-hidden="true"></i>
                            <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Buat username" required maxlength="50" autocomplete="username">
                        </div>
                    </div>

                    <div class="bb-staff-login-field">
                        <label for="email">Email</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required maxlength="150" autocomplete="email">
                        </div>
                    </div>

                    <div class="bb-staff-login-field">
                        <label for="no_whatsapp">Nomor WhatsApp</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>
                            <input id="no_whatsapp" type="tel" name="no_whatsapp" value="{{ old('no_whatsapp') }}" placeholder="08xx-xxxx-xxxx" required maxlength="20" autocomplete="tel">
                        </div>
                    </div>

                    <div class="bb-staff-login-field">
                        <label for="password">Password</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input id="password" type="password" name="password" placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password">
                            <button type="button" class="bb-password-toggle" data-password-toggle data-target="password" aria-label="Tampilkan password">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="bb-staff-login-field">
                        <label for="password_confirmation">Konfirmasi password</label>
                        <div class="bb-staff-login-input-wrap">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password" required minlength="8" autocomplete="new-password">
                            <button type="button" class="bb-password-toggle" data-password-toggle data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="bb-staff-login-submit" data-loading-button>
                        <span>Buat akun pelanggan</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="bb-customer-login-register">Sudah punya akun? <a href="{{ route('login') }}">Masuk sebagai pelanggan</a></p>
                <p class="bb-staff-login-note"><i class="bi bi-shield-check" aria-hidden="true"></i> Data akun digunakan untuk menghubungkan riwayat pesanan Anda.</p>
            </div>

        </section>
    </main>
    <script>
        document.querySelector('.bb-customer-register-form').addEventListener('submit', function () {
            const button = this.querySelector('[data-loading-button]');
            if (button) {
                button.disabled = true;
                button.querySelector('span').textContent = 'Membuat akun...';
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
