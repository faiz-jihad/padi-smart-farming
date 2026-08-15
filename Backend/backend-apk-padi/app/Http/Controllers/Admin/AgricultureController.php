<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAgricultureService;
use Illuminate\View\View;

class AgricultureController extends Controller
{
    public function index(AdminAgricultureService $agriculture): View
    {
        return view('admin.agriculture.index', $agriculture->indexData());
    }
}
