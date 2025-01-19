<?php

use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/getUserData', [HomeController::class, 'getUserData']);
    Route::post('/validateToken', [HomeController::class, 'validateToken']);
    Route::post('/addproduct', [HomeController::class, 'Addproduct']);
    Route::post('/logout', [HomeController::class, 'Logout']);
    Route::delete('/deleteproduct/{id}', [HomeController::class, 'Deleteproduct']);
    Route::post('/products',[HomeController::class,'Products']);
    Route::post('/add_to_cart', [HomeController::class,'AddtoCart'] );
    Route::get('/itemincart', [HomeController::class, 'getItemInCart']);
    Route::get('/usercart', [HomeController::class, 'userCart']);
});

Route::post('/login',[HomeController::class,'Login']);
Route::post('/register',[HomeController::class,'Register']);
Route::post('/newproduct',[HomeController::class,'Newproduct']);

