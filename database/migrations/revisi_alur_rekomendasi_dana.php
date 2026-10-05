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
            throw new RuntimeException('Penyesuaian ENUM ini memerlukan MySQL atau MariaDB.');
        }

        $konflik = [];
        foreach (DB::table('pemeriksaan')->where('status_persetujuan', 'ditolak')->get() as $p) {
            $laporan = DB::table('laporan_kerusakan')->where('laporan_id', $p->laporan_id)->first();
            $adaLanjutan = DB::table('pemeriksaan')->where('laporan_id', $p->laporan_id)
                ->where('pemeriksaan_id', '>', $p->pemeriksaan_id)
                ->where('status_pemeriksaan', '<>', 'dibatalkan')->exists();
            $adaDana = DB::table('pengajuan_dana')->where('pemeriksaan_id', $p->pemeriksaan_id)->exists();
            $adaTindakan = DB::table('penindaklanjutan')->where('pemeriksaan_id', $p->pemeriksaan_id)->exists();
            $adaPekerjaanAktif = DB::table('pemeriksaan')->where('laporan_id', $p->laporan_id)
                ->whereIn('status_pemeriksaan', ['ditugaskan', 'berjalan'])->exists()
                || DB::table('penindaklanjutan')->whereIn('pemeriksaan_id', function ($query) use ($p) {
                    $query->select('pemeriksaan_id')->from('pemeriksaan')->where('laporan_id', $p->laporan_id);
                })->whereIn('status_tindakan', ['ditugaskan', 'berjalan', 'terkendala'])->exists();
            if ($adaLanjutan || $adaDana || $adaTindakan || $adaPekerjaanAktif
                || $p->status_pemeriksaan !== 'selesai'
                || ! in_array($laporan->status_laporan, ['diperiksa', 'selesai', 'dihentikan'])) {
                $konflik[] = 'pemeriksaan #'.$p->pemeriksaan_id;
            }
        }

        foreach (DB::table('pengajuan_dana')->where('status_pengajuan', 'ditolak')->get() as $d) {
            $p = DB::table('pemeriksaan')->where('pemeriksaan_id', $d->pemeriksaan_id)->first();
            $laporan = DB::table('laporan_kerusakan')->where('laporan_id', $p->laporan_id)->first();
            $adaVersi = DB::table('pengajuan_dana')->where('pengajuan_sebelumnya_id', $d->pengajuan_id)->exists();
            $adaTindakan = DB::table('penindaklanjutan')->where('pemeriksaan_id', $p->pemeriksaan_id)->exists();
            $adaPekerjaanAktif = DB::table('pemeriksaan')->where('laporan_id', $p->laporan_id)
                ->whereIn('status_pemeriksaan', ['ditugaskan', 'berjalan'])->exists()
                || DB::table('penindaklanjutan')->whereIn('pemeriksaan_id', function ($query) use ($p) {
                    $query->select('pemeriksaan_id')->from('pemeriksaan')->where('laporan_id', $p->laporan_id);
                })->whereIn('status_tindakan', ['ditugaskan', 'berjalan', 'terkendala'])->exists();
            $adaPemeriksaanBaru = DB::table('pemeriksaan')->where('laporan_id', $p->laporan_id)
                ->where('pemeriksaan_id', '>', $p->pemeriksaan_id)
                ->where('status_pemeriksaan', '<>', 'dibatalkan')->exists();
            if ($adaVersi || $adaTindakan || $adaPemeriksaanBaru || $adaPekerjaanAktif
                || $p->status_pemeriksaan !== 'selesai' || $p->status_persetujuan !== 'disetujui'
                || ! in_array($laporan->status_laporan, ['disetujui', 'selesai', 'dihentikan'])) {
                $konflik[] = 'pengajuan dana #'.$d->pengajuan_id;
            }
        }

        if ($konflik !== []) {
            throw new RuntimeException('Migrasi belum dijalankan. Periksa konflik penolakan lama: '.implode(', ', $konflik).'. Catatan dan data belum diubah.');
        }

        if (! Schema::hasColumn('pemeriksaan', 'alasan_penggantian')) {
            Schema::table('pemeriksaan', function (Blueprint $table) {
                $table->text('alasan_penggantian')->nullable();
            });
        }
        if (! Schema::hasColumn('laporan_kerusakan', 'alasan_penghentian')) {
            Schema::table('laporan_kerusakan', function (Blueprint $table) {
                $table->text('alasan_penghentian')->nullable();
            });
        }

        DB::transaction(function () {
            DB::table('pemeriksaan')->where('status_persetujuan', 'ditolak')->update(['status_persetujuan' => 'revisi']);
            DB::table('pengajuan_dana')->where('status_pengajuan', 'ditolak')->update(['status_pengajuan' => 'revisi']);
        });

        DB::statement("ALTER TABLE laporan_kerusakan MODIFY status_laporan ENUM('masuk','diperiksa','disetujui','ditangani','selesai','dihentikan') NOT NULL DEFAULT 'masuk'");
        DB::statement("ALTER TABLE pemeriksaan MODIFY status_persetujuan ENUM('belum_diajukan','menunggu','disetujui','revisi','dihentikan') NOT NULL DEFAULT 'belum_diajukan'");
        DB::statement("ALTER TABLE pengajuan_dana MODIFY status_pengajuan ENUM('diajukan','revisi','disetujui') NOT NULL DEFAULT 'diajukan'");
    }

    public function down(): void
    {
        throw new RuntimeException('Penghentian dan penolakan lama tidak dapat dipulihkan lewat rollback otomatis. Gunakan cadangan sebelum migrasi.');
    }
};
