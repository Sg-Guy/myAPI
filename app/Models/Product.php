<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory ;
    protected $table = 'products';


    protected $fillable = [
        'nom' ,
        'category_id' ,
        'description' ,
        'quantite',
        'stock' ,
        'prix_unitaire' ,
        'prix_promo' ,
        'image' ,
    ] ;

    //Contrainte vers la table categories
    public function category () {
        return $this->belongsTo(Category::class, "category_id") ;
    }

    //Contrainte vers la table sales
    public function sale() {
        return $this->belongsToMany(Sale::class)
                    ->withPivot('quantity', 'ref') ;
    }

    public function favourite () {
        return $this->belongsTo(Favourite::class);
    }

    
}
