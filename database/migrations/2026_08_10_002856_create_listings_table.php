<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('language')->default('Français');
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->enum('condition', ['comme_neuf', 'tres_bon', 'bon', 'moyen'])->default('bon');
            $table->text('description')->nullable();
            $table->unsignedInteger('price')->default(0);      // FCFA (0 si don/échange/recherche)
            $table->enum('type', ['vente', 'don', 'echange', 'recherche'])->default('vente');
            $table->string('wants')->nullable();               // échange : livre(s) recherché(s)
            $table->unsignedInteger('budget')->nullable();     // recherche : budget max
            $table->string('city');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->enum('status', ['active', 'pending', 'sold', 'hidden'])->default('active');
            $table->unsignedInteger('views')->default(0);
            $table->decimal('rating', 2, 1)->nullable(); // note de démo (0.0–5.0)
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('category_id');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
