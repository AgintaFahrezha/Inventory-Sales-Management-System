@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Role')
@section('page_title', 'Kelola Pengguna Sistem')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Hak akses dan peran pengguna. Admin/Owner memiliki otoritas penuh, sedangkan Staff hanya dapat melakukan transaksi operasional.
        </p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
        </svg>
        <span>Tambah Pengguna</span>
    </a>
</div>

<!-- Search & Filter Bar -->
<div class="filter-bar">
    <form action="{{ route('users.index') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap; align-items: center;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari nama atau email pengguna..."
            >
        </div>

        <div>
            <select name="role" class="form-control" onchange="this.form.submit()">
                <option value="">-- Semua Role --</option>
                <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin / Owner</option>
                <option value="staff" {{ $role === 'staff' ? 'selected' : '' }}>Staff</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-outline">Cari</button>
            @if($search || $role)
                <a href="{{ route('users.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Users Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>Peran / Role</th>
                    <th>Aktivitas Transaksi</th>
                    <th>Terdaftar Sejak</th>
                    <th style="text-align: right; width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                <tr>
                    <td>{{ $users->firstItem() + $index }}</td>
                    <td style="font-weight: 700;">
                        {{ $u->name }}
                        @if($u->id === Auth::id())
                            <span class="badge badge-success" style="font-size: 0.65rem; margin-left: 0.35rem;">Anda</span>
                        @endif
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem;">
                        {{ $u->email }}
                    </td>
                    <td>
                        <span class="badge {{ $u->role === 'admin' ? 'badge-indigo' : 'badge-neutral' }}">
                            {{ strtoupper($u->role) }}
                        </span>
                    </td>
                    <td style="font-size: 0.85rem;">
                        <span title="Penjualan">{{ $u->sales_count }} Penjualan</span> &bull; 
                        <span title="Pembelian">{{ $u->purchases_count }} Pembelian</span>
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                        {{ $u->created_at->format('d/m/Y') }}
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-outline btn-sm">Edit</a>
                            @if($u->id !== Auth::id())
                            <form action="{{ route('users.destroy', $u) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-outline btn-sm">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 2.5rem;">
                        Tidak ada pengguna yang cocok dengan kriteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $users->firstItem() }} hingga {{ $users->lastItem() }} dari {{ $users->total() }} akun
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
