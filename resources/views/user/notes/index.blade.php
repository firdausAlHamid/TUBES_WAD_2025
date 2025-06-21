@extends('layouts.app')

@section('title', 'Catatan Saya')

@push('styles')
<style>
    .note-card {
        transition: all 0.3s ease;
        opacity: 1;
        transform: scale(1);
    }
    .note-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .note-date {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .btn-delete {
        color: #dc3545;
        border: 1px solid #dc3545;
        transition: all 0.2s ease;
    }
    .btn-delete:hover {
        background-color: #dc3545;
        color: white;
    }
    .btn-edit {
        color: #0d6efd;
        border: 1px solid #0d6efd;
        transition: all 0.2s ease;
    }
    .btn-edit:hover {
        background-color: #0d6efd;
        color: white;
    }
    .note-content {
        white-space: pre-wrap;
        max-height: 70px;
        overflow-y: auto;
    }
    .alert {
        transition: all 0.3s ease;
    }
    .arabic-text {
        font-size: 1.5rem;
        line-height: 2;
        text-align: right;
        direction: rtl;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Catatan Saya</h1>
            <p class="text-muted">Daftar catatan untuk ayat-ayat Al-Quran</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($notes->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                <h3 class="mt-3">Belum Ada Catatan</h3>
                <p class="text-muted">Anda belum membuat catatan untuk ayat apapun.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="bi bi-book"></i> Mulai Membaca & Mencatat
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($notes as $note)
                @php
                    // Pisahkan surah dan ayat dari identifier
                    list($surahNumber, $ayahNumber) = explode(':', $note->api_ayat_identifier);
                @endphp
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    Catatan untuk Surah {{ $surahNumber }}, Ayat {{ $ayahNumber }}
                                </h5>
                                <div class="btn-group">
                                    <a href="{{ route('surah.show', ['number' => $surahNumber, 'highlight_ayah' => $ayahNumber]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-book"></i> Baca
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-sm edit-note" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editNoteModal"
                                            data-note-id="{{ $note->id }}"
                                            data-note-content="{{ $note->note }}"
                                            data-ayat-identifier="{{ $note->api_ayat_identifier }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-note"
                                            data-note-id="{{ $note->id }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            
                            <div class="note-content p-3 bg-light rounded">
                                {{ $note->note }}
                            </div>
                            
                            <p class="text-muted mt-2 mb-0">
                                <small>
                                    Dibuat pada {{ $note->created_at->format('d M Y H:i') }}
                                    @if($note->updated_at != $note->created_at)
                                        • Diperbarui {{ $note->updated_at->format('d M Y H:i') }}
                                    @endif
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editNoteForm">
                    <input type="hidden" id="editNoteId">
                    <input type="hidden" id="editAyatIdentifier">
                    <div class="mb-3">
                        <label for="editNoteContent" class="form-label">Catatan:</label>
                        <textarea class="form-control" id="editNoteContent" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveEditNote">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit note functionality
    const editModal = document.getElementById('editNoteModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const noteId = button.dataset.noteId;
            const noteContent = button.dataset.noteContent;
            const ayatIdentifier = button.dataset.ayatIdentifier;
            
            document.getElementById('editNoteId').value = noteId;
            document.getElementById('editNoteContent').value = noteContent;
            document.getElementById('editAyatIdentifier').value = ayatIdentifier;
        });

        document.getElementById('saveEditNote').addEventListener('click', function() {
            const noteId = document.getElementById('editNoteId').value;
            const content = document.getElementById('editNoteContent').value;
            
            if (!content.trim()) {
                alert('Catatan tidak boleh kosong');
                return;
            }

            fetch(`/notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    note: content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan catatan');
            });
        });
    }

    // Delete note functionality
    document.querySelectorAll('.delete-note').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Yakin ingin menghapus catatan ini?')) {
                const noteId = this.dataset.noteId;

                fetch(`/notes/${noteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const card = this.closest('.col-12');
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            if (document.querySelectorAll('.col-12').length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus catatan');
                });
            }
        });
    });
});
</script>
@endpush 