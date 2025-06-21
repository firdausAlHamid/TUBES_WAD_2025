@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>{{ $customJuz->name }}</h1>
            @if($customJuz->description)
                <p class="text-muted">{{ $customJuz->description }}</p>
            @endif
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('juz.edit', $customJuz->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('juz.destroy', $customJuz->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Yakin ingin menghapus hafalan ini?')">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Juz {{ $customJuz->juz_number }}</h5>
            <span class="badge bg-primary">{{ $juzData['juzNumber'] }} Ayat</span>
        </div>
        <div class="card-body">
            @foreach($juzData['ayahs'] as $ayah)
                <div class="ayah-container mb-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="ayah-number">{{ $ayah['numberInSurah'] }}</span>
                        <div class="ayah-actions">
                            <button class="btn btn-sm btn-link bookmark-btn" 
                                    data-identifier="{{ $ayah['number'] }}" 
                                    title="Tambah Bookmark">
                                <i class="bi bi-bookmark"></i>
                            </button>
                            <button class="btn btn-sm btn-link note-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#noteModal"
                                    data-identifier="{{ $ayah['number'] }}"
                                    title="Tambah Catatan">
                                <i class="bi bi-plus-square"></i>
                            </button>
                        </div>
                    </div>
                    <div class="arabic-text mt-2">
                        {{ $ayah['text'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Catatan -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catatan Ayat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <input type="hidden" id="ayatIdentifier">
                    <input type="hidden" id="noteId">
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">Catatan Anda:</label>
                        <textarea class="form-control" id="noteContent" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="deleteNote" style="display: none;">
                    <i class="bi bi-trash"></i> Hapus
                </button>
                <button type="button" class="btn btn-primary" id="saveNote">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.ayah-container {
    border-bottom: 1px solid #eee;
    padding: 15px 0;
}
.ayah-number {
    background: #f8f9fa;
    padding: 2px 8px;
    border-radius: 4px;
}
.arabic-text {
    font-size: 1.5em;
    line-height: 2;
    text-align: right;
}
.btn-link {
    color: #6c757d;
    text-decoration: none;
    padding: 0.25rem 0.5rem;
}
.btn-link:hover {
    color: #0d6efd;
}
.btn-link.active {
    color: #198754;
}
.bookmark-btn.active i {
    color: #198754;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bookmark functionality
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const identifier = this.dataset.identifier;
            const icon = this.querySelector('i');
            
            // Toggle bookmark icon
            if (icon.classList.contains('bi-bookmark')) {
                icon.classList.remove('bi-bookmark');
                icon.classList.add('bi-bookmark-fill');
                this.classList.add('active');
            } else {
                icon.classList.remove('bi-bookmark-fill');
                icon.classList.add('bi-bookmark');
                this.classList.remove('active');
            }
        });
    });

    // Note functionality
    const noteModal = document.getElementById('noteModal');
    const noteForm = document.getElementById('noteForm');
    const deleteNoteBtn = document.getElementById('deleteNote');
    
    noteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const identifier = button.dataset.identifier;
        document.getElementById('ayatIdentifier').value = identifier;
    });
});
</script>
@endpush
@endsection 