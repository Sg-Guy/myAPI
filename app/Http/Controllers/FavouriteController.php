<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes  as OA ;
use Symfony\Contracts\Service\Attribute\Required;

#[OA\Tag(name: "Favoris" , description: "Toutes les routes pour les favoris ici")]
class FavouriteController extends Controller
{

    #[OA\Get(
        path: "/api/favourites",
        description: "les favories d'un utilisateur. Token requis !" ,
        summary: "les favories d'un utilisateur." ,
        security: [["sanctum"=>[]]] ,
        tags: ['Favoris'] ,
        responses: [
            new OA\Response(
                response: 200,
                description: "Récupération effectuée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "favourites",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "product_id",
                                        type: "integer" ,
                                        example: 1
                                    ) ,
                                    new OA\Property(
                                        property: "isFavourite",
                                        type: "integer",
                                        example: 12
                                    )
                                ]
                            )
                        ) ,
                    ]
                )
            ) ,

            new OA\Response(
                response: 401 ,
                description: "Non authentifié !",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "message" ,
                            type: "string" ,
                            example: "Non authentifié"
                        )
                    ]
                )
            )
        ]
        
    )]
    public function index (Request $request) {
        
        if (!$request->user()) {
            return response()->json([
                "message"=>"Non authentifié !" ,
                ] , 401) ;
            }
        
        $user = $request->user() ;
        $favourites = $user->favourite()->select('product_id' , 'isFavourite')->get() ; //Favourite::where('user_id' , $request->user()->id)->get();
        return response()->json([
            'favourites' => $favourites ,
        ] ,200) ;
    }


    #[OA\Post(
        path:"/api/favourites/store",
        description: "Mettre en favoris" ,
        security: ["sanctum"=>[]],
        tags: ["Favoris"] ,
        parameters: [
            new OA\Parameter(
                name: "article",
                in: "path",
                required : true,
                schema: new OA\Schema(
                    type: 'integer'

                )
,            )
        ], 
        responses: [
            new OA\Response(
                response: 200 ,
                description:  "Article rétiré des favoris" ,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property:"message" ,
                            type: "string",
                            example: "Article rétiré des favoris"
                        )
                    ]
                )
            )
        ]
        )]
public function store (Request $request , Product $product) {
    $request->validate([
        'user_id'=>"int|exists:users,id" ,
        'product_id'=>"int|exists:products,id" ,
        'isFavourite' =>'boolean'
        ] , [
        'user_id.exists' => "Vous n'êtes pas connecté !" ,
        'product.exists' => "Ce produit n'existe pas ou a été supprimé !"
    ]) ;

    $fav = new Favourite() ;

    $fav->product_id = $product->product_id ;
    $fav->user_id = $request->user()->user_id ;
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

#[OA\Delete(
    path: "/api/favourites" ,
    summary: "Retirer un élément des favoris" ,
    description: "Rétirer un élément des favoris . Token requis !",
    security: ["sanctum"=>[]] ,
    tags: ["Favoris"] ,
    parameters: [
        new OA\Parameter(
            name: "article",
            in: "path",
            required: true ,
            schema: new OA\Schema(type: "integer")
        )
    ] , 
    responses: [
        new OA\Response(
            response: 200 ,
            description: "Article rétiré des favoris.",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "message" ,
                        type: 'string',
                        example: "Article rétiré des favoris"
                    )
                ]
            )
        ) ,
        new OA\Response(
            response: 404,
            description: "Favoris introuvable" ,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property:"message" ,
                        type:"string" ,
                        example: "Favoris introuvable"
                    )
                ]
            )
        )
    ]
    )]
public function destroy (Request $request , Favourite $favourite) {
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
                'message'=>$product->nom." retiré des favoris",
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