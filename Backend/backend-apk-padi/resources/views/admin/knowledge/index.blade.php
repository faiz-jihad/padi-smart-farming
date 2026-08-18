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
        <span class="kb-breadcrumb-current">Pusat Pengetahuan Pertanian</span>
    </nav>

    {{-- Header --}}
    <div class="kb-header">
        <div>
            <h1 class="kb-title">Pusat Pengetahuan & Panduan Pertanian Padi</h1>
            <p class="kb-description">Dokumentasi teknis pemupukan berimbang, PHT (Wereng/Tikus/Blas), irigasi berselang SRI, dan varietas unggul nasional.</p>
        </div>

        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('admin.knowledge.create') }}" class="kb-cat-btn active" style="padding:10px 18px; display:inline-flex; align-items:center; gap:6px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14m-7-7h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Tambah Panduan Baru</span>
            </a>

            <form method="GET" action="{{ route('admin.knowledge.index') }}" style="display:flex; gap:8px;">
                <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari panduan (misal: Wereng, NPK)..." style="padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; outline:none; width:240px;">
                <button type="submit" class="kb-cat-btn" style="cursor:pointer;">Cari</button>
            </form>
        </div>
    </div>

    {{-- Categories --}}
    <div class="kb-categories">
        <a href="{{ route('admin.knowledge.index') }}" class="kb-cat-btn {{ empty($selectedCategory) ? 'active' : '' }}">Semua Topik</a>
        <a href="{{ route('admin.knowledge.index', ['category' => 'pemupukan']) }}" class="kb-cat-btn {{ $selectedCategory === 'pemupukan' ? 'active' : '' }}">Pemupukan Berimbang</a>
        <a href="{{ route('admin.knowledge.index', ['category' => 'hama_penyakit']) }}" class="kb-cat-btn {{ $selectedCategory === 'hama_penyakit' ? 'active' : '' }}">Hama & Penyakit (PHT)</a>
        <a href="{{ route('admin.knowledge.index', ['category' => 'irigasi_sri']) }}" class="kb-cat-btn {{ $selectedCategory === 'irigasi_sri' ? 'active' : '' }}">Irigasi Berselang (SRI)</a>
        <a href="{{ route('admin.knowledge.index', ['category' => 'sistem_tanam']) }}" class="kb-cat-btn {{ $selectedCategory === 'sistem_tanam' ? 'active' : '' }}">Jajar Legowo</a>
        <a href="{{ route('admin.knowledge.index', ['category' => 'varietas_padi']) }}" class="kb-cat-btn {{ $selectedCategory === 'varietas_padi' ? 'active' : '' }}">Varietas Unggul</a>
        <a href="{{ route('admin.knowledge.index', ['category' => 'pasca_panen']) }}" class="kb-cat-btn {{ $selectedCategory === 'pasca_panen' ? 'active' : '' }}">Pasca Panen & GKG</a>
    </div>

    {{-- Grid Articles --}}
    <div class="kb-grid">
        @forelse($articles as $art)
            <div class="kb-card">
                <div>
                    <span class="kb-badge">{{ str_replace('_', ' ', strtoupper($art->category)) }}</span>
                    <h2 class="kb-card-title">{{ $art->title }}</h2>
                    <p class="kb-card-summary">{{ $art->summary }}</p>

                    @if(!empty($art->tags))
                        <div class="kb-tag-list">
                            @foreach($art->tags as $tag)
                                <span class="kb-tag">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <a href="{{ route('admin.knowledge.show', $art->slug) }}" class="kb-read-link">
                    <span>Baca Panduan Lengkap</span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        @empty
            <div style="grid-column: 1 / -1; background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:48px; text-align:center; color:#64748b;">
                Panduan pertanian tidak ditemukan untuk kata kunci ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
