<?php

namespace App\Http\Controllers;

use App\Http\Requests\productRequest;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes  as OA;

#[OA\Tag(
    name: "Products",
    description: "Gestionn des produits"
)]
class ProductController extends Controller
{

    #[OA\Get(
        path: "/api/products",
        description: "Récupération des produits",
        tags: ["Products"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste de produits récuprée avec succès!",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "id",
                                        type: "integer",
                                        example: 1,
                                    ),
                                    new OA\Property(
                                        property: "nom",
                                        type: "string",
                                        example: "Montre Electronique",
                                    ),
                                    new OA\Property(
                                        property: "description",
                                        type: "string",
                                        example: "Haute qualité. Peut durer jusqu'à 4h",
                                    ),
                                    new OA\Property(
                                        property: "stock",
                                        type: "integer",
                                        example: 13,
                                    ),
                                    new OA\Property(
                                        property: "prix_unitaire",
                                        type: "float",
                                        example: 1000.00,
                                    ),
                                    new OA\Property(
                                        property: "prix_promo",
                                        type: "float",
                                        example: 800.00,
                                    ),
                                    new OA\Property(
                                        property: "image",
                                        type: "string",
                                        example: "image.png",
                                    ),
                                    new OA\Property(
                                        property: "vedette",
                                        type: "integer",
                                        example: 0,
                                    ),
                                    new OA\Property(
                                        property: "category_name",
                                        type: "string",
                                        example: "Informatique",
                                    ),
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function  index()
    {
        $all_product = Product::with('category:id,name')->select(
            "id",
            "category_id",
            "nom",
            "description",
            "stock",
            "prix_unitaire",
            "prix_promo",
            "image",
            "vedette",
        )->orderBy('created_at', 'desc')->get();

        $products = $all_product->map(function ($product) {
            //dd($product->category()->name) ;
            return [
                'id' => $product->id,
                //'category_id' => $product->category_id,
                'nom' => $product->nom,
                'description' => $product->description,
                'stock' => $product->stock,
                'prix_unitaire' => $product->prix_unitaire,
                'prix_promo' => $product->prix_promo,
                'image' => $product->image,
                'vedette' => $product->vedette,
                'category_name' => $product->category->name ?? null,
            ];
        });

        return response()->json([
            'data' => $products

        ], 200);
    }


    //Produits en vedette

    #[OA\Get(
        path: "/api/products/vedette",
        description: "Produits en vedette",
        tags: ["Products"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste de produits en vedette récuprée avec succès!",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "id",
                                        type: "integer",
                                        example: 1,
                                    ),
                                    new OA\Property(
                                        property: "nom",
                                        type: "string",
                                        example: "Montre Electronique",
                                    ),
                                    new OA\Property(
                                        property: "description",
                                        type: "string",
                                        example: "Haute qualité. Peut durer jusqu'à 4h",
                                    ),
                                    new OA\Property(
                                        property: "stock",
                                        type: "integer",
                                        example: 13,
                                    ),
                                    new OA\Property(
                                        property: "prix_unitaire",
                                        type: "float",
                                        example: 1000.00,
                                    ),
                                    new OA\Property(
                                        property: "prix_promo",
                                        type: "float",
                                        example: 800.00,
                                    ),
                                    new OA\Property(
                                        property: "image",
                                        type: "string",
                                        example: "image.png",
                                    ),
                                    new OA\Property(
                                        property: "vedette",
                                        type: "integer",
                                        example: 0,
                                    ),
                                    new OA\Property(
                                        property: "category_name",
                                        type: "string",
                                        example: "Informatique",
                                    ),
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function  vedette()
    {
        $all_product = Product::with('category:id,name')->select(
            "id",
            "category_id",
            "nom",
            "description",
            "stock",
            "prix_unitaire",
            "prix_promo",
            "image",
            "vedette",
        )->where('vedette', true)->get();

        $products = $all_product->map(function ($product) {
            return [
                'id' => $product->id,
                //'category_id' => $product->category_id,
                'nom' => $product->nom,
                'description' => $product->description,
                'stock' => $product->stock,
                'prix_unitaire' => $product->prix_unitaire,
                'prix_promo' => $product->prix_promo,
                'image' => $product->image,
                'vedette' => $product->vedette,
                'category_name' => $product->category->name ?? null,
            ];
        });

        return response()->json([
            'data' => $products

        ], 200);
    }



    //Produits nouveaux
    #[OA\Get(
        path: "/api/products/nouveau",
        description: "Produits nouveaux",
        tags: ["Products"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste de produits des nouveaux produits récuprée avec succès!",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "id",
                                        type: "integer",
                                        example: 1,
                                    ),
                                    new OA\Property(
                                        property: "nom",
                                        type: "string",
                                        example: "Montre Electronique",
                                    ),
                                    new OA\Property(
                                        property: "description",
                                        type: "string",
                                        example: "Haute qualité. Peut durer jusqu'à 4h",
                                    ),
                                    new OA\Property(
                                        property: "stock",
                                        type: "integer",
                                        example: 13,
                                    ),
                                    new OA\Property(
                                        property: "prix_unitaire",
                                        type: "float",
                                        example: 1000.00,
                                    ),
                                    new OA\Property(
                                        property: "prix_promo",
                                        type: "float",
                                        example: 800.00,
                                    ),
                                    new OA\Property(
                                        property: "image",
                                        type: "string",
                                        example: "image.png",
                                    ),
                                    new OA\Property(
                                        property: "vedette",
                                        type: "integer",
                                        example: 0,
                                    ),
                                    new OA\Property(
                                        property: "category_name",
                                        type: "string",
                                        example: "Informatique",
                                    ),
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function  nouveau()
    {
        $all_product = Product::with('category:id,name')->orderBy('created_at', 'desc')->limit(5)->get();
        $all_product = Product::with('category:id,name')->select(
            "id",
            "category_id",
            "nom",
            "description",
            "stock",
            "prix_unitaire",
            "prix_promo",
            "image",
            "vedette",
        )->orderBy('created_at', 'desc')->limit(5)->get();

        $products = $all_product->map(function ($product) {
            return [
                'id' => $product->id,
                //'category_id' => $product->category_id,
                'nom' => $product->nom,
                'description' => $product->description,
                'stock' => $product->stock,
                'prix_unitaire' => $product->prix_unitaire,
                'prix_promo' => $product->prix_promo,
                'image' => $product->image,
                'vedette' => $product->vedette,
                'category_name' => $product->category->name ?? null,
            ];
        });

        return response()->json([
            'data' => $products

        ], 200);
    }



