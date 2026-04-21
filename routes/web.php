<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Role-based Auth Utilities
Route::get('/login', function() {
    return redirect('/');
})->name('login');

Route::get('/login-hq', function() {
    // `admin@arrahnu.com` is seeded with `super-admin` role via DatabaseSeeder.
    $user = \App\Models\User::where('email', 'admin@arrahnu.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/studio');
    }
    return 'HQ Admin user not found. Please run `php artisan db:seed` first.';
});

Route::get('/login-admin', function() {
    $user = \App\Models\User::where('email', 'admin@arrahnu.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/studio');
    }
    return 'Admin user not found. Please seed database.';
});

Route::get('/login-manager', function() {
    $user = \App\Models\User::where('email', 'manager@arrahnu.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/f/new-pledge'); // or manager dashboard
    }
    return 'Manager user not found. Please seed database.';
});

Route::get('/login-teller', function() {
    $user = \App\Models\User::where('email', 'staff@arrahnu.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/f/new-pledge');
    }
    return "Teller user not found. Please run seeders first.";
});

// V3 Dynamic Feature Runtime
Route::get('/f/{featureKey}/{pageKey?}', \App\Livewire\Runtime\FormEngine::class)
    ->middleware(['web', 'auth', 'permission:runtime.execute'])
    ->name('v3.runtime');

// V3 Studio (HQ Admin)
Route::prefix('studio')->middleware(['web', 'auth', 'role:super-admin,system-admin,feature-developer'])->group(function () {
    Route::get('/', \App\Livewire\Studio\Dashboard::class)->name('studio.dashboard');
    Route::get('/monitor', \App\Livewire\Studio\RuntimeMonitor::class)
        ->middleware('permission:monitor.view')
        ->name('studio.monitor');

    // Engine Builders
    Route::get('/flow-canvas/{flowId}', \App\Livewire\Studio\FlowCanvasProxy::class)
        ->middleware('permission:flows.edit')
        ->name('studio.flow-canvas');
    Route::get('/page-builder/{featureVersionId}/{pageId}', \App\Livewire\Studio\PageBuilderProxy::class)
        ->middleware('permission:pages.edit')
        ->name('studio.page-builder');

    // Publish Workflow / Release Center
    Route::get('/releases', function() {
        return view('studio.publish.release-center');
    })->middleware('permission:versions.view')->name('studio.releases');

    Route::get('/releases/{versionId}/review', function($versionId) {
        return view('studio.publish.review', ['versionId' => $versionId]);
    })->middleware('permission:versions.review')->name('studio.releases.review');
    
    // Support & Error Reporting
    Route::get('/support/report-issue', [\App\Http\Controllers\SupportController::class, 'reportIssue'])->name('studio.support.report-issue');
    Route::post('/support/submit-report', [\App\Http\Controllers\SupportController::class, 'submitReport'])->name('studio.support.submit-report');
});
