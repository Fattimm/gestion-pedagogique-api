<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnneeScolaireController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\SemestreController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\SessionDeCoursController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\AbsenceController;
use Laravel\Passport\Http\Controllers\ScopeController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;

Route::prefix('v1')->group(function () {

    // ─── Routes publiques ───────────────────────────────────────────
    Route::post('/login',           [AuthController::class, 'login']);
    Route::get('/logout',           [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/forgot-password', [PasswordResetController::class, 'demander']);
    Route::post('/reset-password',  [PasswordResetController::class, 'reinitialiser']);

    // ─── Routes authentifiées ───────────────────────────────────────
    Route::middleware('auth:api')->group(function () {

        // ── Gestion des utilisateurs (MANAGER) ──────────────────────
        Route::middleware('role:MANAGER')->group(function () {
            Route::post('/users',          [UserController::class, 'store']);
            Route::patch('/users/{id}',    [UserController::class, 'update']);
            Route::delete('/users/{id}',   [UserController::class, 'destroy']);
        });

        // Lecture utilisateurs (MANAGER, CM)
        Route::middleware('role:MANAGER,CM')->group(function () {
            Route::get('/users',       [UserController::class, 'index']);
            Route::get('/users/{id}',  [UserController::class, 'show']);
        });

        // ── Ressources pédagogiques de base (MANAGER uniquement) ────
        Route::middleware('role:MANAGER')->group(function () {
            Route::apiResource('annees-scolaires', AnneeScolaireController::class)
                ->parameters(['annees-scolaires' => 'id']);
            Route::post('/annees-scolaires/{anneeId}/classes',              [AnneeScolaireController::class, 'planifierClasse']);
            Route::delete('/annees-scolaires/{anneeId}/classes/{classeId}', [AnneeScolaireController::class, 'retirerClasse']);

            Route::apiResource('classes',  ClasseController::class)->parameters(['classes'  => 'id']);
            Route::apiResource('salles',   SalleController::class)->parameters(['salles'    => 'id']);
            Route::apiResource('semestres', SemestreController::class)->parameters(['semestres' => 'id']);
            Route::apiResource('modules',  ModuleController::class)->parameters(['modules'  => 'id']);

            // Inscriptions étudiants
            Route::post('/inscriptions/importer',  [InscriptionController::class, 'importer']);
            Route::delete('/inscriptions/{id}',    [InscriptionController::class, 'destroy']);

            // Cours : création, modification, suppression, étudiants
            Route::post('/cours',                [CoursController::class, 'store']);
            Route::patch('/cours/{id}',          [CoursController::class, 'update']);
            Route::delete('/cours/{id}',         [CoursController::class, 'destroy']);
            Route::get('/cours/{id}/etudiants',  [CoursController::class, 'etudiants']);

            // Sessions : création
            Route::post('/sessions', [SessionDeCoursController::class, 'store']);
        });

        // Lecture classes planifiées (MANAGER, COACH, APPRENANT)
        Route::middleware('role:MANAGER,COACH,APPRENANT')->group(function () {
            Route::get('/annees-scolaires/{anneeId}/classes',   [AnneeScolaireController::class, 'classesPlanifiees']);
            Route::get('/annees-scolaires/{anneeId}/semestres', [SemestreController::class, 'parAnnee']);
        });

        // ── Cours : lecture (tous les rôles authentifiés) ───────────
        Route::get('/cours',       [CoursController::class, 'index']);
        Route::get('/cours/{id}',  [CoursController::class, 'show']);

        // Vue COACH : ses cours filtrés par jour/semaine/statut
        Route::get('/cours/professeur/{profId}', [CoursController::class, 'parProfesseur'])
            ->middleware('role:COACH,MANAGER,CM');

        // Vue APPRENANT : ses cours filtrés par période/module
        Route::get('/cours/etudiant/{etudiantId}', [CoursController::class, 'parEtudiant'])
            ->middleware('role:APPRENANT,MANAGER,CM');

        // ── Sessions ────────────────────────────────────────────────
        Route::get('/sessions', [SessionDeCoursController::class, 'index'])
            ->middleware('role:CM,MANAGER');
        Route::get('/cours/{id}/sessions', [SessionDeCoursController::class, 'parCours']);
        Route::get('/sessions/{id}',       [SessionDeCoursController::class, 'show']);
        Route::patch('/sessions/{id}',     [SessionDeCoursController::class, 'update'])
            ->middleware('role:MANAGER');

        // Annuler une session : MANAGER ou COACH (demande)
        Route::patch('/sessions/{id}/annuler', [SessionDeCoursController::class, 'annuler'])
            ->middleware('role:MANAGER,COACH');

        // Vue COACH : ses sessions filtrées par jour/semaine
        Route::get('/sessions/professeur/{profId}', [SessionDeCoursController::class, 'parProfesseur'])
            ->middleware('role:COACH,MANAGER,CM');

        // Vue APPRENANT : ses sessions filtrées par période/module
        Route::get('/sessions/etudiant/{etudiantId}', [SessionDeCoursController::class, 'parEtudiant'])
            ->middleware('role:APPRENANT,MANAGER,CM');

        // Heures du mois d'un professeur (COACH, CM, MANAGER)
        Route::get('/sessions/professeur/{profId}/heures-mois', [SessionDeCoursController::class, 'heuresMois'])
            ->middleware('role:COACH,CM,MANAGER');

        // Bilan mensuel d'un prof avec quota (CM = Attaché)
        Route::get('/sessions/professeur/{profId}/bilan', [SessionDeCoursController::class, 'bilanProfesseur'])
            ->middleware('role:CM,MANAGER');

        // ── Inscriptions : lecture (MANAGER, CM) ────────────────────
        Route::get('/inscriptions', [InscriptionController::class, 'index'])
            ->middleware('role:MANAGER,CM');

        // ── Émargement ──────────────────────────────────────────────
        // Signer sa présence (APPRENANT)
        Route::post('/sessions/{sessionId}/signer', [EmargementController::class, 'signer'])
            ->middleware('role:APPRENANT');

        // Valider la session = générer les absences (CM = Attaché)
        Route::post('/sessions/{sessionId}/valider', [EmargementController::class, 'validerSession'])
            ->middleware('role:CM');

        // Invalider une session (annuler une validation) (CM = Attaché)
        Route::delete('/sessions/{sessionId}/valider', [EmargementController::class, 'invaliderSession'])
            ->middleware('role:CM');

        // Voir les émargements d'une session (CM, MANAGER)
        Route::get('/sessions/{sessionId}/emargements', [EmargementController::class, 'parSession'])
            ->middleware('role:CM,MANAGER');

        // ── Absences ────────────────────────────────────────────────
        // Voir ses absences (APPRENANT lui-même, MANAGER, CM)
        Route::get('/absences/etudiant/{etudiantId}', [AbsenceController::class, 'parEtudiant'])
            ->middleware('role:APPRENANT,MANAGER,CM');

        // Justifier une absence (APPRENANT)
        Route::patch('/absences/{id}/justifier', [AbsenceController::class, 'justifier'])
            ->middleware('role:APPRENANT');

        // Traiter une justification (CM = Attaché)
        Route::patch('/absences/{id}/traiter', [AbsenceController::class, 'traiter'])
            ->middleware('role:CM');

        // Absences par professeur avec filtre mois/module (CM, MANAGER)
        Route::get('/absences/professeur/{professeurId}', [AbsenceController::class, 'parProfesseur'])
            ->middleware('role:CM,MANAGER');
    });
});


// ─── Routes OAuth Passport ──────────────────────────────────────────────────
Route::post('/token', [
    'uses'       => [AccessTokenController::class, 'issueToken'],
    'as'         => 'token',
    'middleware' => 'throttle',
]);

Route::get('/authorize', [
    'uses'       => [AuthorizationController::class, 'authorize'],
    'as'         => 'authorizations.authorize',
    'middleware' => 'web',
]);

$guard = config('passport.guard', null);

Route::middleware(['web', $guard ? 'auth:' . $guard : 'auth'])->group(function () {
    Route::post('/token/refresh', [
        'uses' => [TransientTokenController::class, 'refresh'],
        'as'   => 'token.refresh',
    ]);
    Route::post('/authorize', [
        'uses' => [ApproveAuthorizationController::class, 'approve'],
        'as'   => 'authorizations.approve',
    ]);
    Route::delete('/authorize', [
        'uses' => [DenyAuthorizationController::class, 'deny'],
        'as'   => 'authorizations.deny',
    ]);
    Route::get('/tokens', [
        'uses' => [AuthorizedAccessTokenController::class, 'forUser'],
        'as'   => 'tokens.index',
    ]);
    Route::delete('/tokens/{token_id}', [
        'uses' => [AuthorizedAccessTokenController::class, 'destroy'],
        'as'   => 'tokens.destroy',
    ]);
    Route::get('/scopes', [
        'uses' => [ScopeController::class, 'all'],
        'as'   => 'scopes.index',
    ]);
    Route::get('/personal-access-tokens', [
        'uses' => [PersonalAccessTokenController::class, 'forUser'],
        'as'   => 'personal.tokens.index',
    ]);
    Route::post('/personal-access-tokens', [
        'uses' => [PersonalAccessTokenController::class, 'store'],
        'as'   => 'personal.tokens.store',
    ]);
    Route::delete('/personal-access-tokens/{token_id}', [
        'uses' => [PersonalAccessTokenController::class, 'destroy'],
        'as'   => 'personal.tokens.destroy',
    ]);
});
