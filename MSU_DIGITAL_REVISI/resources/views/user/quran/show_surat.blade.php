@extends('layouts.app') {{-- Atau layout user Anda --}}

@section('title', $surahDetails['englishName'] ?? 'Surah Detail')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
    .ayah-text {
        font-size: 1.8rem; /* Ukuran font Arab */
        line-height: 2.5;
        text-align: right;
        margin-bottom: 0.5rem;
        direction: rtl;
    }
    .translation-text {
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        color: #555;
        text-align: left;
        direction: ltr;
    }
    .ayah-container {
        border-bottom: 1px solid #eee;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    .ayah-number-badge {
        float: left; 
        margin-right: 10px; 
        font-size: 0.9rem;
        background-color: #28a745; /* Warna hijau seperti di gambar */
        color: white;
        padding: 5px 8px;
        border-radius: 50%;
        min-width: 28px; /* Agar buletannya konsisten */
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    .ayah-actions {
        float: right;
        font-size: 1.2rem;
    }
    .ayah-actions .btn-bookmark, .ayah-actions .btn-add-note {
        color: #6c757d;
        padding: 0.2rem 0.4rem;
        text-decoration: none;
        border: none;
        background: none;
    }
    .ayah-actions .btn-bookmark.bookmarked,
    .ayah-actions .btn-bookmark:hover,
    .ayah-actions .btn-add-note:hover {
        color: #28a745;
    }
    .sticky-surah-header {
        position: sticky;
        top: 60px; /* Sesuaikan dengan tinggi navbar Anda */
        background-color: white;
        z-index: 1000;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }
    .surah-title-container {
        background-color: #28a745; /* Warna hijau header */
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .btn-audio {
        color: #6c757d;
        padding: 0.2rem 0.4rem;
        text-decoration: none;
        border: none;
        background: none;
        cursor: pointer;
    }
    
    .btn-audio:hover {
        color: #28a745;
    }
    
    .btn-audio.playing {
        color: #28a745;
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    .audio-controls {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 10px;
    }

    .audio-progress {
        flex-grow: 1;
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        position: relative;
        cursor: pointer;
    }

    .audio-progress-bar {
        height: 100%;
        background: #28a745;
        border-radius: 2px;
        width: 0%;
    }

    .audio-time {
        font-size: 0.8rem;
        color: #6c757d;
        min-width: 45px;
    }

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
    .note-preview {
        border-left: 3px solid #28a745;
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
    .note-btn i {
        font-size: 1.1rem;
    }
    .bi-bookmark-fill {
        color: #198754;
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
    }

    .toast {
        background-color: rgba(255, 255, 255, 0.95);
        border-left: 4px solid;
    }

    .toast.success {
        border-left-color: #198754;
    }

    .toast.error {
        border-left-color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="toast-container position-fixed top-0 end-0 p-3" id="dynamic-toast-container"></div>

<div class="container mt-4">
    @if(isset($surahDetails) && $surahDetails)
        <div class="surah-title-container text-center sticky-surah-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    {{-- Tombol Navigasi Surah Sebelumnya (jika bukan surah pertama) --}}
                    @if($surahDetails['number'] > 1)
                        <a href="{{ route('surah.show', $surahDetails['number'] - 1) }}" class="btn btn-light btn-sm">&laquo; Sebelumnya</a>
                    @endif
                </div>
                <h2 class="h4 mb-0">{{ $surahDetails['name'] }} <small>({{ $surahDetails['englishName'] }})</small></h2>
                <div>
                    {{-- Tombol Navigasi Surah Berikutnya (jika bukan surah terakhir) --}}
                    @if($surahDetails['number'] < 114)
                        <a href="{{ route('surah.show', $surahDetails['number'] + 1) }}" class="btn btn-light btn-sm">Berikutnya &raquo;</a>
                    @endif
                </div>
            </div>
            <div class="mt-2">
                <small>{{ $surahDetails['revelationType'] }} - {{ $surahDetails['numberOfAyahs'] }} ayat</small>
            </div>
            <div class="mt-2">
                <button id="toggleArabic" class="btn btn-outline-light btn-sm">Toggle Teks Arab</button>
                <button id="toggleTranslation" class="btn btn-outline-light btn-sm">Toggle Terjemahan</button>
            </div>
        </div>

        <div class="mt-3">
            @if($surahDetails['number'] != 1 && $surahDetails['number'] != 9) {{-- Kecuali Al-Fatihah & At-Taubah --}}
                <div class="ayah-container bismillah-container">
                    <p class="ayah-text arabic-text">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
                    <p class="translation-text translation-text-content">Dengan nama Allah Yang Maha Pengasih, Maha Penyayang.</p>
                </div>
            @endif

            @foreach ($surahDetails['ayahs'] as $ayah)
                <div class="ayah-container" id="ayah-{{ $ayah['numberInSurah'] }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="ayah-number-badge">{{ $ayah['numberInSurah'] }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="ayah-text arabic-text">{{ $ayah['text'] }}</p>
                            <p class="translation-text translation-text-content">{{ $ayah['translation_text'] }}</p>
                            <div class="audio-controls" id="audio-controls-{{ $ayah['numberInSurah'] }}">
                                <button class="btn-audio" 
                                        data-ayah-number="{{ $ayah['numberInSurah'] }}"
                                        data-audio-url="{{ $ayah['audioUrl'] ?? '' }}"
                                        title="Play Audio">
                                    <i class="bi bi-play-circle"></i>
                                </button>
                                <div class="audio-progress">
                                    <div class="audio-progress-bar"></div>
                                </div>
                                <span class="audio-time">00:00</span>
                            </div>
                        </div>
                        <div class="ayah-actions ms-2">
                            <button class="btn-bookmark {{ $ayah['is_bookmarked'] ? 'bookmarked' : '' }}" 
                                    data-identifier="{{ $ayah['api_ayat_identifier'] }}" 
                                    title="{{ $ayah['is_bookmarked'] ? 'Hapus Bookmark' : 'Tambah Bookmark' }}">
                                <i class="bi {{ $ayah['is_bookmarked'] ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                            </button>
                            <button class="btn-add-note" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#noteModal"
                                    data-ayat-identifier="{{ $ayah['api_ayat_identifier'] }}"
                                    data-ayat-text="{{ Str::limit($ayah['text'], 100) }}"
                                    data-note-content="{{ $ayah['personal_note'] ? $ayah['personal_note']->note : '' }}"
                                    data-note-id="{{ $ayah['personal_note'] ? $ayah['personal_note']->id : '' }}"
                                    title="{{ $ayah['personal_note'] ? 'Edit Catatan' : 'Tambah Catatan' }}">
                                <i class="bi {{ $ayah['personal_note'] ? 'bi-pencil-square' : 'bi-plus-square' }}"></i>
                            </button>
                        </div>
                    </div>
                    @if($ayah['personal_note'])
                        <div class="note-preview mt-2 p-2 bg-light rounded">
                            <small class="text-muted">Catatan:</small>
                            <p class="mb-0">{{ $ayah['personal_note']->note }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    @else
        <div class="alert alert-danger">Gagal memuat detail surat atau surat tidak ditemukan.</div>
        <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Daftar Surat</a>
    @endif
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Arabic Text
    const toggleArabicBtn = document.getElementById('toggleArabic');
    if (toggleArabicBtn) {
        toggleArabicBtn.addEventListener('click', function() {
            document.querySelectorAll('.arabic-text').forEach(el => {
                el.style.display = el.style.display === 'none' ? '' : 'none';
            });
        });
    }

    // Toggle Translation Text
    const toggleTranslationBtn = document.getElementById('toggleTranslation');
    if (toggleTranslationBtn) {
        toggleTranslationBtn.addEventListener('click', function() {
            document.querySelectorAll('.translation-text-content').forEach(el => {
                el.style.display = el.style.display === 'none' ? '' : 'none';
            });
        });
    }

    // Toast notification function
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('dynamic-toast-container');
        const toastId = 'toast-' + Date.now();
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center ${type}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            delay: 3000
        });
        toast.show();
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    // Handle bookmark toggle
    document.querySelectorAll('.btn-bookmark').forEach(button => {
        button.addEventListener('click', function() {
            const identifier = this.dataset.identifier;
            const isBookmarked = this.classList.contains('bookmarked');
            const action = isBookmarked ? 'remove' : 'add';

            fetch('{{ route('bookmarks.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ identifier: identifier, action: action })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('bookmarked');
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-bookmark');
                    icon.classList.toggle('bi-bookmark-fill');
                    
                    if (action === 'add') {
                        this.title = 'Hapus Bookmark';
                        showToast('Berhasil ditambahkan ke bookmark', 'success');
                    } else {
                        this.title = 'Tambah Bookmark';
                        showToast('Bookmark dihapus', 'success');
                    }
                } else {
                    showToast(data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Tidak dapat menjangkau server', 'error');
            });
        });
    });

    // Handle note functionality
    const noteModal = document.getElementById('noteModal');
    const noteForm = document.getElementById('noteForm');
    const saveNoteBtn = document.getElementById('saveNote');
    const deleteNoteBtn = document.getElementById('deleteNote');
    
    noteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const identifier = button.dataset.ayatIdentifier;
        const noteContent = button.dataset.noteContent;
        const noteId = button.dataset.noteId;
        
        document.getElementById('ayatIdentifier').value = identifier;
        document.getElementById('noteContent').value = noteContent;
        document.getElementById('noteId').value = noteId;
        
        deleteNoteBtn.style.display = noteId ? 'block' : 'none';
    });

    // Save note
    saveNoteBtn.addEventListener('click', function() {
        const identifier = document.getElementById('ayatIdentifier').value;
        const noteContent = document.getElementById('noteContent').value;
        const noteId = document.getElementById('noteId').value;

        if (!noteContent.trim()) {
            showToast('Catatan tidak boleh kosong', 'error');
            return;
        }

        fetch('{{ route('notes.save') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                identifier: identifier,
                content: noteContent,
                note_id: noteId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Catatan berhasil disimpan.', 'success');
                const bsModal = bootstrap.Modal.getInstance(noteModal);
                bsModal.hide();

                const ayahContainer = document.querySelector(`.btn-add-note[data-ayat-identifier="${identifier}"]`).closest('.ayah-container');
                if (!ayahContainer) return;

                let notePreview = ayahContainer.querySelector('.note-preview');
                if (noteContent.trim()) {
                    if (!notePreview) {
                        const newPreview = document.createElement('div');
                        newPreview.className = 'note-preview mt-2 p-2 bg-light rounded';
                        newPreview.innerHTML = `<small class="text-muted">Catatan:</small><p class="mb-0"></p>`;
                        ayahContainer.querySelector('.d-flex.justify-content-between').insertAdjacentElement('afterend', newPreview);
                        notePreview = newPreview;
                    }
                    notePreview.querySelector('p').textContent = noteContent;
                    notePreview.style.display = 'block';
                } else {
                    if (notePreview) {
                        notePreview.remove();
                    }
                }

                const noteBtn = ayahContainer.querySelector('.btn-add-note');
                noteBtn.dataset.noteContent = noteContent;
                noteBtn.dataset.noteId = data.note_id;
                const noteBtnIcon = noteBtn.querySelector('i');
                if (noteContent.trim()) {
                    noteBtn.title = 'Edit Catatan';
                    noteBtnIcon.className = 'bi bi-pencil-square';
                } else {
                    noteBtn.title = 'Tambah Catatan';
                    noteBtnIcon.className = 'bi bi-plus-square';
                }
            } else {
                showToast(data.message || 'Gagal menyimpan catatan.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menyimpan catatan.', 'error');
        });
    });

    // Custom toast function with a link
    function showCustomToast(message, linkUrl) {
        const toastContainer = document.getElementById('dynamic-toast-container');
        if (!toastContainer) return;

        const toastHTML = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message} 
                        <a href="${linkUrl}" class="btn btn-sm btn-light ms-2">Lihat Catatan</a>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        toastContainer.innerHTML = toastHTML;
        const newToast = toastContainer.querySelector('.toast');
        const bsToast = new bootstrap.Toast(newToast);
        bsToast.show();
    }

    // Delete note
    if (deleteNoteBtn) {
        deleteNoteBtn.addEventListener('click', function() {
            if (!confirm('Yakin ingin menghapus catatan ini?')) return;

            const noteId = document.getElementById('noteId').value;
            const identifier = document.getElementById('ayatIdentifier').value;

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
                    const bsModal = bootstrap.Modal.getInstance(noteModal);
                    bsModal.hide();
                    showToast('Catatan berhasil dihapus', 'success');

                    // Update UI
                    const ayahContainer = document.querySelector(`[data-ayat-identifier="${identifier}"]`)
                        .closest('.ayah-container');
                    const notePreview = ayahContainer.querySelector('.note-preview');
                    if (notePreview) {
                        notePreview.remove();
                    }

                    // Reset button
                    const noteBtn = ayahContainer.querySelector('.btn-add-note');
                    noteBtn.innerHTML = '<i class="bi bi-plus-square"></i>';
                    noteBtn.dataset.noteContent = '';
                    noteBtn.dataset.noteId = '';
                    noteBtn.title = 'Tambah Catatan';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menghapus catatan', 'error');
            });
        });
    }

    // Audio Player Functionality
    let currentAudio = null;
    let currentButton = null;

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function resetPreviousAudio() {
        if (currentAudio) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
            if (currentButton) {
                const icon = currentButton.querySelector('i');
                icon.classList.remove('bi-pause-circle');
                icon.classList.add('bi-play-circle');
                currentButton.classList.remove('playing');
                
                // Reset progress bar
                const controls = currentButton.closest('.audio-controls');
                const progressBar = controls.querySelector('.audio-progress-bar');
                const timeDisplay = controls.querySelector('.audio-time');
                progressBar.style.width = '0%';
                timeDisplay.textContent = '00:00';
            }
        }
    }

    document.querySelectorAll('.btn-audio').forEach(button => {
        button.addEventListener('click', function() {
            const audioUrl = this.dataset.audioUrl;
            const icon = this.querySelector('i');
            const controls = this.closest('.audio-controls');
            const progressBar = controls.querySelector('.audio-progress-bar');
            const timeDisplay = controls.querySelector('.audio-time');
            const progressContainer = controls.querySelector('.audio-progress');

            if (currentAudio && currentButton === this) {
                // Pause current audio
                if (currentAudio.paused) {
                    currentAudio.play();
                    icon.classList.remove('bi-play-circle');
                    icon.classList.add('bi-pause-circle');
                    this.classList.add('playing');
                } else {
                    currentAudio.pause();
                    icon.classList.remove('bi-pause-circle');
                    icon.classList.add('bi-play-circle');
                    this.classList.remove('playing');
                }
                return;
            }

            // Reset previous audio if exists
            resetPreviousAudio();

            // Create new audio
            currentAudio = new Audio(audioUrl);
            currentButton = this;

            currentAudio.addEventListener('loadedmetadata', () => {
                timeDisplay.textContent = formatTime(currentAudio.duration);
            });

            currentAudio.addEventListener('timeupdate', () => {
                const progress = (currentAudio.currentTime / currentAudio.duration) * 100;
                progressBar.style.width = `${progress}%`;
                timeDisplay.textContent = formatTime(currentAudio.currentTime);
            });

            currentAudio.addEventListener('ended', () => {
                icon.classList.remove('bi-pause-circle');
                icon.classList.add('bi-play-circle');
                this.classList.remove('playing');
                progressBar.style.width = '0%';
                timeDisplay.textContent = '00:00';
                currentAudio = null;
                currentButton = null;
            });

            // Add click event for progress bar
            progressContainer.addEventListener('click', function(e) {
                if (currentAudio) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const width = rect.width;
                    const percentage = x / width;
                    currentAudio.currentTime = currentAudio.duration * percentage;
                }
            });

            // Play new audio
            currentAudio.play();
            icon.classList.remove('bi-play-circle');
            icon.classList.add('bi-pause-circle');
            this.classList.add('playing');
        });
    });
});
</script>
@endpush 