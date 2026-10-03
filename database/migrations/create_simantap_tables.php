<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (explode(';', file_get_contents(database_path('schema/simantap.sql'))) as $statement) {
            if (trim($statement) !== '') {
                DB::unprepared($statement);
            }
        }
    }

    public function down(): void
    {
        foreach (['konfirmasi_hasil', 'penindaklanjutan', 'pengajuan_dana', 'pemeriksaan', 'laporan_kerusakan', 'inventaris', 'ruangan', 'pengguna'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
