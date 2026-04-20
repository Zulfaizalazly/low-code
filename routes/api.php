<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Studio\FlowBuilderController;
use App\Http\Controllers\Api\Studio\PageBuilderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\Studio\ApprovalController;
use App\Http\Controllers\Api\Studio\ImpactAnalysisController;
use App\Http\Controllers\Api\Studio\SimulationController;
use App\Http\Controllers\Api\Studio\ScopeOverrideController;

Route::prefix('studio')->middleware(['api', 'auth'])->group(function () {
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
