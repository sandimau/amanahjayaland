@extends('layouts.admin')
@section('title', 'Daftar User')
@section('content')
<div class="admin-title"><div><div class="eyebrow">AREA ADMIN</div><h1>Kelola <em>user.</em></h1><p>Atur akun dan menu yang dapat diakses setiap user.</p></div><a class="button button-dark" href="{{ route('admin.users.create') }}">+ Tambah User</a></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Nama</th><th>Email</th><th>Terakhir diubah</th><th>Aksi</th></tr></thead><tbody>@foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong>@if($user->is(auth()->user())) <small>Akun Anda</small>@endif</td><td>{{ $user->email }}</td><td>{{ $user->updated_at->format('d M Y') }}</td><td><a href="{{ route('admin.users.edit', $user) }}">Edit ↗</a></td></tr>@endforeach</tbody></table></div><div class="pagination">{{ $users->links() }}</div>
@endsection
