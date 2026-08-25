<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supprimer une annonce ne doit pas effacer l'historique des transactions :
     * la ligne de commande est conservée (l'interface affiche « Annonce supprimée »).
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
            $table->unsignedBigInteger('listing_id')->nullable()->change();
            $table->foreign('listing_id')->references('id')->on('listings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
            $table->unsignedBigInteger('listing_id')->nullable(false)->change();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
        });
    }
};
