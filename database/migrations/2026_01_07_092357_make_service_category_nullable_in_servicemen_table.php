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
        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropForeign(['service_category']);
        });
        
        \DB::statement('ALTER TABLE `servicemen` MODIFY `service_category` BIGINT UNSIGNED NULL');
        
        Schema::table('servicemen', function (Blueprint $table) {
            $table->foreign('service_category')->references('id')->on('service_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropForeign(['service_category']);
        });
        
        \DB::statement('ALTER TABLE `servicemen` MODIFY `service_category` BIGINT UNSIGNED NOT NULL');
        
        Schema::table('servicemen', function (Blueprint $table) {
            $table->foreign('service_category')->references('id')->on('service_categories')->onDelete('cascade');
        });
    }
};
