<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function index (Request $request) {
        $favourites = Favourite::where('user_id' , $request->user()->id)->get();
        return response()->json([
            'favourites' => $favourites ,
        ] ,200) ;
    }


public function store (Request $request) {
    $request->validate([
        'user_id'=>"int|exists:users,id" ,
        'product_id'=>"int|exists:products,id" ,
        'isFavourite' =>'boolean'
        ] , [
        'user_id.exists' => "Vous n'êtes pas connecté !" ,
        'product.exists' => "Ce produit n'existe pas ou a été supprimé !"
    ]) ;

    $fav = new Favourite() ;

    $fav->product_id = $request->product_id ;
    $fav->user_id = $request->user_id ;
    $fav->isFavourite = $request->isFavourite;

    try { 
        $fav->save() ;
        $product = Product::find($request->product_id);
        return response()->json([
            'message'=>$product->nom,
            ]) ;
    } catch (Exception $e) {
        return response()->json([
            'message'=> "Une erreur s'est produite:".$e ,
        ]) ;
    }

}
public function delete (Request $request , Favourite $favourite) {
    $request->validate([
        //'user_id'=>"int|exists:users,id" ,
        //'product_id'=>"int|exists:products,id" ,
        'isFavourite' =>'boolean'
        ] , [
        //'user_id.exists' => "Vous n'êtes pas connecté !" ,
        //'product.exists' => "Ce produit n'existe pas ou a été supprimé !"
    ]) ;

   // $favourite->product_id = $request->product_id ;
    //$favourite->user_id = $request->user_id ;
    $favourite->isFavourite = $request->isFavourite;
    //dd($favourite->isFavourite);
    
    try { 
        if (!$request->isFavourite) {
            $product = Product::find($request->product_id);
            $favourite->delete() ;
            return response()->json([
                'message'=>$product->nom." retiré es favoris",
                ] , 200) ;
        } else {
            return response()->json([
                'message'=>"Une erreur s'est produite",
                ] , 200) ;
        }
            
    } catch (Exception $e) {
        return response()->json([
            'message'=> "Une erreur s'est produite:".$e ,
        ]) ;
    }

}


}