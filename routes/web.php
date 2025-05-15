<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostagemController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('postagens', PostagemController::class);


Route::resource('postagens', PostagemController::class);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');
