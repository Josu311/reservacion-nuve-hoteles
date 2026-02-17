<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Quitar la restricción UNIQUE
            $table->dropUnique(['provider_folio']);

            // Opcional: dejar un índice normal para consultas por folio
            $table->index('provider_folio');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Revertir: quitar el índice normal y volver a UNIQUE
            $table->dropIndex(['provider_folio']);
            $table->unique('provider_folio');
        });
    }
};
