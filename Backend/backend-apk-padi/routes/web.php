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

Route::get('/admin/agriculture', function () {
    return view('admin.agriculture.index', [
        'title' => 'Pertanian',
    ]);
})->name('admin.agriculture.index');

Route::get('/admin/disease', function () {
    return view('admin.disease.index', [
        'title' => 'Laporan Penyakit',
    ]);
})->name('admin.disease.index');

Route::get('/admin/early-warning', function () {
    return view('admin.early-warning.index', [
        'title' => 'Early Warning',
    ]);
})->name('admin.early-warning.index');

Route::get('/admin/marketplace', function () {
    return view('admin.marketplace.index', [
        'title' => 'Marketplace',
    ]);
})->name('admin.marketplace.index');

Route::get('/admin/broadcast', function () {
    return view('admin.broadcast.index', [
        'title' => 'Broadcast',
    ]);
})->name('admin.broadcast.index');

Route::get('/admin/audit', function () {
    return view('admin.audit.index', [
        'title' => 'Audit Log',
    ]);
})->name('admin.audit.index');
