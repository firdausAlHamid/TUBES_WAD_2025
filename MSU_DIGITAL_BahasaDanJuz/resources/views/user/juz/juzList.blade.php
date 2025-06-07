@extends('layouts.app')

@section('title', 'Daftar Juz Al-Quran')

@push('styles')
    <style>
        .juz-card {
            transition: transform 0.2s;
            border: 1px solid #e3e3e3;
            border-radius: 8px;
        }

        .juz-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .juz-number {
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

        .juz-title {
            font-size: 1.2rem;
            color: #333;
        }

        .juz-meta {
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
            <div class="col-12 juz-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">Daftar Juz Al-Quran</h1>
                    <p class="text-muted">Pilih Juz yang ingin dibaca</p>
                </div>
                <a href="{{ route('juz.add') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah Juz
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            @foreach ($juzList as $juz)
                <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card juz-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="juz-number">{{ $loop->iteration }}</span>
                                <div>
                                    <div class="juz-title"> {{ $juz->name }}</div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small>
                                    <i class="fas fa-book-open me-1"></i>
                                    {{ $juz->first_surah ?? 'N/A' }} {{ '-' }} {{ $juz->last_surah ?? 'N/A' }}
                                </small>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="{{ route('juz.show', $loop->iteration) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-book"></i> Read
                                </a>
                                <div>
                                    <a href="{{ route('juz.edit', $juz->id) }}" class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $juz->id }}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal moved outside the card -->
                <div class="modal fade" id="deleteModal{{ $juz->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $juz->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel{{ $juz->id }}">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete Juz "{{ $juz->name }}"?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('juz.destroy', $juz->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
