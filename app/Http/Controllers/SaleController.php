<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Sale;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
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
        // 1. Validation manuelle
        $validator = Validator::make($request->all(), [
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ], [
            'products.required' => 'Vous devez fournir au moins un produit.',
            'products.*.product_id.exists' => 'Un produit sélectionné n’existe pas.',
            'products.*.quantity.min' => 'La quantité doit être d’au moins 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 2. Génération de la référence unique
            do {
                $reference_generate = now()->format('dmy') . random_int(100000, 999999);
            } while (Sale::where('reference', $reference_generate)->exists());

            $total = 0;
            $itemsToAttach = [];

            // 3. Optimisation : Récupérer tous les produits d'un coup (évite le N+1)
            $productIds = collect($request->products)->pluck('product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->products as $index => $item) {
                $product = $products->get($item['product_id']);

                // 4. Vérification du stock (Retourne 422 pour cohérence)
                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Stock insuffisant pour {$product->nom}",
                        'errors' => ["products.$index.quantity" => ["Disponible : {$product->stock}"]]
                    ], 422);
                }

                // Décrémenter le stock
                $product->decrement('stock', $item['quantity']);

                // Calcul du prix (promo ou unitaire)
                $prix = $product->prix_promo > 0 ? $product->prix_promo : $product->prix_unitaire;
                $total += $prix * $item['quantity'];

                $itemsToAttach[$item['product_id']] = ['quantity' => $item['quantity']];
            }

            // 5. Création de la vente
            $sale = Sale::create([
                'user_id' => $request->user()->id,
                'reference' => $reference_generate,
                'status' => 'en cours',
                'total' => $total,
                'date_commande' => now(),
            ]);

            // Liaison pivot
            $sale->products()->attach($itemsToAttach);

            DB::commit();

            //$admin = "gsagbo541@gmail.com";
            // Ou si vous avez juste un mail fixe :
            Notification::route('mail', 'admin@exemple.com')->notify(new NewOrderNotification($sale));

            /*if ($admin) {
                $admin->notify(new NewOrderNotification($sale));
            }*/
            return response()->json([
                'message' => 'Votre commande a été enregistrée avec succès !',
                'sale' => $sale->load('products')
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $reference)
    {
        $sale = Sale::where('reference', $reference)->first();
        if (!$sale) {
            return response()->json(['message' => 'Commande non trouvée'], 404);
        }

        $request->validate([
            'status' => 'required|in:en cours,annulee,expediee,remboursee',
        ], [
            'status.required' => 'Le statut est requis !',
            'status.in' => 'Le statut doit être en cours, annulée, expédiée ou remboursée !',
        ]);

        ///dd($request->all());
        try {
            $sale->status = $request->status;
            $sale->save();
            return response()->json(['message' => 'Statut de la commande mis à jour avec succès', 'sale' => $sale], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour du statut', 'error' => $e->getMessage()], 500);
        }
    }

    public function forAdmin()
    {
        try {
            $sales = Sale::with('products', 'user')->orderBy('date_commande', 'desc')->get();
            if ($sales->isEmpty()) {
                return response()->json(['message' => "Aucune commande enregistrée pour le moment"], 204);
            }
            return response()->json(['sales' => $sales], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur lors du chargement" . $e], 500);
        }
    }
    public function OrderDetail($reference)
    {
        try {
            $sales = Sale::with('products', 'user')->where('reference', $reference)->first();
            if (!$sales) {
                return response()->json(['message' => "Commande non trouvée"], 404);
            }
            return response()->json(['sale' => $sales], 200);
        } catch (Exception $e) {
            return response()->json(['message' => "Erreur lors du chargement" . $e], 500);
        }
    }
}
