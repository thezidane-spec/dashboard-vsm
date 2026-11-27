<div class="modal fade no-print" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ $updateRoute }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="{{ $modalId }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <label for="{{ $field }}" class="form-label-custom">Tonase (kg)</label>
                    <input type="number" id="{{ $field }}" name="{{ $field }}" class="form-control-custom @error($field) is-invalid @enderror"
                           min="0" max="999999999999.999" step="0.001" inputmode="decimal"
                           value="{{ old($field, $value) }}" required>
                    @error($field)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Masukkan nilai dalam kilogram. Maksimal 3 angka desimal.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold">Simpan</button>
                </div>
            </form>
            @if($value !== null)
                <form action="{{ $deleteRoute }}" method="POST" class="px-3 pb-3"
                      onsubmit="return confirm('Hapus data tonase ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash3 me-1"></i>Hapus Tonase
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@if($errors->has($field))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            bootstrap.Modal.getOrCreateInstance(document.getElementById(@json($modalId))).show();
        });
    </script>
@endif
