<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Admin') · Amanah Jaya Land</title><link rel="stylesheet" href="{{ asset('css/site.css') }}"></head>
<body class="admin-body">
<header class="admin-header"><div class="container header-inner"><a class="brand" href="{{ route('admin.index') }}"><span class="brand-symbol">AJ</span> AREA ADMIN</a><nav class="admin-nav"><a href="{{ route('admin.index') }}">Proyek</a><a href="{{ route('admin.content.index') }}">Konten Website</a><a href="{{ route('admin.settings.edit') }}">Pengaturan</a><a href="{{ route('admin.messages.index') }}">Pesan</a><a href="{{ route('home') }}" target="_blank" rel="noopener">Lihat situs ↗</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Keluar</button></form></nav></div></header>
<main class="container admin-main">@if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert error" role="alert"><strong>Periksa isian berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</main>
</body></html>
