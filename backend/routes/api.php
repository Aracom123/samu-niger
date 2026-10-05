<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CapaciteController;
use App\Http\Controllers\Api\ConfirmationAccueilController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\NotificationEvenementController;
use App\Http\Controllers\Api\OrientationController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\StructureSanitaireController;
use App\Http\Controllers\Api\TypeStructureController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Référentiels — gestion réservée à l'administrateur national.
    Route::apiResource('regions', RegionController::class)->middleware('permission:regions.gerer')
        ->except(['index', 'show']);
    Route::get('regions', [RegionController::class, 'index']);
    Route::get('regions/{region}', [RegionController::class, 'show']);

    Route::apiResource('types-structures', TypeStructureController::class)
        ->middleware('permission:types-structures.gerer')->except(['index', 'show']);
    Route::get('types-structures', [TypeStructureController::class, 'index']);
    Route::get('types-structures/{type_structure}', [TypeStructureController::class, 'show']);

    // Structures sanitaires
    Route::get('structures', [StructureSanitaireController::class, 'index']);
    Route::get('structures/{structure_sanitaire}', [StructureSanitaireController::class, 'show']);
    Route::middleware('permission:structures.gerer')->group(function () {
        Route::post('structures', [StructureSanitaireController::class, 'store']);
        Route::put('structures/{structure_sanitaire}', [StructureSanitaireController::class, 'update']);
        Route::delete('structures/{structure_sanitaire}', [StructureSanitaireController::class, 'destroy']);
    });

    // Capacités des lits (rattachées directement à la structure — service unique d'urgence)
    Route::get('capacites', [CapaciteController::class, 'index']);
    Route::get('capacites/{capacite}', [CapaciteController::class, 'show']);
    Route::get('capacites/{capacite}/historique', [CapaciteController::class, 'historique']);
    Route::put('capacites/{capacite}', [CapaciteController::class, 'update'])
        ->middleware('permission:capacite.mettre-a-jour');

    // Patients
    Route::apiResource('patients', PatientController::class)->except(['destroy']);

    // Interventions
    Route::apiResource('interventions', InterventionController::class)->except(['destroy']);
    Route::post('interventions/{intervention}/statut', [InterventionController::class, 'changerStatut']);

    // Orientation et confirmation d'accueil
    Route::apiResource('orientations', OrientationController::class)->only(['index', 'store', 'show']);
    Route::post('orientations/{orientation}/confirmation', [ConfirmationAccueilController::class, 'store'])
        ->middleware('permission:accueil.confirmer');

    // Tableau de bord
    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.consulter');

    // Notifications
    Route::get('notifications', [NotificationEvenementController::class, 'index']);
    Route::patch('notifications/{notification_evenement}', [NotificationEvenementController::class, 'update']);
});
