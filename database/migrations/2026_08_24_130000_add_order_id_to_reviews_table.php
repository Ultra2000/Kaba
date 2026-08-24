<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Avis laissé suite à une vente précise (demande remise).
            $table->foreignId('order_id')->nullable()->after('listing_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
