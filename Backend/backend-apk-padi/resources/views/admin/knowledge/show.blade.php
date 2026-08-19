@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/knowledge.css') }}">

<div class="kb-page">
    {{-- Breadcrumb --}}
    <nav class="kb-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <a href="{{ route('admin.knowledge.index') }}" style="color:#64748b; text-decoration:none;">Pusat Pengetahuan</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="kb-breadcrumb-current">{{ $article->title }}</span>
    </nav>

    {{-- Header --}}
    <div class="kb-header">
        <div>
            <span class="kb-badge">{{ str_replace('_', ' ', strtoupper($article->category)) }}</span>
            <h1 class="kb-title" style="font-size:28px;">{{ $article->title }}</h1>
            <p class="kb-description" style="font-size:15px; margin-top:6px;">{{ $article->summary }}</p>
        </div>

        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('admin.knowledge.edit', $article) }}" class="kb-cat-btn active" style="padding:8px 16px;">
                Edit Panduan
            </a>

            <form method="POST" action="{{ route('admin.knowledge.destroy', $article) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel panduan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="kb-cat-btn" style="background:#fef2f2; color:#dc2626; border-color:#fca5a5; cursor:pointer;">
                    Hapus
                </button>
            </form>

            <a href="{{ route('admin.knowledge.index') }}" class="kb-cat-btn">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- Content --}}
    <article class="kb-article-content">
        {!! Str::markdown($article->content_markdown) !!}
    </article>
</div>
@endsection
