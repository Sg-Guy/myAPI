<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Sale;
use Exception;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Products",
    description: "Gestion des produits"
)]
class ProductController extends Controller
{
    #[OA\Get(
        path: "/api/products",
        summary: "Liste de tous les produits",
        tags: ["Products"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste de produits récupérée avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                ref: "#/components/schemas/Product"
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function index()
    {
        $products = Product::with('category:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nom' => $p->nom,
                'description' => $p->description,
                'stock' => $p->stock,
                'prix_unitaire' => $p->prix_unitaire,
                'prix_promo' => $p->prix_promo,
                'image' => $p->image,
                'vedette' => $p->vedette,
                'category_name' => $p->category->name ?? null,
            ]);

        return response()->json(['data' => $products], 200);
    }

    #[OA\Get(
        path: "/api/products/vedette",
        summary: "Produits en vedette",
        tags: ["Products"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des produits en vedette récupérée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                ref: "#/components/schemas/Product"
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function vedette()
    {
        $products = Product::with('category:id,name')
            ->where('vedette', true)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nom' => $p->nom,
                'description' => $p->description,
                'stock' => $p->stock,
                'prix_unitaire' => $p->prix_unitaire,
                'prix_promo' => $p->prix_promo,
                'image' => $p->image,
                'vedette' => $p->vedette,
                'category_name' => $p->category->name ?? null,
            ]);

        return response()->json(['data' => $products], 200);
    }

    #[OA\Post(
        path: "/api/products/store",
        summary: "Créer un produit",
        tags: ["Products"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["category_id", "nom", "description", "stock", "prix_unitaire", "image"],
                    properties: [
                        new OA\Property(property: "category_id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "PC"),
                        new OA\Property(property: "description", type: "string", example: "PC authentique"),
                        new OA\Property(property: "stock", type: "integer", example: 20),
                        new OA\Property(property: "prix_unitaire", type: "float", example: 100000.00),
                        new OA\Property(property: "prix_promo", type: "float", example: 90000.00),
                        new OA\Property(property: "image", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Produit créé avec succès"),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function store(ProductRequest $request)
    {
        $product = new Product($request->only([
            'nom',
            'category_id',
            'description',
            'stock',
            'prix_unitaire',
            'prix_promo',
            'vedette'
        ]));

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('images', 'public');
        }

        try {
            $product->save();
            return response()->json(['message' => "Création effectuée", 'data' => $product], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur: " . $e], 400);
        }
    }

    #[OA\Put(
        path: "/api/products/update/{product}",
        summary: "Modifier un produit",
        tags: ["Products"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "product", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["category_id", "nom", "description", "stock", "prix_unitaire"],
                    properties: [
                        new OA\Property(property: "category_id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "PC"),
                        new OA\Property(property: "description", type: "string", example: "PC authentique"),
                        new OA\Property(property: "stock", type: "integer", example: 20),
                        new OA\Property(property: "prix_unitaire", type: "float", example: 100000.00),
                        new OA\Property(property: "prix_promo", type: "float", example: 90000.00),
                        new OA\Property(property: "image", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Produit modifié avec succès")
        ]
    )]
    public function update(ProductRequest $request, Product $product)
    {
        $product->fill($request->only(['nom', 'category_id', 'description', 'stock', 'prix_unitaire', 'prix_promo', 'vedette']));

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('images', 'public');
        }

        try {
            $product->save();
            return response()->json(['message' => "Mise à jour effectuée"], 201);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur: " . $e], 404);
        }
    }

    #[OA\Delete(
        path: "/api/products/destroy/{product}",
        summary: "Supprimer un produit",
        tags: ["Products"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "product", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Produit supprimé avec succès"),
            new OA\Response(response: 401, description: "Non autorisé")
        ]
    )]
    public function destroy(Request $request, Product $product)
    {
        /* if (!$request->user() || $request->user()->role->name !== "Admin") {
            return response()->json(['message'=>"Action impossible ! Vous n'êtes pas un administrateur"],401);
        }*/

            $check = $product->sale()->count() ;
            
            if ( $check > 0) {
                return response()->json([
                    "message"=>"Impossible de supprimer ce produit. Des Commandes lui sont liés"
                ] , 422) ;
            }
            try {
            $product->delete();
            return response()->json(['message' => "Produit supprimé avec succès"], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur: " . $e], 500);
        }
    }


    #[OA\Get(

        path: "/api/products/admin",
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
                                    new OA\Property(property: "id", type: "integer", example: 1,),
                                    new OA\Property(property: "nom", type: "string", example: "Montre Electronique",),
                                    new OA\Property(property: "description", type: "string", example: "Haute qualité. Peut durer jusqu'à 4h",),
                                    new OA\Property(property: "stock", type: "integer", example: 13,),
                                    new OA\Property(property: "prix_unitaire", type: "float", example: 1000.00,),
                                    new OA\Property(property: "prix_promo", type: "float", example: 800.00,),
                                    new OA\Property(property: "image", type: "string", example: "image.png",),
                                    new OA\Property(property: "vedette", type: "integer", example: 0,),
                                    new OA\Property(property: "category_name", type: "string", example: "Informatique",),
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )] 
    public function forAdmin()
    {
        try {
            $all_product = Product::with('category:id,name')->orderBy('created_at', 'desc')->get();
            $products = $all_product->map(function ($product) {
                //dd($product->category()->name);
            return ['id' => $product->id, 'category_id' => $product->category_id, 'nom' => $product->nom, 'description' => $product->description, 'stock' => $product->stock, 'prix_unitaire' => $product->prix_unitaire, 'prix_promo' => $product->prix_promo, 'image' => $product->image, 'vedette' => $product->vedette, 'category_name' => $product->category->name ?? null,];
            });
            return response()->json(['data' => $products], 200);
        } catch (Exception $e) {
            return response()->json(["message" => "Erreur interne au serveur !"], 500);
        }
    }
    public function detailForAdmin($id)
    {

        $product = Product::find($id);

        if (!$product) {
            return response()->json(["message" => "Produit introuvable !"], 404);
        }
        //$vente = Sale::with("product")->where("product_id", $id)->get();
        //dd($vente);
        $revenus = $product->prix_promo ? $product->prix_promo * $product->sale()->sum("quantity") : ($product->prix_unitaire * $product->sale()->sum("quantity")) ;
        return response()->json([
            "data"=>$product ,
            "category_name"=>$product->category->name ,
            "ventes"=>$product->sale()->sum("quantity") ,
            "revenus"=> $revenus ,
            ] , 200) ;
    }
}


// Définition du schéma Product pour Swagger
#[OA\Schema(
    schema: "Product",
    title: "Product",
    type: "object",
    required: ["nom", "category_id", "description", "stock", "prix_unitaire"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nom", type: "string", example: "Montre Electronique"),
        new OA\Property(property: "description", type: "string", example: "Haute qualité. Peut durer jusqu'à 4h"),
        new OA\Property(property: "stock", type: "integer", example: 13),
        new OA\Property(property: "prix_unitaire", type: "float", example: 1000.00),
        new OA\Property(property: "prix_promo", type: "float", example: 800.00),
        new OA\Property(property: "image", type: "string", example: "image.png"),
        new OA\Property(property: "vedette", type: "integer", example: 0),
        new OA\Property(property: "category_name", type: "string", example: "Informatique"),
    ]
)]
class ProductSchema {}
