<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serviceman_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serviceman_id')->constrained('servicemen')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('achieved_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serviceman_achievements');
    }
};
