<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('inventaris', 'kode_inventaris')) {
            Schema::table('inventaris', function (Blueprint $table) {
                $table->dropColumn('kode_inventaris');
            });
        }

        Schema::dropIfExists('nomor_inventaris');
    }

    public function down(): void
    {
        throw new RuntimeException('Kode inventaris lama tidak dapat dipulihkan lewat rollback. Gunakan cadangan database sebelum perubahan ini.');
    }
};
