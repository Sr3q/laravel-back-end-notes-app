<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

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

Route::post('login',[AuthController::class,'login']);
Route::post('register',[AuthController::class,'register']);
Route::post('recover-password',[AuthController::class,'recoverPassword']);
Route::post('recover-password-verification',[AuthController::class,'recoverPasswordVerification']);

Route::group(['middleware'=>'auth:sanctum'],function (){
    Route::get('token-check',[AuthController::class,'checkTokenValidate']);
    Route::post('logout',[AuthController::class,'logout']);
    Route::apiResource('note',NoteController::class)->except(['create','edit']);
    Route::post('email-verification',[AuthController::class,'emailVerification']);
    Route::get('resend-email-verification',[AuthController::class,'reSendEmailVerification']);
});


