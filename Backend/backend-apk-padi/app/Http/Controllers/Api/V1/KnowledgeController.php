<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Agriculture\AgricultureKnowledgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function __construct(
        private AgricultureKnowledgeService $knowledgeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $search = $request->query('search');

        $articles = $this->knowledgeService->getAllArticles($category, $search);

        return response()->json([
            'success' => true,
            'message' => 'Daftar panduan pengetahuan pertanian berhasil diambil',
            'data' => $articles,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = $this->knowledgeService->getBySlug($slug);

        if (! $article) {
            return response()->json([
                'success' => false,
                'message' => 'Panduan pengetahuan tidak ditemukan',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }
}
