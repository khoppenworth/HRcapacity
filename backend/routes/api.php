<?php

use App\Http\Controllers\API\AssessmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InstallationController;
use App\Http\Controllers\API\QuestionnaireController;
use App\Http\Controllers\API\WorkFunctionController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function (): void {
    Route::get('install/status', [InstallationController::class, 'status']);
    Route::post('install', [InstallationController::class, 'install']);

    Route::middleware(['tenant'])->group(function (): void {
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('auth/logout', [AuthController::class, 'logout']);

            Route::middleware('role:tenant_admin')->group(function (): void {
                Route::apiResource('work-functions', WorkFunctionController::class);
                Route::get('questionnaires', [QuestionnaireController::class, 'index']);
                Route::post('questionnaires', [QuestionnaireController::class, 'store']);
                Route::get('questionnaires/{questionnaire}', [QuestionnaireController::class, 'show']);
                Route::put('questionnaires/{questionnaire}', [QuestionnaireController::class, 'update']);
                Route::post('questionnaires/{questionnaire}/publish', [QuestionnaireController::class, 'publish']);
            });

            Route::prefix('my')->group(function (): void {
                Route::get('assessments', [AssessmentController::class, 'index']);
                Route::post('assessments', [AssessmentController::class, 'createOrFetch']);
                Route::get('assessments/{assessment}', [AssessmentController::class, 'show']);
                Route::put('assessments/{assessment}/save-draft', [AssessmentController::class, 'saveDraft']);
                Route::put('assessments/{assessment}/submit', [AssessmentController::class, 'submit']);
            });
        });
    });
});
