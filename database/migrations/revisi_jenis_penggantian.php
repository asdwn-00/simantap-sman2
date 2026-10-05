<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Penyesuaian jenis penggantian memerlukan MySQL atau MariaDB.');
        }

        if (! Schema::hasColumn('pemeriksaan', 'jenis_penggantian')) {
            Schema::table('pemeriksaan', function (Blueprint $table) {
                $table->enum('jenis_penggantian', ['unit', 'sparepart'])->nullable();
            });
        }

        DB::table('pemeriksaan')->where('rekomendasi', 'penggantian')
            ->whereNull('jenis_penggantian')->update(['jenis_penggantian' => 'unit']);

        $adaAturan = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'pemeriksaan')
            ->where('CONSTRAINT_NAME', 'ck_pemeriksaan_jenis')
            ->exists();

        if (! $adaAturan) {
            DB::statement("ALTER TABLE pemeriksaan ADD CONSTRAINT ck_pemeriksaan_jenis CHECK (((rekomendasi IS NULL AND jenis_penggantian IS NULL) OR (rekomendasi = 'perbaikan' AND jenis_penggantian IS NULL) OR (rekomendasi = 'penggantian' AND (jenis_penggantian = 'unit' OR (jenis_penggantian = 'sparepart' AND sumber_pengganti = 'pengadaan')))) IS TRUE)");
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Jenis penggantian tidak dihapus otomatis agar riwayat sparepart tetap tersimpan. Gunakan cadangan sebelum migrasi.');
    }
};
