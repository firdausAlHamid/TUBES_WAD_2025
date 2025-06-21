@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Hafalan Juz Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('juz.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Hafalan</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" 
                                placeholder="Contoh: Hafalan Juz 1 Minggu Pertama" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3" 
                                placeholder="Tambahkan catatan atau target hafalan">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="juz_number" class="form-label">Nomor Juz</label>
                            <select class="form-select @error('juz_number') is-invalid @enderror" 
                                id="juz_number" name="juz_number" required>
                                <option value="">Pilih Juz</option>
                                @for($i = 1; $i <= 30; $i++)
                                    <option value="{{ $i }}" {{ old('juz_number') == $i ? 'selected' : '' }}>
                                        Juz {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            @error('juz_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="edition" class="form-label">Edisi Al-Quran</label>
                            <select class="form-select @error('edition') is-invalid @enderror" 
                                id="edition" name="edition" required>
                                <option value="quran-simple-enhanced" {{ old('edition') == 'quran-simple-enhanced' ? 'selected' : '' }}>
                                    Quran Simple
                                </option>
                                <option value="ar.alafasy" {{ old('edition') == 'ar.alafasy' ? 'selected' : '' }}>
                                    Mishary Rashid Al-Afasy
                                </option>
                            </select>
                            @error('edition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Tambah Hafalan
                            </button>
                            <a href="{{ route('juz.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 