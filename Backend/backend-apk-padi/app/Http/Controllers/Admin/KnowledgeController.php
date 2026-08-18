<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgricultureKnowledge;
use App\Services\Agriculture\AgricultureKnowledgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function __construct(
        private AgricultureKnowledgeService $knowledgeService
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
            'slug' => Str::slug($validated['title']),
            'summary' => $validated['summary'],
            'content_markdown' => $validated['content_markdown'],
            'tags' => $tagsArray,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.knowledge.show', $article->slug)
            ->with('status', 'Panduan pengetahuan pertanian berhasil ditambahkan.');
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

        $article->update([
            'category' => strtolower($validated['category']),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'summary' => $validated['summary'],
            'content_markdown' => $validated['content_markdown'],
            'tags' => $tagsArray,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.knowledge.show', $article->slug)
            ->with('status', 'Panduan pengetahuan pertanian berhasil diperbarui.');
    }

    public function destroy(AgricultureKnowledge $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.knowledge.index')
            ->with('status', 'Panduan pengetahuan pertanian berhasil dihapus.');
    }
}
