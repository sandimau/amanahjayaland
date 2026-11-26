@extends('layouts.admin')
@section('title', 'Daftar Proyek')
@section('content')
<div class="admin-title"><div><div class="eyebrow">DASHBOARD</div><h1>Kelola <em>proyek.</em></h1><p>{{ $projects->total() }} proyek · <a href="{{ route('admin.messages.index') }}">{{ $messageCount }} pesan masuk</a></p></div><a class="button button-dark" href="{{ route('admin.projects.create') }}">+ Tambah Proyek</a></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Proyek</th><th>Lokasi</th><th>Status</th><th>Terakhir diubah</th><th>Aksi</th></tr></thead><tbody>@forelse($projects as $project)<tr><td><strong>{{ $project->name }}</strong><small>/proyek/{{ $project->slug }}</small></td><td>{{ $project->location ?: '—' }}</td><td><span class="badge {{ $project->is_published ? 'published' : '' }}">{{ $project->is_published ? 'Terbit' : 'Draf' }}</span></td><td>{{ $project->updated_at->format('d M Y') }}</td><td><a href="{{ route('admin.projects.edit', $project) }}">Edit ↗</a></td></tr>@empty<tr><td colspan="5">Belum ada proyek. Tambahkan proyek pertama Anda.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $projects->links() }}</div>
@endsection
