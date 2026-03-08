<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashBoardController extends Controller
{
    public function dashboard()
    {
        $revenu = Sale::sum("total");
        $commandes = Sale::count('reference');
        $products = Product::count('id');
        $top_products = Product::withCount('sale')->orderByDesc('sale_count')->limit(4)->get(); // agit sur la table pivot
        $commandes_mois = Sale::selectRaw('MONTHNAME(created_at) as mois')
            ->selectRaw('COUNT(*) as total')
            ->whereYear('created_at', 2025)
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
