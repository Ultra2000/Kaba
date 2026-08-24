<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Offre de prix proposée par l'acheteur (uniquement pour les ventes).
            // Null = prix affiché accepté tel quel.
            $table->unsignedInteger('offered_price')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('offered_price');
        });
    }
};
