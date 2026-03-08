<?php

use App\Http\Controllers\AController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DashBoardController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\LocalisationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/user')->group(function (){
    Route::get('/', function (Request $request) {
        return response()->json(['user'=>$request->user()] , 200);
    })->middleware('auth:sanctum');

    Route::post('/login', [UserController::class,'login']) ;
    Route::post('/register', [UserController::class ,'register']) ;
    Route::post('/logout', [UserController::class,'logout'])->middleware('auth:sanctum');
    Route::get('/profile',[UserController::class, 'profile'])->middleware('auth:sanctum');
    Route::put('/update', [UserController::class,'update'])->middleware('auth:sanctum') ;
}) ;

Route::get("/dashboard" , [DashBoardController::class , 'dashboard'])->middleware(['auth:sanctum' , 'role:Admin']) ;




Route::apiResource('categories' , CategoryController::class) ;




Route::prefix('/products')->group(function (){
    Route::get('/admin' , [ProductController::class, "forAdmin"])->middleware(["auth:sanctum" , "role:Admin"]);
    Route::get('/' , [ProductController::class, "index"]);
    Route::post('store' , [ProductController::class , "store"])->middleware(["auth:sanctum" , 'role:Admin']);
    Route::get('vedette' , [ProductController::class , "vedette"]);
    Route::get('nouveau' , [ProductController::class , "nouveau"]);
    Route::put('update/{product}' , [ProductController::class , "update"])->middleware(["auth:sanctum", 'role:Admin']) ;
    Route::delete('destroy/{product}' , [ProductController::class ,"destroy"])->middleware(["auth:sanctum", 'role:Admin']) ;
    Route::get('details/admin/{product}' , [ProductController::class ,"detailForAdmin"]) ; //->middleware(["auth:sanctum"]) ;

}) ;

Route::prefix('/sales')->group(function (){
    Route::get('/' , [SaleController::class ,'index'])->middleware('auth:sanctum') ;
    Route::post('store' ,[SaleController::class, 'store'])->middleware('auth:sanctum') ;
    Route::get('en_cours' , [SaleController::class,'en_cours'])->middleware('auth:sanctum') ;
    Route::get('annullee' , [SaleController::class ,'annullee'])->middleware('auth:sanctum') ;
    Route::get('expediee' , [SaleController::class ,'expediee'])->middleware('auth:sanctum') ;
    Route::put('update/{sale}' , [SaleController::class,'update']) ;
}) ;

Route::/*middleware(["auth:sanctum" , 'role:Admin'])->*/prefix('/roles')->group(function (){
Route::get('/' , [RoleController::class ,'index']) ;
    Route::post('store' , [RoleController::class ,'store']) ;
    Route::put('update/{id}' , [RoleController::class ,'update']) ;
    Route::put('destroy/{id}' , [RoleController::class ,'destroy']) ;
    Route::get('show/{id}' , [RoleController::class ,'show']) ;
}) ;

Route::prefix('/localisations')->controller(LocalisationController::class)->group(function (){
    Route::get('/' , 'index')->name('index')->middleware('auth:sanctum') ;
    Route::post('store' , 'store')->name('store')->middleware('auth:sanctum') ;
    Route::put('update/{id}' , 'update')->middleware('auth:sanctum') ;
}) ;

Route::middleware(['auth:sanctum'])->prefix('/favourites')->group(function (){
Route::get('/' ,[FavouriteController::class , 'index']) ;
    Route::post('store/{article}',  [FavouriteController::class , 'store']);
    Route::delete('delete/{article}' ,  [FavouriteController::class , 'destroy']) ;
    //Route::put('update/{localisation}' , 'update')->name('localisations.update') ;
}) ;



