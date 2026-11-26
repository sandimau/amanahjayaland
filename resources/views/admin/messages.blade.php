@extends('layouts.admin')
@section('title', 'Pesan Masuk')
@section('content')
<div class="admin-title"><div><div class="eyebrow">KONTAK</div><h1>Pesan <em>masuk.</em></h1><p>{{ $messages->total() }} pesan dari pengunjung.</p></div></div>
@forelse($messages as $message)<article class="message-card"><div class="message-head"><div><strong>{{ $message->name }}</strong><span>{{ $message->created_at->format('d M Y, H:i') }}</span></div><form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Hapus pesan ini?')">@csrf @method('DELETE')<button class="link-button danger-text" type="submit">Hapus</button></form></div><p><a href="mailto:{{ $message->email }}">{{ $message->email }}</a> @if($message->phone) · <a href="tel:{{ $message->phone }}">{{ $message->phone }}</a> @endif</p>@if($message->subject)<h3>{{ $message->subject }}</h3>@endif <p class="message-text">{{ $message->message }}</p></article>@empty<div class="empty-state">Belum ada pesan masuk.</div>@endforelse
<div class="pagination">{{ $messages->links() }}</div>
@endsection
