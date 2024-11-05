<?php

use App\Http\Controllers\Api\InformationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/information', [InformationController::class, 'index'])->name('api.information.show');
Route::post('/information', [InformationController::class, 'create'])->name('api.information.create');
Route::put('/information', [InformationController::class, 'edit'])->name('api.information.edit');
Route::delete('/information', [InformationController::class, 'delete'])->name('api.information.delete');


//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
