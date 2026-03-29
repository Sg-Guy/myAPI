<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\FuncCall;

class Sale extends Model
{
    protected $fillable = [
        'reference' ,
        'user_id' ,
        'date_commande' ,
        'status' ,
        'total' ,
        'product_id' ,
        'sale_id',
        'quantity'
    ] ;

   public function products()
    {
        return $this->belongsToMany(Product::class, 'product_sale')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function user (){
        return $this->belongsTo(User::class);
    }
}


