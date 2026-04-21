<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('hotel_code', 40)
                ->default('torreon')
                ->after('user_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['hotel_code']);
            $table->dropColumn('hotel_code');
        });
    }
};
