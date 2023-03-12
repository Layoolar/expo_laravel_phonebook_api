<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware(['auth:sanctum', 'admin']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('contacts', ContactController::class);
    Route::get('contacts/search/{name}', [ContactController::class, 'searchUsersContacts']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::get('all_contacts', [ContactController::class, 'showAll']);
    Route::get('all_contacts/search/{name}', [ContactController::class, 'search']);
    Route::get('users/search/{name}', [UserController::class, 'search']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('users', UserController::class)->except(['index', 'destroy',]);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'destroy']);
    Route::get('users/show/{id}', [UserController::class, 'showAny']);
    Route::post('users/update/{id}', [UserController::class, 'updateAny']);
});
