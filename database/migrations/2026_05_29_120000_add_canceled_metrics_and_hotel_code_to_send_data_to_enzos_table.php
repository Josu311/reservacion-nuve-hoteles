<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('send_data_to_enzos', function (Blueprint $table) {
            $table->string('hotel_code', 40)->nullable()->after('id')->index();
            $table->unsignedInteger('canceled_reservations_count')->default(0)->after('pending_reservations_mount');
            $table->unsignedBigInteger('canceled_reservations_mount')->default(0)->after('canceled_reservations_count');
        });
    }

    public function down(): void
    {
        Schema::table('send_data_to_enzos', function (Blueprint $table) {
            $table->dropIndex(['hotel_code']);
            $table->dropColumn([
                'hotel_code',
                'canceled_reservations_count',
                'canceled_reservations_mount',
            ]);
        });
    }
};
