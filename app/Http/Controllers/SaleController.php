<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Sale;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Sales",
    description: "Gestion des ventes et commandes"
)]
class SaleController extends Controller
{
    #[OA\Get(
        path: "/api/sales",
        description: "Récupérer toutes les ventes de l'utilisateur connecté",
        tags: ["Sales"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des ventes récupérée"),
            new OA\Response(response: 204, description: "Aucune commande enregistrée"),
            new OA\Response(response: 500, description: "Erreur serveur")
        ]
    )]
    public function index(Request $request)
    {
        $sales = Sale::with('products')->where('user_id', $request->user()->id)->get();
        try {
            if ($sales->isEmpty()) {
                return response()->json(['message' => "Vous n'avez aucune commande enregistrée"], 204);
            }
            return response()->json(['sales' => $sales], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur lors du chargement"], 500);
        }
    }

    #[OA\Get(
        path: "/api/sales/annullee",
        description: "Récupérer les ventes annulées",
        tags: ["Sales"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des ventes annulées récupérée"),
            new OA\Response(response: 201, description: "Aucune commande annulée"),
            new OA\Response(response: 500, description: "Erreur serveur")
        ]
    )]
    public function annullee(Request $request)
    {
        $sales = Sale::with('products')->where('user_id', $request->user()->id)->where('status', 'annullee')->get();
        try {
            if ($sales->isEmpty()) {
                return response()->json(['message' => "Aucune commande annulée !"], 201);
            }
            return response()->json(['sales' => $sales], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur lors du chargement"], 500);
        }
    }

    #[OA\Get(
        path: "/api/sales/expediee",
        description: "Récupérer les ventes expédiées",
        tags: ["Sales"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des ventes expédiées récupérée"),
            new OA\Response(response: 204, description: "Aucune commande expédiée"),
            new OA\Response(response: 500, description: "Erreur serveur")
        ]
    )]
    public function expediee(Request $request)
    {
        $sales = Sale::with('products')->where('user_id', $request->user()->id)->where('status', 'expediee')->get();
        try {
            if ($sales->isEmpty()) {
                return response()->json(['message' => "Aucune commande expédiée"], 204);
            }
            return response()->json(['sales' => $sales], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur lors du chargement"], 500);
        }
    }

    #[OA\Get(
        path: "/api/sales/en_cours",
        description: "Récupérer les ventes en cours",
        tags: ["Sales"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des ventes en cours récupérée"),
            new OA\Response(response: 204, description: "Aucune commande en cours"),
            new OA\Response(response: 500, description: "Erreur serveur")
        ]
    )]
    public function en_cours(Request $request)
    {
        $sales = Sale::with('products')->where('user_id', $request->user()->id)->where('status', 'en cours')->get();
        try {
            if ($sales->isEmpty()) {
                return response()->json(['message' => "Aucune commande enregistrée pour le moment"], 204);
            }
            return response()->json(['sales' => $sales], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur lors du chargement"], 500);
        }
    }

    #[OA\Post(
        path: "/api/sales/store",
        description: "Créer une nouvelle vente et décrémenter le stock des produits",
        tags: ["Sales"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["products"],
                    properties: [
                        new OA\Property(
                            property: "products",
                            type: "array",
                            items: new OA\Items(
                                required: ["product_id", "quantity"],
                                properties: [
                                    new OA\Property(property: "product_id", type: "integer", example: 1),
                                    new OA\Property(property: "quantity", type: "integer", example: 2)
                                ]
                            )
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Commande créée avec succès"),
            new OA\Response(response: 404, description: "Produit introuvable"),
            new OA\Response(response: 500, description: "Erreur serveur")
        ]
    )]
    public function store(SaleRequest $request)
    {
        DB::beginTransaction();
        try {
            do {
                $datePart = now()->format('dmy');
                $randomPart = random_int(100000, 999999);
                $reference_generate = $datePart . $randomPart;
            } while (Sale::where('reference', $reference_generate)->exists());

            $total = 0;
            $itemsToAttach = [];

            foreach ($request->products as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    DB::rollBack();
                    return response()->json(['message' => "Produit introuvable ID: " . $item['product_id']], 404);
                }

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Stock insuffisant pour le produit ID: " . $item['product_id']
                    ], 400);
                }

                // Décrémenter le stock
                $product->stock -= $item['quantity'];
                $product->save();

                $prix = $product->prix_promo > 0 ? $product->prix_promo : $product->prix_unitaire;
                $total += $prix * $item['quantity'];

                $itemsToAttach[$item['product_id']] = ['quantity' => $item['quantity']];
            }

            $sale = Sale::create([
                'user_id' => $request->user()->id,
                'reference' => $reference_generate,
                'status' => 'en cours',
                'total' => $total,
                'date_commande' => now(),
            ]);

            $sale->products()->attach($itemsToAttach);

            DB::commit();

            return response()->json([
                'message' => 'Votre commande a été enregistrée avec succès !',
                'reference' => $reference_generate,
                'sale' => $sale->load('products')
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la création de la commande', 'error' => $e->getMessage()], 500);
        }
    }
}