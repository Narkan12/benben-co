@props([
    'currentPage' => 1,
    'totalPages' => 5,
    'baseUrl' => url()->current(),
])

<nav aria-label="Navigasi halaman">
    <ul class="bb-pagination">
        <li>
            <a href="{{ $currentPage > 1 ? request()->fullUrlWithQuery(['page' => $currentPage - 1]) : '#' }}" aria-label="Sebelumnya">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        @for ($i = 1; $i <= $totalPages; $i++)
            <li class="{{ $i === (int) $currentPage ? 'active' : '' }}">
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
            </li>
        @endfor

        <li>
            <a href="{{ $currentPage < $totalPages ? request()->fullUrlWithQuery(['page' => $currentPage + 1]) : '#' }}" aria-label="Berikutnya">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
