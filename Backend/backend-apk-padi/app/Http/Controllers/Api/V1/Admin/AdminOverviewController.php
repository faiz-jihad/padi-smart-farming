<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOverviewController extends Controller
{
    public function __invoke(
        Request $request,
        AdminApiService $admin,
        ?string $resource = null,
        ?string $id = null,
    ): JsonResponse {
        return $admin->handle($request, $resource, $id);
    }
}
