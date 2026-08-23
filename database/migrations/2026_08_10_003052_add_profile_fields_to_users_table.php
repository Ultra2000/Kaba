<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('city')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('city');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->enum('role', ['user', 'pro', 'admin'])->default('user')->after('avatar_path');
            $table->boolean('is_verified')->default(false)->after('role');
            $table->decimal('rating_avg', 2, 1)->default(0)->after('is_verified');
            $table->unsignedInteger('sales_count')->default(0)->after('rating_avg');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'city', 'bio', 'avatar_path', 'role', 'is_verified', 'rating_avg', 'sales_count']);
        });
    }
};
