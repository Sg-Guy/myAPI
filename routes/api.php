<?php

use App\Http\Controllers\AController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\LocalisationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get ('/welcome' , function (){
    return 'Welcome' ;
} ) ;
Route::prefix('/user')->controller(UserController::class)->group(function (){
    Route::get('/', function (Request $request) {
        return response()->json(['user'=>$request->user()] , 200);
    })->middleware('auth:sanctum');

    Route::post('/login', 'login')->name('routes.login') ;
    Route::post('/register', 'register')->name('routes.register') ;
    Route::post('/logout', 'logout')->name('routes.logout')->middleware('auth:sanctum');
    Route::get('/profil', 'profil')->name('routes.profil')->middleware('auth:sanctum');
    Route::put('/update', 'update')->name('routes.update')->middleware('auth:sanctum') ;
}) ;




Route::prefix('/categories')->controller(CategoryController::class)->group(function (){
    Route::get('/' , 'index')->name('index') ;
    Route::post('/store' , 'store')->name('categories.store') ;
    Route::put('/update/{categorie}' , 'update')->name('categories.update') ;
    Route::delete('/destroy/{categorie}' , 'destroy')->name('categories.destroy') ;
}) ;


Route::prefix('/products')->controller(ProductController::class)->group(function (){
    Route::get('/' , 'index')->name('index') ;
    Route::post('store' , 'store')->name('products.store') ;
    Route::get('vedette' , 'vedette')->name('products.vedette') ;
    Route::get('nouveau' , 'nouveau')->name('products.nouveau') ;
    Route::put('update/{product}' , 'update')->name('products.update') ;
    Route::delete('destroy/{product}' , 'destroy')->name('products.destroy') ;
}) ;

Route::prefix('/sales')->controller(SaleController::class)->group(function (){
    Route::get('/' , 'index')->name('sales.index')->middleware('auth:sanctum') ;
    Route::post('store' , 'store')->name('sales.store')->middleware('auth:sanctum') ;
    Route::get('en_cours' , 'en_cours')->name('sales.en_cours')->middleware('auth:sanctum') ;
    Route::get('annullee' , 'annullee')->name('sales.annullee')->middleware('auth:sanctum') ;
    Route::get('expediee' , 'expediee')->name('sales.expediee')->middleware('auth:sanctum') ;
    Route::put('update/{sale}' , 'update')->name('sales.update') ;
}) ;

Route::prefix('/roles')->controller(RoleController::class)->group(function (){
    Route::get('/' , 'index')->name('index') ;
    Route::post('store' , 'store')->name('roles.store') ;
    Route::put('update/{role}' , 'update')->name('roles.update') ;
}) ;

Route::prefix('/localisations')->controller(LocalisationController::class)->group(function (){
    Route::get('/' , 'index')->name('index')->middleware('auth:sanctum') ;
    Route::post('store' , 'store')->name('store')->middleware('auth:sanctum') ;
    Route::put('update/{id}' , 'update')->middleware('auth:sanctum') ;
}) ;

Route::prefix('/favourites')/*->middleware('auth:sanctum')*/->controller(FavouriteController::class)->name('favourites.')->group(function (){
Route::get('/' , 'index')->name('index') ;
    Route::post('store' , 'store')->name('store') ;
    Route::delete('delete/{favoris}' , 'delete')->name('delete') ;
    //Route::put('update/{localisation}' , 'update')->name('localisations.update') ;
}) ;



