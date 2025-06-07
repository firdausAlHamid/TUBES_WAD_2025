@extends('layouts.app')
@section('title', 'Edit Bahasa')

@section('content')
<div class="container">
    <h3>Edit Bahasa</h3>
    <form action="{{ route('languages.update', $language->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $language->name }}" required>
        </div>
        <div class="mb-3">
            <label>Edition</label>
            <input type="text" name="edition" class="form-control" value="{{ $language->edition }}" required>
        </div>
        <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="code" class="form-control" value="{{ $language->code }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('languages.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
