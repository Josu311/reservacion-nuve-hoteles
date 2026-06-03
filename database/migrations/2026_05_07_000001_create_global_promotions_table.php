<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->dateTime('booking_starts_at')->nullable();
            $table->dateTime('booking_ends_at')->nullable();
            $table->date('stay_starts_at')->nullable();
            $table->date('stay_ends_at')->nullable();
            $table->string('hotel_code', 40)->nullable()->index();
            $table->string('room_type_code', 10)->nullable()->index();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_promotions');
    }
};
