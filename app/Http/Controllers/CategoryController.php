<?php

namespace App\Http\Controllers;

use App\Http\Requests\categorieRequest;
use App\Models\Category;
use App\Models\Product;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Catégories",
    description: "Gestion des catégories"
)]
class CategoryController extends Controller
{
    #[OA\Post(
        path: "/api/categories",
        summary: "Créer une catégorie",
        description: "Création d'une nouvelle catégorie",
        security: [["sanctum" => []]],
        tags: ["Catégories"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "Robotique",
                        description: "Nom unique de la catégorie"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "La catégorie a bien été créée"
            ),
            new OA\Response(
                response: 409,
                description: "Cette catégorie existe déjà"
            ),
            new OA\Response(
                response: 500,
                description: "Une erreur est survenue ."
            )
        ]
    )]
    public function store(categorieRequest $request)
    {
        $exists = Category::where('name', $request->name)->exists();

        if ($exists) {
            return response()->json([
                'message' => "Cette catégorie existe déjà"
            ], 409);
        }

        try {
            $category = Category::create([
                'name' => $request->name
            ]);

            return response()->json([
                'message' => "La catégorie a bien été créée",
                'data' => $category
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Une erreur est survenue.",
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/categories",
        summary: "Lister toutes les catégories",
        description: "Retourne toutes les catégories",
        //security: [["sanctum" => []]],
        tags: ["Catégories"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste récupérée avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Requête traitée avec succès"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Informatique"),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur"
            )
        ]
    )]
    public function index()
    {
        $categories = Category::with('product')->select('id', 'name' , 'description' , 'created_at' , 'updated_at')->get();
        
        return response()->json([
            'message' => "Requête traitée avec succès",
            'data' => $categories
        ], 200);
    }

 

    #[OA\Put(
        path: "/api/categories/{category}",
        summary: "Modifier une catégorie",
        description: "Modifier le nom d'une catégorie",
        security: [["sanctum" => []]],
        tags: ["Catégories"],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "Robotique Industrielle"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Catégorie mise à jour avec succès"
            ),
            new OA\Response(
                response: 409,
                description: "Cette catégorie existe déjà"
            ),
            new OA\Response(
                response: 404,
                description: "Catégorie non trouvée"
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur"
            )
        ]
    )]
    public function update(categorieRequest $request, Category $category)
    {
        $exists = Category::where('name', $request->name)
            ->where('id', '!=', $category->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => "Cette catégorie existe déjà"
            ], 409);
        }
        
        try {

            $category->update([
                'name' => $request->name
            ]);

            return response()->json([
                'message' => "Catégorie mise à jour avec succès",
                'data' => $category
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la mise à jour"
            ], 500);
        }
    }


    // Methode delete
    #[OA\Delete(
        path: "/api/categories/{category}",
        summary: "Supprimer une catégorie",
        description: "Supprime une catégorie si elle existe",
        security: [["sanctum" => []]],
        tags: ["Catégories"],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Catégorie supprimée avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Catégorie non trouvée"
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur"
            )
        ]
    )]
    public function destroy(Category $category)
    {
        $products = Product::where('category_id', '=', $category->id)->get();
        if (count($products) > 0) {
            return response()->json([
                'message' => "Action Impossible . Des Articles sont liés à cette catégorie ! "
            ], 422);
        }

        
     

        if ($category) {
            try {
                $category->delete();

                return response()->json([
                    'message' => "Catégorie supprimée avec succès"
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'message' => "Erreur lors de la suppression"
                ], 500);
            }
        }


    }
}
