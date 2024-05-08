<?php

use Illuminate\Support\Facades\Route;

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
    return ['Laravel' => app()->version()];
});

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

require __DIR__.'/auth.php';
