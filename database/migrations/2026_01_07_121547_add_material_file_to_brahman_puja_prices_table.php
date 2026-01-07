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
        Schema::table('brahman_puja_prices', function (Blueprint $table) {
            $table->string('material_file')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brahman_puja_prices', function (Blueprint $table) {
            $table->dropColumn('material_file');
        });
    }
};
