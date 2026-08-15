<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.dashboard', [
        'title' => 'Dashboard',
    ]);
})->name('admin.dashboard');

Route::get('/admin/users', function () {
    return view('admin.users.index', [
        'title' => 'Pengguna',
    ]);
})->name('admin.users.index');
