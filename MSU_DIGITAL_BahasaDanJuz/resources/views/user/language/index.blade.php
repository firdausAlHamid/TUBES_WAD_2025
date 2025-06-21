@extends('layouts.app')
@section('title', 'Daftar Bahasa')

@section('content')
<div class="container">
    <h3>Daftar Bahasa</h3>
    <a href="{{ route('languages.create') }}" class="btn btn-success mb-3">Tambah Bahasa</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Edition</th>
                <th>Kode</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($languages as $lang)
                <tr>
                    <td>{{ $lang->name }}</td>
                    <td>{{ $lang->edition }}</td>
                    <td>{{ $lang->code }}</td>
                    <td>
                        <a href="{{ route('languages.edit', $lang->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('languages.destroy', $lang->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus bahasa ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
