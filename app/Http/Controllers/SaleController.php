<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Sale;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;
use Symfony\Component\Mime\Message;

use function PHPUnit\Framework\isEmpty;
use function Symfony\Component\Clock\now;

class SaleController extends Controller
{

    //Lecture des ventes
    public function index (Request $request) {
        $sales = Sale::with('products')->where('user_id' , $request->user()->id)->get() ;
        //dd($sales);
        try {
            if (count($sales)>0){

                return response()->json([
                    
                    "sales" =>$sales
                    
                ] ,200) ;
            } else {
                return response()->json([
                    'message'=>"Vous n'avez aucune commande enregistrées" ,
                ] , 204) ;
            }
        }catch (Exception $e) {
            return response()->json([
                'message'=>"Erreur lors du chargement" ,
            ] , 500) ;
        }
    }



    public function annullee (Request $request) {
        $sales = Sale::with('products')->where('user_id' , $request->user()->id)->where('status','annullee')->get() ;
        //dd($sales);
        try {
            if (count($sales)>0){

                return response()->json([
                    
                    "Sales" =>$sales
                    
                ] ,200) ;
            } else {
                return response()->json([
                    'message'=>"Aucune commande annullée !" ,
                ] , 200) ;
            }
        }catch (Exception $e) {
            return response()->json([
                'message'=>"Erreur lors du chargement" ,
            ] , 500) ;
        }
    }

    //commande expediee
    public function expediee (Request $request) {
        $sales = Sale::with('products')->where('user_id' , $request->user()->id)->where('status','expediee')->get() ;
        //dd($sales);
        try {
            if (count($sales)>0){

                return response()->json([
                    
                    "Sales" =>$sales
                    
                ] ,200) ;
            } else {
                return response()->json([
                    'message'=>"Aucune commande expediée" ,
                ] , 200) ;
            }
        }catch (Exception $e) {
            return response()->json([
                'message'=>"Erreur lors du chargement" ,
            ] , 500) ;
        }
    }
    //commande en cours
    public function en_cours (Request $request) {
        $sales = Sale::with('products')->where('user_id' , $request->user()->id)->where('status','en cours')->get() ;
        //dd($sales);
        try {
            if (count($sales)>0){

                return response()->json([
                    
                    "Sales" =>$sales
                    
                ] ,200) ;
            } else {
                return response()->json([
                    'message'=>"Aucune commande enregistrées pour le moment" ,
                ] , 200) ;
            }
        }catch (Exception $e) {
            return response()->json([
                'message'=>"Erreur lors du chargement" ,
            ] , 500) ;
        }
    }


    /**
     * Crée une nouvelle vente avec plusieurs produits.
     */


    /*public function store(SaleRequest $request)
    {
        try {
            
            
            // Référence
            $datePart = now()->format('dmy');
            $randomPart = random_int(100000, 999999);
            $reference_generate = $datePart . $randomPart;

            $reference_verify = Sale::where('reference',"=",$reference_generate) ;

            while (count($reference_verify) > 0) {
                $datePart = now()->format('dmy');
                $randomPart = random_int(100000, 999999);
                $reference_generate = $datePart . $randomPart;
                $reference_verify = Sale::where('reference',"=",$reference_generate) ;
            }

            
            $total = 0 ;

            foreach ($request->products as $item) {
                try {
                    $p_prix = Product::find($item['product_id']) ;
                    $total += ($p_prix->prix_unitaire * $item['quantity']);

                } catch (Exception $e) {
                    return response()->json([
                        'message'=>"Erreur lors de l'enregistrement de la commande " ,
                    ] , 500) ;
                }
            }
            // Création de la vente principale
            $sale = Sale::create([
                'user_id'   => $request->user_id,
                'reference' => $reference_generate,
                'status' => 'en cours',
                'total' => $total,
                'date_commande' => now(),

            ]);

            // Insertion des produits associés
            foreach ($request->products as $item) {

                //  Vérification que le produit existe dans la table `products`
                $product = Product::find($item['product_id']);
                if (!$product) {
                    return response()->json([
                        'message'=>"Produit introuvable" ,
                    ] , 404) ;
                }else {

                //  Lier le produit à la vente (table pivot)
                $sale->products()->attach($item['product_id'], [
                    'quantity' => $item['quantity']
                ]); 
            }
            }

            DB::commit();

            return response()->json([
                'message'   => 'Votre commande a été enregistrée avec succès !',
                'reference' => $reference_generate,
                'sale'      => $sale->load('products')
            ] , 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erreur lors de la création de la vente ',
                'error'   => $e->getMessage()
            ],500);
        }
    }*/

        public function store(SaleRequest $request)
{
    
    // --- 1. Démarrage de la transaction ---
    DB::beginTransaction();

    try {
        // --- 2. Vérification de l'unicité de la Référence ---
        do {
            $datePart = now()->format('dmy');
            $randomPart = random_int(100000, 999999);
            $reference_generate = $datePart . $randomPart;
        } while (Sale::where('reference', $reference_generate)->exists()); // CORRECTION: Vérifie dans la table Sale

        $total = 20;
        $itemsToAttach = [];
        
        // --- 3. Vérification des produits et Calcul du Total (Sécurisé) ---
        foreach ($request->products as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                DB::rollBack();
                return response()->json([
                    'message' => "Produit introuvable avec l'ID: " . $item['product_id'],
                ], 404);
            }

            // Calcul du total sécurisé (utilise le prix de la DB)
            if ($product->prix_promo >0) {
                $total += ($product->prix_promo * $item['quantity']);

            } else {
                $total += ($product->prix_unitaire * $item['quantity']);

            }

            // Préparation des données pour l'attachement (une seule boucle)
            $itemsToAttach[$item['product_id']] = [
                'quantity' => $item['quantity']
                // Si vous avez besoin d'enregistrer le prix unitaire d'achat au moment de la commande, ajoutez ici :
                // 'price_at_sale' => $product->prix_unitaire
            ];
        }

        // --- 4. Création de la vente principale ---
        //dd($request->user()->id) ;
        $sale = Sale::create([
            'user_id'       => $request->user()->id,
            'reference'     => $reference_generate,
            'status'        => 'en cours',
            'total'         => $total,
            'date_commande' => now(), // Utilise now() pour un horodatage complet
        ]);

        // --- 5. Insertion des produits associés (une seule fois) ---
        $sale->products()->attach($itemsToAttach);
        
        DB::commit();

        return response()->json([
            'message'   => 'Votre commande a été enregistrée avec succès !',
            'reference' => $reference_generate,
            'sale'      => $sale->load('products')
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Erreur lors de la création de la commande.',
            'error'   => $e->getMessage()
        ], 500);
    }
}
}