    //Créer un nouveau produit
    #[OA\Post(
        path: "/api/products/store",
        description: "Création de nouveau produit",
        tags: ["Products"],
        security: [["sanctum"=>[]]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
               mediaType: "multipart/form-data",
               schema: new OA\Schema(
                 required: [
                    "category_id",
                    "nom",
                    "description",
                    "stock",
                    "prix_unitaire",
                    "image",
                ],

                properties: [
                    new OA\Property(
                        property: "category_id",
                        type: "integer",
                        example: 1,
                        description: "Doit être une catégorie existante !"
                    ),
                    new OA\Property(
                        property: "nom",
                        type: "string",
                        example: "PC"
                    ),
                    new OA\Property(
                        property: "description",
                        type: "string",
                        example: "PC authentique"
                    ),
                    new OA\Property(
                        property: "stock",
                        type: "integer",
                        example: 20
                    ),
                    new OA\Property(
                        property: "prix_unitaire",
                        type: "float",
                        example: 100000.00
                    ),
                    new OA\Property(
                        property: "prix_promo",
                        type: "float",
                        example: 90000.00,
                        description: "Optionnel"
                    ),
                    new OA\Property(
                        property: "image",
                        type: "string",
                        format:"binary",
                        example: "url/vers/image"
                    ),
                ],

               )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Création effectuée",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        
                        properties: [
                            new OA\Property(
                                property: "category_id",
                                type: "integer",
                                example: "Informatique",
                                description: "Doit être une catégorie existante !"
                            ),
                            new OA\Property(
                                property: "nom",
                                type: "string",
                                example: "PC"
                            ),
                            new OA\Property(
                                property: "description",
                                type: "string",
                                example: "PC authentique"
                            ),
                            new OA\Property(
                                property: "stock",
                                type: "integer",
                                example: 20
                            ),
                            new OA\Property(
                                property: "prix_unitaire",
                                type: "float",
                                example: 100000.00
                            ),
                            new OA\Property(
                                property: "prix_promo",
                                type: "float",
                                example: 90000.00,
                                description: "Optionnel"
                            ),
                            new OA\Property(
                                property: "image",
                                type: "string",
                                example: "url/vers/image"
                            ),
                        ]
                    )
                )
            ) ,
            new OA\Response(
                response: 401 ,
                description: "Non authentifé !",
                content: new OA\JsonContent(
                    type: "string" ,
                    properties: [
                        new OA\Property(
                            property: "message",
                            type:"string" ,
                            example:"Action impossible ! Vous n'êtes pas un Administrateur."
                        )
                    ]
                )
            ) ,
        ]
    )]
    public function store(productRequest $productRequest)
    {
        if ($productRequest->user() && $productRequest->user()->role->nom == "Admin") {


            $product = new Product();
            $product->nom = $productRequest->nom;
            $product->category_id = $productRequest->category_id;
            $product->description = $productRequest->description;
            $product->stock = $productRequest->stock;
            $product->prix_unitaire = $productRequest->prix_unitaire;
            $product->prix_promo = $productRequest->prix_promo;
            // $product->image = $productRequest->image ; //revoire la methode de stockage
            if ($productRequest->hasFile('image')) {
                $chemin_image = $productRequest->file('image')->store('images', 'public');
                //dd($chemin_image);

            }
            //dd($product) ;
            $product->image = $chemin_image; //revoire la methode de stockage
            $product->vedette = $productRequest->vedette;


            try {
                $product->save();

                return response()->json([
                    'message' => "Création effectuée",
                    'data' => $product,
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'message' => "Requête erronnée" . $e
                ], 400);
            }
        } else {
            return response()->json([
                "message" => "Action impossible ! Vous n'êtes pas un Administrateur",
            ], 401);
        }
    }
    public function update(productRequest $request/*Product $product*/)
    {
        $product = Product::where('id', '=', 1);
        dd($product);
        $product->nom = $request->nom;
        $product->category_id = $request->category_id;
        $product->description = $request->description;
        $product->stock = $request->stock;
        $product->prix_unitaire = $request->prix_unitaire;
        $product->prix_promo = $request->prix_promo;
        $product->image = $request->image; //revoire la methode de stockage

        //dd($product) ;
        try {
            $product->update();

            return response()->json([
                'message' => "mise à jour effectuée",
                //'data'=>$product ,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Requête erronnée" . $e
            ], 404);
        }
    }

    public function destroy(Product $product)
    {

        //$product = Product::find($product);
        try {
            if ($product) {
                //dd($product);
                //$product = $product;
                $product->delete();
                return  response()->json(
                    [
                        'message' => 'produit supprimé avec succès'
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'message' => "Le produit n'existe pas",
                    ],
                    400
                );
            }
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => "Erreur: " . $e,
                ],
                500
            );
        }
    }
}
