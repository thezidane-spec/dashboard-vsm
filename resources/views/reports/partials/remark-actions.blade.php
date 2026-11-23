{{-- Tombol edit & hapus untuk satu item remarks (hanya tampil di halaman edit proyek, yaitu saat $projectId diisi) --}}
@if(!empty($projectId))
    <span class="rm-actions no-print">
        <button type="button" class="rm-btn" title="Edit" data-remark-edit="{{ $type }}" data-index="{{ $index }}"><i class="bi bi-pencil-square"></i></button>
        <button type="button" class="rm-btn rm-btn-del" title="Hapus"
                data-remark-delete="{{ $type }}" data-index="{{ $index }}" data-label="{{ $label }}"
                data-action="{{ route('projects.remarks.item.destroy', [$projectId, $type, $index]) }}"><i class="bi bi-trash3"></i></button>
    </span>
@endif