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

Route::prefix('studio')->group(function () {
    // Versions & Approval
    Route::prefix('versions')->group(function () {
        Route::get('/', [ApprovalController::class, 'index']);
        Route::get('/rollback-history', [ApprovalController::class, 'rollbackHistory']);
        Route::get('{id}', [ApprovalController::class, 'show']);
        Route::get('{id}/validations', [ApprovalController::class, 'validations']);
        Route::post('{id}/submit', [ApprovalController::class, 'submit'])->middleware('publish.permission:submit');
        Route::post('{id}/approve', [ApprovalController::class, 'approve'])->middleware('publish.permission:review');
        Route::post('{id}/reject', [ApprovalController::class, 'reject'])->middleware('publish.permission:review');
        Route::post('{id}/publish', [ApprovalController::class, 'publish'])->middleware('publish.permission:publish');
        Route::post('{id}/rollback', [ApprovalController::class, 'rollback'])->middleware('publish.permission:rollback');
        
        // Impact Analysis
        Route::get('{id}/impact-analysis', [ImpactAnalysisController::class, 'show']);
        Route::post('{id}/impact-analysis', [ImpactAnalysisController::class, 'analyze']);
        
        // Simulation
        Route::post('{id}/simulate/{flowKey}', [SimulationController::class, 'simulate']);
        Route::get('{id}/simulations', [SimulationController::class, 'history']);
    });

    Route::get('simulations/{simulationId}', [SimulationController::class, 'show']);

    // Flow Builder Endpoints
    Route::prefix('flows')->group(function () {
        Route::post('{flowId}/save', [FlowBuilderController::class, 'save']);
        Route::post('{flowId}/validate', [FlowBuilderController::class, 'validate']);
        Route::post('{flowId}/simulate', [FlowBuilderController::class, 'simulate']);
    });

    // Page Builder Endpoints
    Route::prefix('pages')->group(function () {
        Route::post('{pageId}/save', [PageBuilderController::class, 'save']);
        Route::post('{pageId}/validate', [PageBuilderController::class, 'validate']);
        Route::get('entities', [PageBuilderController::class, 'entities']);
    });
});
