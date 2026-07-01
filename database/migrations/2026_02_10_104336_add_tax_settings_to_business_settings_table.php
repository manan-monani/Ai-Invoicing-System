<?php

use App\Models\BusinessSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {});

        DB::table('business_settings')->updateOrInsert(
            ['key' => BusinessSetting::TAX_ENABLED],
            ['value' => '0', 'type' => 'boolean']
        );

        DB::table('business_settings')->updateOrInsert(
            ['key' => BusinessSetting::TAX_MODE],
            ['value' => 'none', 'type' => 'enum']
        );

        DB::table('business_settings')->updateOrInsert(
            ['key' => BusinessSetting::DEFAULT_TAX_ID],
            ['value' => null, 'type' => 'relation']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {});

        DB::table('business_settings')
            ->whereIn('key', [
                BusinessSetting::TAX_ENABLED,
                BusinessSetting::TAX_MODE,
                BusinessSetting::DEFAULT_TAX_ID,
            ])
            ->delete();
    }
};
