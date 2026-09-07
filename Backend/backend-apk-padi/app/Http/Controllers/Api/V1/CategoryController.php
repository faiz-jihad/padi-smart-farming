<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'icon',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil diambil',
            'data' => $categories,
        ]);
    }
}
