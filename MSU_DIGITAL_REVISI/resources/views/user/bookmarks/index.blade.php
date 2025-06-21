@extends('layouts.app')

@section('title', 'Bookmarks')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Ayat yang Ditandai</h1>
            <p class="text-muted">Daftar ayat yang Anda tandai untuk dibaca nanti</p>
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

    @if($bookmarks->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bookmark text-muted" style="font-size: 3rem;"></i>
                <h3 class="mt-3">Belum Ada Ayat yang Ditandai</h3>
                <p class="text-muted">Anda belum menandai ayat apapun. Tandai ayat untuk membacanya nanti.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="bi bi-book"></i> Mulai Membaca
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($bookmarks as $bookmark)
                @php
                    // Pisahkan surah dan ayat dari identifier
                    list($surahNumber, $ayahNumber) = explode(':', $bookmark->api_ayat_identifier);
                @endphp
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title">
                                        Surah {{ $surahNumber }}, Ayat {{ $ayahNumber }}
                                    </h5>
                                    <p class="card-text text-muted">
                                        <small>Ditandai pada {{ $bookmark->created_at->format('d M Y H:i') }}</small>
                                    </p>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('surah.show', ['number' => $surahNumber, 'highlight_ayah' => $ayahNumber]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-book"></i> Baca
                                    </a>
                                    <form action="{{ route('bookmarks.destroy', $bookmark->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                onclick="return confirm('Yakin ingin menghapus bookmark ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection 