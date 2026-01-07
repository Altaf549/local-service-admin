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
            $table->string('email')->unique()->nullable()->after('name');
            $table->string('mobile_number')->nullable()->after('email');
            $table->string('password')->nullable()->after('mobile_number');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('availability_status');
            $table->string('government_id')->nullable()->after('status');
            $table->string('id_proof_image')->nullable()->after('government_id');
            $table->text('address')->nullable()->after('id_proof_image');
            $table->string('profile_photo')->nullable()->after('address');
            $table->json('achievements')->nullable()->after('profile_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropColumn(['email', 'mobile_number', 'password', 'status', 'government_id', 'id_proof_image', 'address', 'profile_photo', 'achievements']);
        });
    }
};
