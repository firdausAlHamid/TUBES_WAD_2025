@extends('layouts.app')

@section('title', 'Juz ' . ($juz['number'] ?? ''))

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
    .juz-header {
        background-color: #f8f9fa;
        border-left: 4px solid #28a745;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 juz-header">
            <h1 class="h3 mb-2">Juz {{ $juz['number'] ?? '' }}</h1>
            <p class="text-muted">Daftar Surah dalam Juz ini</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        @if(isset($juz['surahs']) && count($juz['surahs']) > 0)
            @foreach($juz['surahs'] as $surah)
                <div class="col-md-6 col-lg-4 mb-3">
                    <a href="{{ route('surah.show', $surah['number']) }}" class="text-decoration-none">
                        <div class="card surah-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="surah-number">{{ $surah['number'] }}</span>
                                    <div class="text-end">
                                        <div class="surah-name-arabic">{{ $surah['name_arabic'] ?? $surah['name'] }}</div>
                                        <div class="surah-name-translation">{{ $surah['translation'] ?? $surah['englishName'] ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="surah-meta d-flex justify-content-between">
                                    <span>{{ $surah['revelation_type'] ?? '' }}</span>
                                    <span>{{ $surah['ayahs_count'] ?? '' }} Ayat</span>
                                </div>
                                <div class="mt-2 small text-muted">
                                    Ayat {{ $surah['start_ayah'] ?? '' }} - {{ $surah['end_ayah'] ?? '' }}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Tidak ada surah yang ditemukan dalam Juz ini.
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any JavaScript functionality here if needed
});
</script>
@endpush
