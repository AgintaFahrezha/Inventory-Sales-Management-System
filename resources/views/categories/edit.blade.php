@extends('layouts.app')

@section('title', 'Edit Kategori')
@section('page_title', 'Ubah Data Kategori')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Kategori: {{ $category->name }}</h3>
            <a href="{{ route('categories.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name" class="form-label">Nama Kategori <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name', $category->name) }}" 
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Deskripsi (Opsional)</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3" 
                        class="form-control @error('description') is-invalid @enderror"
                    >{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('categories.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Perbarui Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
