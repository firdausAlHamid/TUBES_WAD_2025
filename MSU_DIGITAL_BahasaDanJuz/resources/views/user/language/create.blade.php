@extends('layouts.app')
@section('title', 'Tambah Bahasa')

@section('content')
<div class="container">
    <h3>Tambah Bahasa</h3>
    <form action="{{ route('languages.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Edition</label>
            <input type="text" name="edition" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="code" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('languages.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
