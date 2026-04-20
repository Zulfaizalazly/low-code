<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Pilot Auth Utilities
Route::get('/login', function() {
    return "Please use <a href='/login-pilot'>/login-pilot</a> for the zero-config test.";
})->name('login');

Route::get('/login-pilot', function() {
    $user = \App\Models\User::where('email', 'staff@arrahnu.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/f/new-pledge');
    }
    return "Pilot user not found. Please run php artisan v3:produce-pilot first.";
});

// V3 Dynamic Feature Runtime
Route::get('/f/{featureKey}/{pageKey?}', \App\Livewire\Runtime\FormEngine::class)
    ->middleware(['web', 'auth'])
    ->name('v3.runtime');

// V3 Studio (HQ Admin)
Route::prefix('studio')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', \App\Livewire\Studio\Dashboard::class)->name('studio.dashboard');
    Route::get('/monitor', \App\Livewire\Studio\RuntimeMonitor::class)->name('studio.monitor');

    // Engine Builders
    Route::get('/flow-canvas/{flowId}', \App\Livewire\Studio\FlowCanvasProxy::class)->name('studio.flow-canvas');
    Route::get('/page-builder/{featureVersionId}/{pageId}', \App\Livewire\Studio\PageBuilderProxy::class)->name('studio.page-builder');

    // Publish Workflow / Release Center
    Route::get('/releases', function() {
        return view('studio.publish.release-center');
    })->name('studio.releases');

    Route::get('/releases/{versionId}/review', function($versionId) {
        return view('studio.publish.review', ['versionId' => $versionId]);
    })->name('studio.releases.review');
    
    // Support & Error Reporting
    Route::get('/support/report-issue', [\App\Http\Controllers\SupportController::class, 'reportIssue'])->name('studio.support.report-issue');
    Route::post('/support/submit-report', [\App\Http\Controllers\SupportController::class, 'submitReport'])->name('studio.support.submit-report');
});
