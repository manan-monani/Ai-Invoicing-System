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
        Schema::table('items', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('name');
            $table->text('description')->nullable()->after('sku');
            $table->string('unit', 20)->default('pcs')->after('description');
            $table->decimal('cost_price', 12, 2)->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['sku', 'description', 'unit', 'cost_price']);
        });
    }
};
