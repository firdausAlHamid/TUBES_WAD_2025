@extends('layouts.app')

@section('title', 'Daftar Surat Al-Quran')

@push('styles')
<style>
    .surah-card {
        transition: transform 0.2s;
        border: 1px solid #e3e3e3;
        border-radius: 8px;
    }
    .surah-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .surah-number {
        background-color: #28a745;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }
    .surah-name-arabic {
        font-size: 1.5rem;
        color: #333;
        text-align: right;
        direction: rtl;
    }
    .surah-name-translation {
        font-size: 0.9rem;
        color: #666;
    }
    .surah-meta {
        font-size: 0.8rem;
        color: #666;
    }
    .last-read-banner {
        background-color: #e8f5e9;
        border-left: 4px solid #28a745;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-6">Al-Quran Digital</h1>
            <p class="lead">Baca, tandai, dan buat catatan untuk ayat-ayat Al-Quran</p>
        </div>
    </div>

    <!-- Search Feature Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <input type="text" id="quran-search-input" class="form-control form-control-lg" placeholder="Cari nama surah (misal: Al-Baqarah, Yusuf)...">
                </div>
            </div>
        </div>
        <!-- Search History Container -->
        <div class="col-md-12">
            <div id="search-dynamic-container">
                <!-- History will be loaded here -->
            </div>
        </div>
    </div>

    @if (isset($lastRead) && $lastRead)
    <div id="last-read-card" class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-success position-relative d-flex justify-content-between align-items-center">
                <!-- Delete Button -->
                <button id="delete-last-read" class="btn btn-link text-danger p-0" style="position: absolute; top: 0.75rem; right: 0.75rem; line-height: 1;" title="Hapus Penanda">
                    <i class="bi bi-trash-fill fs-5"></i>
                </button>
                
                <div>
                    <h5 class="alert-heading mb-0">Terakhir Dibaca</h5>
                    <p class="mb-0">{{ $lastRead['surah_name'] }} (Surah {{ $lastRead['surah_number'] }}) Ayat {{ $lastRead['ayah_number'] }}</p>
                </div>
                <a href="{{ route('surah.show', ['number' => $lastRead['surah_number'], 'highlight_ayah' => $lastRead['ayah_number']]) }}" class="btn btn-success">
                    <i class="bi bi-book-fill"></i> Lanjutkan Membaca
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        @forelse($surahs as $surah)
            <div class="col-md-4 mb-4 surah-card-wrapper" data-surah-name="{{ $surah['name'] }}" data-surah-translation="{{ $surah['englishName'] }}">
                <a href="{{ route('surah.show', $surah['number']) }}" class="text-decoration-none">
                    <div class="card surah-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="surah-number-badge">{{ $surah['number'] }}</div>
                                <div class="text-end">
                                    <h5 class="card-title surah-name-arabic">{{ $surah['name'] }}</h5>
                                    <p class="card-text surah-english-name">{{ $surah['englishName'] }}</p>
                                </div>
                            </div>
                            <hr>
                            <p class="card-text text-muted text-end">{{ $surah['revelationType'] }} • {{ $surah['numberOfAyahs'] }} Ayat</p>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    Gagal memuat daftar surah. Silakan coba lagi nanti.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('quran-search-input');
    const historyContainer = document.getElementById('search-dynamic-container');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // --- RENDER FUNCTION for History ---
    function renderHistory(history) {
        if (history.length === 0) {
            historyContainer.innerHTML = `<p class="text-center text-muted mt-3">Belum ada riwayat pencarian.</p>`;
            return;
        }
        const historyList = history.map(item => `
            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center history-item" data-term="${item.term}">
                <span><i class="bi bi-clock-history me-2"></i>${item.term}</span>
                <button class="btn btn-sm btn-outline-danger delete-history-btn" data-id="${item.id}" title="Hapus">&times;</button>
            </a>
        `).join('');
        historyContainer.innerHTML = `
            <div class="card mt-3">
                <div class="card-header">Riwayat Pencarian</div>
                <div class="list-group list-group-flush">${historyList}</div>
            </div>
        `;
    }

    // --- API FUNCTIONS for History ---
    async function fetchHistory() {
        try {
            const response = await fetch('{{ route("search.history") }}');
            const history = await response.json();
            renderHistory(history);
        } catch (error) {
            console.error('Failed to fetch history:', error);
        }
    }

    async function saveSearchTerm(term) {
        try {
            await fetch('{{ route("search.history.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ term })
            });
        } catch (error) {
            console.error('Failed to save search term:', error);
        }
    }

    async function deleteHistoryItem(id) {
        try {
            await fetch(`/search/history/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
        } catch (error) {
            console.error('Failed to delete history item:', error);
        }
    }

    // --- REAL-TIME SURAH FILTER LOGIC ---
    function filterSurahs() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const surahCards = document.querySelectorAll('.surah-card-wrapper');
        let visibleCount = 0;

        surahCards.forEach(card => {
            const surahName = card.dataset.surahName.toLowerCase();
            const surahTranslation = card.dataset.surahTranslation.toLowerCase();
            
            if (surahName.includes(searchTerm) || surahTranslation.includes(searchTerm)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        // You can add a "not found" message here if visibleCount is 0
    }

    // --- EVENT LISTENERS ---
    searchInput.addEventListener('input', filterSurahs);

    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const term = searchInput.value.trim();
            if (term) {
                saveSearchTerm(term).then(fetchHistory); // Save and refresh history
            }
        }
    });

    historyContainer.addEventListener('click', (e) => {
        // Handle clicking a history item to search
        const historyItem = e.target.closest('.history-item');
        if (historyItem) {
            e.preventDefault();
            const term = historyItem.dataset.term;
            searchInput.value = term;
            filterSurahs();
        }

        // Handle deleting a history item
        const deleteBtn = e.target.closest('.delete-history-btn');
        if (deleteBtn) {
            e.preventDefault();
            e.stopPropagation();
            const id = deleteBtn.dataset.id;
            deleteBtn.closest('.list-group-item').remove();
            deleteHistoryItem(id);
        }
    });

    // Initial load
    fetchHistory();

    // --- DELETE LAST READ LOGIC ---
    const deleteButton = document.getElementById('delete-last-read');
    if (deleteButton) {
        deleteButton.addEventListener('click', function () {
            if (confirm('Apakah Anda yakin ingin menghapus penanda terakhir dibaca?')) {
                fetch('{{ route("last-read.delete") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken, // Reuse existing csrfToken variable
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const lastReadCard = document.getElementById('last-read-card');
                        if (lastReadCard) {
                            lastReadCard.style.transition = 'opacity 0.5s ease';
                            lastReadCard.style.opacity = '0';
                            setTimeout(() => lastReadCard.remove(), 500);
                        }
                        // You can use a more elegant notification system if you have one
                        // alert(data.message);
                    } else {
                        alert('Gagal menghapus: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghubungi server.');
                });
            }
        });
    }
});
</script>
@endpush 