<?php

use App\Http\Controllers\Api\v1\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/articles', [ArticleController::class, 'index']);
Route::post('/import', [ArticleController::class, 'import']);
Route::get('/search', [ArticleController::class, 'search']);
