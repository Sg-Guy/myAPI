<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

use function Symfony\Component\Clock\now;

class DashBoardController extends Controller
{
    public function dashboard()
    {
        $annee = Carbon::now()->year; 
        $revenu = Sale::sum("total");
        $commandes = Sale::count('reference');
        $products = Product::count('id');
        $top_products = Product::withCount('sale')->orderByDesc('sale_count')->limit(4)->get(); // agit sur la table pivot
        $commandes_mois = Sale::selectRaw('MONTHNAME(created_at) as mois')
            ->selectRaw('COUNT(*) as total')
            ->whereYear('created_at', $annee)
            ->groupByRaw('MONTH(created_at),MONTHNAME(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();
        return response()->json([
            "revenu" => $revenu,
            "commandes" => $commandes,
            "products" => $products,
            "commandes_mois" => $commandes_mois,
            "top_products" => $top_products,
        ]);
    }
}
