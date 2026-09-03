<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgricultureKnowledge;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminNotificationService;
use App\Services\Agriculture\AgricultureKnowledgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function __construct(
        private AgricultureKnowledgeService $knowledgeService,
        private AdminAuditLogger $auditLogger,
        private AdminNotificationService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $category = $request->query('category');
        $search = $request->query('search');

        $articles = $this->knowledgeService->getAllArticles($category, $search);

        return view('admin.knowledge.index', [
            'articles' => $articles,
            'selectedCategory' => $category,
            'searchQuery' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.knowledge.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:500',
            'content_markdown' => 'required|string',
            'tags' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ]);

        $tagsArray = ! empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        $article = AgricultureKnowledge::create([
            'category' => strtolower($validated['category']),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::lower(Str::random(4)),
            'summary' => $validated['summary'],
            'content_markdown' => $validated['content_markdown'],
            'tags' => $tagsArray,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        // Audit Log
        $this->auditLogger->write('admin_knowledge_created', $article, null, $article->toArray(), $request);

        // System Notifications
        $this->notificationService->notifyAdmins(
            'Materi Pengetahuan Ditambahkan',
            "Panduan baru \"{$article->title}\" ({$article->category}) telah ditambahkan.",
            'knowledge',
            ['slug' => $article->slug, 'category' => $article->category]
        );

        $this->notificationService->notifyFarmers(
            'Panduan Pertanian Baru',
            "Materi baru \"{$article->title}\" telah diterbitkan di Pusat Pengetahuan.",
            'crop_alert',
            ['slug' => $article->slug, 'category' => $article->category]
        );

        $this->notificationService->notifyExtensionOfficers(
            'Materi Edukasi Baru',
            "Materi penyuluhan \"{$article->title}\" telah ditambahkan ke sistem.",
            'ppl_validation',
            ['slug' => $article->slug, 'category' => $article->category]
        );

        return redirect()->route('admin.knowledge.show', $article->slug)
            ->with('status', 'Panduan pengetahuan pertanian berhasil ditambahkan dan notifikasi telah dikirim ke perangkat.');
    }

    public function show(string $slug): View
    {
        $article = $this->knowledgeService->getBySlug($slug);

        if (! $article) {
            abort(404, 'Artikel pengetahuan pertanian tidak ditemukan.');
        }

        return view('admin.knowledge.show', [
            'article' => $article,
        ]);
    }

    public function edit(AgricultureKnowledge $article): View
    {
        return view('admin.knowledge.edit', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, AgricultureKnowledge $article): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:500',
            'content_markdown' => 'required|string',
            'tags' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ]);

        $tagsArray = ! empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        $oldValues = $article->toArray();

        $article->update([
            'category' => strtolower($validated['category']),
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'content_markdown' => $validated['content_markdown'],
            'tags' => $tagsArray,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        // Audit Log
        $this->auditLogger->write('admin_knowledge_updated', $article, $oldValues, $article->toArray(), $request);

        // System Notifications
        $this->notificationService->notifyAdmins(
            'Materi Pengetahuan Diperbarui',
            "Panduan \"{$article->title}\" ({$article->category}) telah diperbarui.",
            'knowledge',
            ['slug' => $article->slug, 'category' => $article->category]
        );

        $this->notificationService->notifyFarmers(
            'Pembaruan Panduan Pertanian',
            "Materi \"{$article->title}\" telah diperbarui dengan rekomendasi terbaru.",
            'crop_alert',
            ['slug' => $article->slug, 'category' => $article->category]
        );

        $this->notificationService->notifyExtensionOfficers(
            'Pembaruan Materi Edukasi',
            "Materi penyuluhan \"{$article->title}\" telah diperbarui.",
            'ppl_validation',
            ['slug' => $article->slug, 'category' => $article->category]
        );

        return redirect()->route('admin.knowledge.show', $article->slug)
            ->with('status', 'Panduan pengetahuan pertanian berhasil diperbarui dan notifikasi pembaruan telah dikirim.');
    }

    public function destroy(Request $request, AgricultureKnowledge $article): RedirectResponse
    {
        $oldValues = $article->toArray();
        $articleTitle = $article->title;
        $articleId = $article->id;

        $article->delete();

        // Audit Log
        $this->auditLogger->write('admin_knowledge_deleted', AgricultureKnowledge::class, $oldValues, null, $request, $articleId);

        // System Notifications
        $this->notificationService->notifyAdmins(
            'Materi Pengetahuan Dihapus',
            "Panduan \"{$articleTitle}\" telah dihapus dari sistem.",
            'knowledge'
        );

        return redirect()->route('admin.knowledge.index')
            ->with('status', 'Panduan pengetahuan pertanian berhasil dihapus dan log notifikasi dicatat.');
    }
}
