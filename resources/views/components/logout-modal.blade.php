@props(['action'])

<div class="modal fade" id="logout-confirm-modal" tabindex="-1" aria-labelledby="logout-confirm-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bb-logout-modal">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="logout-confirm-title">Konfirmasi Logout</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>
            <div class="modal-body">
                <div class="bb-logout-modal-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></div>
                <p class="mb-0">Apakah Anda yakin ingin keluar dari akun Anda?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    <button type="submit" class="btn btn-bb-logout">Ya, Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>
