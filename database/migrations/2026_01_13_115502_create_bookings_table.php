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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('booking_type'); // service, puja
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('puja_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('serviceman_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('brahman_id')->nullable()->constrained('brahmans')->onDelete('cascade');
            $table->dateTime('booking_date');
            $table->string('booking_time');
            $table->text('address');
            $table->string('mobile_number');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->enum('payment_method', ['cod'])->default('cod');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
