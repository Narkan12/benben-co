<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bakery BenBen Co. — Manisnya Momen, Dibuat dengan Hati')</title>
    <meta name="description" content="@yield('meta_description', 'Bakery BenBen Co. menghadirkan brownies, cake, cheesecake, cookies, bread, dan pastry premium yang dibuat fresh setiap hari.')">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- Bakery BenBen Co. design system --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    <link rel="icon" href="{{ route('media.show', ['path' => 'images/logo.png']) }}" type="image/png">

    @stack('styles')
</head>
<body>

    <x-navbar />

    <main>
        @if (session('success') || session('error'))
            <div class="container-bb pt-3">
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;">
                    <div class="toast bb-toast {{ session('error') ? 'is-error' : '' }}" role="status"
                         aria-live="polite" aria-atomic="true" data-toast>
                        <div class="toast-header">
                            <strong class="me-auto">{{ session('error') ? 'Perlu diperiksa' : 'BENBEN CO. Bakery' }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button>
                        </div>
                        <div class="toast-body">{{ session('success') ?? session('error') }}</div>
                    </div>
                </div>
            </div>
        @endif
        @if ($errors->any())
            <div class="container-bb pt-3">
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <x-footer />

    {{-- Bootstrap 5 JS bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
