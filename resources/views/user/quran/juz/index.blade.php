@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Hafalan Juz</h1>
            <p class="text-muted">Kelola hafalan Al-Quran Anda berdasarkan juz</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('juz.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Juz Baru
            </a>
        </div>
    </div>

    <!-- Daftar Juz Default -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Daftar Juz Al-Quran</h5>
        </div>
        <div class="card-body">
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
                @for ($i = 1; $i <= 30; $i++)
                    <div class="col">
                        <a href="{{ route('quran.juz', $i) }}" class="text-decoration-none">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-0">Juz {{ $i }}</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Daftar Juz Kustom -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Hafalan Saya</h5>
        </div>
        <div class="card-body">
            @if($customJuzs->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-journal-text display-4 text-muted"></i>
                    <p class="mt-3">Belum ada hafalan yang ditambahkan</p>
                    <a href="{{ route('juz.create') }}" class="btn btn-primary">
                        Mulai Menambahkan Hafalan
                    </a>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($customJuzs as $juz)
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $juz->name }}</h5>
                                    <p class="card-text text-muted">Juz {{ $juz->juz_number }}</p>
                                    @if($juz->description)
                                        <p class="card-text">{{ $juz->description }}</p>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group w-100">
                                        <a href="{{ route('juz.show', $juz->id) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-book"></i> Baca
                                        </a>
                                        <a href="{{ route('juz.edit', $juz->id) }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('juz.destroy', $juz->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Yakin ingin menghapus hafalan ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 