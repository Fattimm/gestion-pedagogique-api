<?php

use App\Models\User;
use App\Models\Referentiel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ReferentielController;
use App\Http\Controllers\AuthControllerFirebase;
use Laravel\Passport\Http\Controllers\ScopeController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {

});


Route::prefix('v1')->group(function () {


    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout']);


    Route::get('/users', [UserController::class, 'index']); 
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{id}', [UserController::class, 'update']); 
    Route::delete('/users/{id}', [UserController::class, 'destroy']); 
    Route::get('/users/{id}', [UserController::class, 'show']);


    Route::get('/referentiels', [ReferentielController::class, 'index']);
    Route::post('/referentiels', [ReferentielController::class, 'store']);
    Route::get('/referentiels/{id}', [ReferentielController::class, 'show']);
    Route::patch('/referentiels/{id}', [ReferentielController::class, 'update']);
    Route::delete('/referentiels/{id}', [ReferentielController::class, 'destroy']);
    Route::get('/archive/referentiels', [ReferentielController::class, 'archive']);


    Route::post('/promotions', [PromoController::class, 'store']);
    Route::get('/promotions', [PromoController::class, 'index']);
    Route::patch('/promotions/{id}', [PromoController::class, 'update']);
    Route::get('/promotions/encours', [PromoController::class, 'getCurrentPromo']);
    Route::patch('/promotions/{id}/referentiels', [PromoController::class, 'updateReferentiels']);
    Route::patch('/promotions/{id}/etat', [PromoController::class, 'updateStatus']);
    Route::get('/promotions/{id}/stats', [PromoController::class, 'getStats']);
    Route::patch('/promotions/{id}/cloturer', [PromoController::class, 'closePromo']);
    Route::get('/promotions/{id}/referentiels', [PromoController::class, 'getReferentiels']);

});









Route::post('/token', [
    'uses' => [AccessTokenController::class, 'issueToken'],
    'as' => 'token',
    'middleware' => 'throttle',
]);

Route::get('/authorize', [
    'uses' => [AuthorizationController::class, 'authorize'],
    'as' => 'authorizations.authorize',
    'middleware' => 'web',
]);

$guard = config('passport.guard', null);

Route::middleware(['web', $guard ? 'auth:' . $guard : 'auth'])->group(function () {
    Route::post('/token/refresh', [
        'uses' => [TransientTokenController::class, 'refresh'],
        'as' => 'token.refresh',
    ]);

    Route::post('/authorize', [
        'uses' => [ApproveAuthorizationController::class, 'approve'],
        'as' => 'authorizations.approve',
    ]);

    Route::delete('/authorize', [
        'uses' => [DenyAuthorizationController::class, 'deny'],
        'as' => 'authorizations.deny',
    ]);

    Route::get('/tokens', [
        'uses' => [AuthorizedAccessTokenController::class, 'forUser'],
        'as' => 'tokens.index',
    ]);

    Route::delete('/tokens/{token_id}', [
        'uses' => [AuthorizedAccessTokenController::class, 'destroy'],
        'as' => 'tokens.destroy',
    ]);

    // Route::get('/clients', [
    //     'uses' => [ClientController::class, 'forUser'],
    //     'as' => 'clients.index',
    // ]);

    // Route::post('/clients', [
    //     'uses' => [ClientController::class, 'store'],
    //     'as' => 'clients.store',
    // ]);

    // Route::put('/clients/{client_id}', [
    //     'uses' => [ClientController::class, 'update'],
    //     'as' => 'clients.update',
    // ]);

    // Route::delete('/clients/{client_id}', [
    //     'uses' => [ClientController::class, 'destroy'],
    //     'as' => 'clients.destroy',
    // ]);

    Route::get('/scopes', [
        'uses' => [ScopeController::class, 'all'],
        'as' => 'scopes.index',
    ]);

    Route::get('/personal-access-tokens', [
        'uses' => [PersonalAccessTokenController::class, 'forUser'],
        'as' => 'personal.tokens.index',
    ]);

    Route::post('/personal-access-tokens', [
        'uses' => [PersonalAccessTokenController::class, 'store'],
        'as' => 'personal.tokens.store',
    ]);

    Route::delete('/personal-access-tokens/{token_id}', [
        'uses' => [PersonalAccessTokenController::class, 'destroy'],
        'as' => 'personal.tokens.destroy',
    ]);
});
