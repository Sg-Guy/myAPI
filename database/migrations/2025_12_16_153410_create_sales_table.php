<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            // Référence unique sous forme de chaîne alphanumérique
            $table->string('reference')->unique();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // date
             $table->date('date_commande') ;

             //status
             $table->enum('status' , ['en cours ' , 'expediee' , 'annulee' ,'remboursee'])->default('en cours') ;

             //total
              $table->double('total') ;
             $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};