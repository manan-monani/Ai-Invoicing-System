<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('has_discount')->default(false)->after('is_active');
            $table->decimal('discount_amount', 12, 2)->nullable()->after('has_discount');
            $table->boolean('discount_is_percentage')->default(false)->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['has_discount', 'discount_amount', 'discount_is_percentage']);
        });
    }
};
