<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Studio\FlowBuilderController;
use App\Http\Controllers\Api\Studio\PageBuilderController;
use App\Http\Controllers\Api\Studio\ApprovalController;
use App\Http\Controllers\Api\Studio\ImpactAnalysisController;
use App\Http\Controllers\Api\Studio\SimulationController;
use App\Http\Controllers\Api\Studio\ScopeOverrideController;

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
    $user = \App\Models\User::where('email', 'manager1@arrahnu.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/branch');
    }
    return 'Manager user not found. Please seed database.';
});

Route::get('/login-teller', function() {
    $user = \App\Models\User::where('email', 'staff1@arrahnu.com')->first();
    if ($user) {
        auth()->login($user);
        return redirect('/f/new-pledge');
    }
    return "Teller user not found. Please run seeders first.";
});

// ─── Staff Portal & Branch Manager Toggle ───
Route::post('/branch/toggle-view', [\App\Http\Controllers\Branch\ViewToggleController::class, 'toggle'])
    ->middleware(['web', 'auth', 'role:branch_manager'])
    ->name('branch.toggle-view');

Route::get('/portal', \App\Livewire\Runtime\StaffPortal::class)
    ->middleware(['web', 'auth'])
    ->name('runtime.portal');

// V3 Dynamic Feature Runtime
Route::get('/f/{featureKey}/{pageKey?}', \App\Livewire\Runtime\FormEngine::class)
    ->middleware(['web', 'auth', 'permission:runtime.execute', \App\Http\Middleware\LogFeatureAccess::class])
    ->name('v3.runtime');

// ─── Branch Manager Dashboard ───
Route::prefix('branch')->middleware(['web', 'auth', 'role:branch_manager'])->group(function () {
    Route::get('/', \App\Livewire\Branch\BranchDashboard::class)->name('branch.dashboard');
    Route::get('/staff', \App\Livewire\Branch\StaffActivity::class)->name('branch.staff');
    Route::get('/features', \App\Livewire\Branch\AvailableFeatures::class)->name('branch.features');
    Route::get('/support', \App\Livewire\Branch\BranchSupport::class)->name('branch.support');
});

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

    // Audit Trail Logs
    Route::get('/audit', \App\Livewire\Studio\AuditLogs::class)->name('studio.audit');
});

// Studio API Endpoints (Using web session for authentication)
Route::prefix('api/studio')->middleware(['web', 'auth'])->group(function () {
    // Versions & Approval
    Route::prefix('versions')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->middleware('permission:versions.view');
        Route::get('/rollback-history', [ApprovalController::class, 'rollbackHistory'])->middleware('permission:versions.view');
        Route::get('{id}', [ApprovalController::class, 'show'])->middleware('permission:versions.view');
        Route::get('{id}/validations', [ApprovalController::class, 'validations'])->middleware('permission:versions.view');
        Route::post('{id}/submit', [ApprovalController::class, 'submit'])->middleware(['publish.permission:submit', 'permission:versions.submit']);
        Route::post('{id}/approve', [ApprovalController::class, 'approve'])->middleware(['publish.permission:review', 'permission:versions.approve']);
        Route::post('{id}/reject', [ApprovalController::class, 'reject'])->middleware(['publish.permission:review', 'permission:versions.reject']);
        Route::post('{id}/publish', [ApprovalController::class, 'publish'])->middleware(['publish.permission:publish', 'permission:versions.publish']);
        Route::post('{id}/rollback', [ApprovalController::class, 'rollback'])->middleware(['publish.permission:rollback', 'permission:versions.rollback']);
        
        // Impact Analysis
        Route::get('{id}/impact-analysis', [ImpactAnalysisController::class, 'show'])->middleware('permission:versions.view');
        Route::post('{id}/impact-analysis', [ImpactAnalysisController::class, 'analyze'])->middleware('permission:versions.view');
        
        // Simulation
        Route::post('{id}/simulate/{flowKey}', [SimulationController::class, 'simulate'])->middleware('permission:flows.simulate');
        Route::get('{id}/simulations', [SimulationController::class, 'history'])->middleware('permission:versions.view');
    });

    Route::get('simulations/{simulationId}', [SimulationController::class, 'show'])->middleware('permission:versions.view');

    // Flow Builder Endpoints
    Route::prefix('flows')->group(function () {
        Route::post('{flowId}/save', [FlowBuilderController::class, 'save'])->middleware('permission:flows.edit');
        Route::post('{flowId}/validate', [FlowBuilderController::class, 'validate'])->middleware('permission:flows.view');
        Route::post('{flowId}/simulate', [FlowBuilderController::class, 'simulate'])->middleware('permission:flows.simulate');
    });

    // Page Builder Endpoints
    Route::prefix('pages')->group(function () {
        Route::post('{pageId}/save', [PageBuilderController::class, 'save'])->middleware('permission:pages.edit');
        Route::post('{pageId}/validate', [PageBuilderController::class, 'validate'])->middleware('permission:pages.view');
        Route::get('entities', [PageBuilderController::class, 'entities'])->middleware('permission:pages.view');
    });

    // Scope Override Endpoints
    Route::prefix('scope-overrides')->group(function () {
        Route::get('feature/{featureVersionId}', [ScopeOverrideController::class, 'index'])->middleware('permission:scopes.view');
        Route::get('{id}', [ScopeOverrideController::class, 'show'])->middleware('permission:scopes.view');
        Route::post('/', [ScopeOverrideController::class, 'store'])->middleware('permission:scopes.create');
        Route::put('{id}', [ScopeOverrideController::class, 'update'])->middleware('permission:scopes.edit');
        Route::delete('{id}', [ScopeOverrideController::class, 'destroy'])->middleware('permission:scopes.delete');
        Route::post('{id}/expire', [ScopeOverrideController::class, 'expire'])->middleware('permission:scopes.edit');
        Route::post('bulk', [ScopeOverrideController::class, 'bulkStore'])->middleware('permission:scopes.create');
        Route::get('feature/{featureVersionId}/history', [ScopeOverrideController::class, 'history'])->middleware('permission:scopes.view');
        Route::post('test-resolve', [ScopeOverrideController::class, 'testResolve'])->middleware('permission:scopes.view');
        Route::post('feature/{featureVersionId}/clear-cache', [ScopeOverrideController::class, 'clearCache'])->middleware('permission:scopes.edit');
    });
});
