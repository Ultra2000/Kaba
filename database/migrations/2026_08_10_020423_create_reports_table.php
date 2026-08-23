<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('reportable'); // reportable_type + reportable_id (Listing ou User)
            $table->enum('reason', ['faux_livre', 'arnaque', 'prix_abusif', 'offensant']);
            $table->text('details')->nullable();
            $table->enum('status', ['open', 'resolved', 'dismissed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
