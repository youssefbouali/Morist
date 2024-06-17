<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\StarController;
use App\Http\Controllers\ReservationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    //return ['Laravel' => app()->version()];
    return ['MORIST_API_VERSION' => 6.0];
});

Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);

Route::get('/api/user/{user}', [UserController::class, 'getUser']);
Route::get('/api/user', [UserController::class, 'getSessionUser']);
Route::put('/api/user', [UserController::class, 'setUser']);
Route::post('/api/logout', [UserController::class, 'logout']);

Route::get('/api/explore/undefined', [OfferController::class, 'exploreall']);
Route::get('/api/explore/{city}', [OfferController::class, 'explore']);
Route::get('/api/search/{resource}', [OfferController::class, 'search']);

Route::post('/api/Offre', [OfferController::class, 'storeOffre']);
Route::get('/api/Offre', [OfferController::class, 'indexSessionOffre']);
Route::get('/api/Offre/{id}', [OfferController::class, 'showOffre']);
Route::put('/api/Offre/{id}', [OfferController::class, 'updateOffre']);
Route::delete('/api/Offre/{id}', [OfferController::class, 'destroyOffre']);
Route::get('/api/Offre/u/{user}', [OfferController::class, 'indexUserOffre']);

Route::post('/api/panier', [PanierController::class, 'postPanier']);
Route::get('/api/panier', [PanierController::class, 'getPanier']);
Route::delete('/api/panier/{id_offre}', [PanierController::class, 'destroyPanier']);

Route::post('/api/Star', [StarController::class, 'storeStar']);
Route::get('/api/Star', [StarController::class, 'indexStar']);
Route::get('/api/Star/{id_offre}', [StarController::class, 'showStar']);
Route::delete('/api/Star/{id_offre}', [StarController::class, 'destroyStar']);

Route::post('/api/Reservation', [ReservationController::class, 'storeReservation']);
Route::get('/api/Reservation', [ReservationController::class, 'indexReservation']);
Route::get('/api/Reservation/{id}', [ReservationController::class, 'showReservation']);
Route::put('/api/Reservation/{id}', [ReservationController::class, 'updateReservation']);
Route::delete('/api/Reservation/{id}', [ReservationController::class, 'destroyReservation']);

require __DIR__.'/auth.php';




























/*Route::post('/user', function (*//*Request $request*//*) {
	//$userData = $request->all();
    return ['user' => "h"];
});

Route::get('/user', function () {
    return ['user' => "{0:{'username':'u1'},1:{'username':'u2'}}"];
});

Route::get('/user/{user}', function ($user1) {
    return ['user' => $user1];
});

Route::put('/user', function () {
    return ['user' => "Updated"];
});

Route::delete('/user', function () {
    return ['user' => "Deleted"];
});*/