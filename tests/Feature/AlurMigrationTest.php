<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AlurMigrationTest extends TestCase
{
    private bool $dibuat = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('simantap_alur_test_20261004', DB::connection()->getDatabaseName());
        $settings = config('database.connections.mysql');
        $settings['database'] = null;
        $settings['url'] = null;
        config(['database.connections.server_uji' => $settings]);
        DB::connection('server_uji')->statement('CREATE DATABASE `simantap_alur_migration_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->dibuat = true;
        config(['database.connections.mysql.database' => 'simantap_alur_migration_test']);
        DB::purge('mysql');
        foreach (explode(';', file_get_contents(database_path('schema/simantap.sql'))) as $sql) {
            if (trim($sql) !== '') {
                DB::unprepared($sql);
            }
        }
        $this->seed();
        DB::statement("ALTER TABLE pemeriksaan MODIFY status_persetujuan ENUM('belum_diajukan','menunggu','disetujui','ditolak','revisi') NOT NULL DEFAULT 'belum_diajukan', DROP COLUMN alasan_penggantian");
        DB::statement("ALTER TABLE pengajuan_dana MODIFY status_pengajuan ENUM('diajukan','revisi','ditolak','disetujui') NOT NULL DEFAULT 'diajukan'");
        DB::statement("ALTER TABLE laporan_kerusakan MODIFY status_laporan ENUM('masuk','diperiksa','disetujui','ditangani','selesai') NOT NULL DEFAULT 'masuk', DROP COLUMN alasan_penghentian");
    }

    protected function tearDown(): void
    {
        if ($this->dibuat) {
            DB::disconnect('mysql');
            DB::connection('server_uji')->statement('DROP DATABASE `simantap_alur_migration_test`');
        }
        parent::tearDown();
    }

    public function test_penolakan_dipetakan_tanpa_menghapus_catatan_atau_membuka_laporan_selesai(): void
    {
        DB::table('pemeriksaan')->where('pemeriksaan_id', 1)->update([
            'status_pemeriksaan' => 'selesai', 'status_persetujuan' => 'ditolak', 'catatan_koordinator' => 'Catatan keputusan lama',
        ]);
        DB::table('pengajuan_dana')->where('pengajuan_id', 2)->update(['status_pengajuan' => 'ditolak', 'catatan' => 'Nominal terlalu tinggi']);
        $id = DB::table('laporan_kerusakan')->insertGetId([
            'pelapor_id' => 1, 'inventaris_id' => 1, 'ruangan_id' => 1,
            'kerusakan' => 'Riwayat penutupan lama', 'status_laporan' => 'selesai', 'tanggal_ditutup' => '2026-10-01 12:00:00',
        ]);
        DB::table('pemeriksaan')->insert([
            'laporan_id' => $id, 'petugas_id' => 4, 'status_pemeriksaan' => 'selesai', 'status_persetujuan' => 'ditolak',
        ]);
        $jumlah = DB::table('pemeriksaan')->count();
        $migration = require database_path('migrations/revisi_alur_rekomendasi_dana.php');
        $migration->up();
        $migration->up();
        $this->assertDatabaseHas('pemeriksaan', ['pemeriksaan_id' => 1, 'status_persetujuan' => 'revisi', 'catatan_koordinator' => 'Catatan keputusan lama']);
        $this->assertDatabaseHas('pengajuan_dana', ['pengajuan_id' => 2, 'pengajuan_sebelumnya_id' => 1, 'status_pengajuan' => 'revisi', 'catatan' => 'Nominal terlalu tinggi']);
        $this->assertDatabaseHas('laporan_kerusakan', ['laporan_id' => $id, 'status_laporan' => 'selesai', 'tanggal_ditutup' => '2026-10-01 12:00:00']);
        $this->assertSame($jumlah, DB::table('pemeriksaan')->count());
        $this->assertTrue(Schema::hasColumn('pemeriksaan', 'alasan_penggantian'));
        $this->assertTrue(Schema::hasColumn('laporan_kerusakan', 'alasan_penghentian'));
        $this->assertSame(0, DB::table('pemeriksaan')->where('status_persetujuan', 'ditolak')->count());
        $this->assertSame(0, DB::table('pengajuan_dana')->where('status_pengajuan', 'ditolak')->count());
        $this->assertSame('revisi', DB::table('pemeriksaan')->where('laporan_id', $id)->value('status_persetujuan'));
        $this->assertFalse(\App\Services\AlurLaporan::bolehIsi(
            \App\Models\LaporanKerusakan::find($id), \App\Models\Pemeriksaan::where('laporan_id', $id)->first(), \App\Models\Pengguna::find(4)
        ));
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('pengajuan_dana')->where('pengajuan_id', 2)->update(['status_pengajuan' => 'ditolak']);
    }

    public function test_konflik_riwayat_dihentikan_sebelum_perubahan(): void
    {
        DB::table('pemeriksaan')->where('pemeriksaan_id', 7)->update(['status_persetujuan' => 'ditolak']);
        DB::table('pengajuan_dana')->where('pengajuan_id', 1)->update(['status_pengajuan' => 'ditolak']);
        $migration = require database_path('migrations/revisi_alur_rekomendasi_dana.php');
        $gagal = false;
        try {
            $migration->up();
        } catch (\RuntimeException $error) {
            $gagal = true;
            $this->assertStringContainsString('pemeriksaan #7', $error->getMessage());
            $this->assertStringContainsString('pengajuan dana #1', $error->getMessage());
        }
        $this->assertTrue($gagal);
        $this->assertFalse(Schema::hasColumn('pemeriksaan', 'alasan_penggantian'));
        $this->assertDatabaseHas('pemeriksaan', ['pemeriksaan_id' => 7, 'status_persetujuan' => 'ditolak']);
        $this->assertDatabaseHas('pengajuan_dana', ['pengajuan_id' => 1, 'status_pengajuan' => 'ditolak']);
    }

    public function test_penolakan_lama_dengan_pekerjaan_aktif_pada_pemeriksaan_lain_tidak_diubah(): void
    {
        DB::table('pengajuan_dana')->where('pengajuan_id', 2)->update(['status_pengajuan' => 'ditolak']);
        DB::table('pemeriksaan')->where('pemeriksaan_id', 1)->update(['laporan_id' => 3]);
        $migration = require database_path('migrations/revisi_alur_rekomendasi_dana.php');
        try {
            $migration->up();
            $this->fail('Migrasi harus menolak konflik pekerjaan aktif.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('pengajuan dana #2', $error->getMessage());
        }
        $this->assertFalse(Schema::hasColumn('pemeriksaan', 'alasan_penggantian'));
        $this->assertDatabaseHas('pengajuan_dana', ['pengajuan_id' => 2, 'status_pengajuan' => 'ditolak']);
    }

}
