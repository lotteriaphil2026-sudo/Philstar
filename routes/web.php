<?php
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;
Route::get('/favicon.ico', fn () => redirect('/favicon.svg'))->name('favicon');
Route::get('/',[NewsController::class,'index'])->name('news.index');
Route::get('/articles/{article}',[NewsController::class,'show'])->name('news.show');
