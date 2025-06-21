@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Progres Baca Juz')

@section('content')
<div class="container mt-5 mb-5">
    <h1>Halaman Tambah Juz</h1>
    <p class="lead">
        Halaman ini digunakan untuk menambahkan progres baca Juz Al-Quran. Silakan pilih Juz yang ingin Anda baca dari daftar di bawah ini.
    </p>
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <form action="{{ route('juz.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="nama_juz" class="form-label">Nama Juz</label>
                    <input type="text" class="form-control @error('nama_juz') is-invalid @enderror" id="nama_juz" name="nama_juz" value="{{ old('nama_juz') }}" required>
                    @error('nama_juz')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="surat_awal" class="form-label">Surat Awal</label>
                    <input type="text" class="form-control @error('surat_awal') is-invalid @enderror" id="surat_awal" name="surat_awal" value="{{ old('surat_awal') }}" required>
                    @error('surat_awal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="surat_akhir" class="form-label">Surat Akhir</label>
                    <input type="text" class="form-control @error('surat_akhir') is-invalid @enderror" id="surat_akhir" name="surat_akhir" value="{{ old('surat_akhir') }}" required>
                    @error('surat_akhir')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Tambah Juz</button>
            </form>
        </div>
    </div>
</div>
@endsection
