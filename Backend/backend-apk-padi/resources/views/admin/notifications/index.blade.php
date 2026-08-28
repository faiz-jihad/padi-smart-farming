@extends('layouts.admin')

@section('title', 'Notifikasi')

@section('content')
    <div style="padding: 24px;">
        <h1>Semua Notifikasi</h1>

        <p>Jumlah belum dibaca: {{ $unreadCount }}</p>

        @forelse($notifications as $notification)
            <div style="padding: 16px; border-bottom: 1px solid #ddd;">
                <strong>{{ $notification->title }}</strong>

                <p>{{ $notification->body }}</p>

                <small>
                    {{ optional($notification->created_at)->diffForHumans() }}
                </small>
            </div>
        @empty
            <p>Belum ada notifikasi.</p>
        @endforelse

        <div style="margin-top: 20px;">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection