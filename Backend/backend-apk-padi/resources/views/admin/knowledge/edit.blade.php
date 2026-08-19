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
        <span class="kb-breadcrumb-current">Edit Panduan</span>
    </nav>

    {{-- Header --}}
    <div class="kb-header">
        <div>
            <h1 class="kb-title">Edit Panduan Pertanian</h1>
            <p class="kb-description">Perbarui materi atau informasi teknis artikel pengetahuan.</p>
        </div>

        <a href="{{ route('admin.knowledge.show', $article->slug) }}" class="kb-cat-btn">Batal / Kembali</a>
    </div>

    {{-- Form --}}
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">
        <form method="POST" action="{{ route('admin.knowledge.update', $article) }}">
            @csrf
            @method('PATCH')

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="category">Kategori Topik <span style="color:#dc2626;">*</span></label>
                    <select name="category" id="category" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff;" required>
                        <option value="pemupukan" @selected($article->category === 'pemupukan')>Pemupukan Berimbang</option>
                        <option value="hama_penyakit" @selected($article->category === 'hama_penyakit')>Hama & Penyakit (PHT)</option>
                        <option value="irigasi_sri" @selected($article->category === 'irigasi_sri')>Irigasi Berselang (SRI)</option>
                        <option value="sistem_tanam" @selected($article->category === 'sistem_tanam')>Sistem Tanam (Jajar Legowo)</option>
                        <option value="varietas_padi" @selected($article->category === 'varietas_padi')>Varietas Unggul</option>
                        <option value="pasca_panen" @selected($article->category === 'pasca_panen')>Pasca Panen & GKG</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="title">Judul Panduan <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="title" id="title" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" value="{{ old('title', $article->title) }}" required>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="summary">Ringkasan Eksekutif <span style="color:#dc2626;">*</span></label>
                <textarea name="summary" id="summary" rows="2" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" required>{{ old('summary', $article->summary) }}</textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="content_markdown">Isi Materi Lengkap (Format Markdown / Text) <span style="color:#dc2626;">*</span></label>
                <textarea name="content_markdown" id="content_markdown" rows="12" style="width:100%; padding:14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; font-family:monospace; box-sizing:border-box;" required>{{ old('content_markdown', $article->content_markdown) }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:28px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="tags">Tag Kata Kunci (Dipisahkan koma)</label>
                    <input type="text" name="tags" id="tags" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" value="{{ old('tags', is_array($article->tags) ? implode(', ', $article->tags) : '') }}">
                </div>

                <div style="display:flex; align-items:center; gap:8px; margin-top:24px;">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" style="width:18px; height:18px;" @checked(old('is_featured', $article->is_featured))>
                    <label for="is_featured" style="font-size:14px; font-weight:600; color:#0f172a;">Jadikan Artikel Utama (Featured)</label>
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit" class="kb-cat-btn active" style="padding:12px 24px; border:none; cursor:pointer;">Simpan Perubahan</button>
                <a href="{{ route('admin.knowledge.show', $article->slug) }}" class="kb-cat-btn">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
