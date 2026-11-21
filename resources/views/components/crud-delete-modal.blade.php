<div class="modal fade" id="crud-delete-confirm-modal" tabindex="-1" aria-labelledby="crud-delete-confirm-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bb-crud-modal">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="crud-delete-confirm-title">Konfirmasi Hapus</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>
            <div class="modal-body bb-crud-confirm-body">
                <div class="bb-crud-modal-icon" aria-hidden="true"><i class="bi bi-trash3"></i></div>
                <p class="mb-0">Yakin ingin menghapus <strong data-crud-delete-name>data ini</strong>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="#" data-crud-delete-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-bb-delete" disabled>Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
