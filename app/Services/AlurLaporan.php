<?php

namespace App\Services;

use App\Models\LaporanKerusakan;
use App\Models\Pemeriksaan;
use App\Models\Pengguna;
use App\Models\PengajuanDana;

class AlurLaporan
{
    public static function pemeriksaan(LaporanKerusakan $laporan)
    {
        return $laporan->pemeriksaan->where('status_pemeriksaan', '!=', 'dibatalkan')->sortByDesc('pemeriksaan_id')->first();
    }

    public static function tindakan(LaporanKerusakan $laporan)
    {
        $p = self::pemeriksaan($laporan);

        return $p ? $laporan->penindaklanjutan->where('pemeriksaan_id', $p->pemeriksaan_id)->where('status_tindakan', '!=', 'dibatalkan')->sortByDesc('penugasan_id')->first() : null;
    }

    public static function adaPekerjaanAktif(LaporanKerusakan $laporan): bool
    {
        return $laporan->pemeriksaan->whereIn('status_pemeriksaan', ['ditugaskan', 'berjalan'])->isNotEmpty()
            || $laporan->penindaklanjutan->whereIn('status_tindakan', ['ditugaskan', 'berjalan', 'terkendala'])->isNotEmpty();
    }

    public static function bolehEdit(LaporanKerusakan $laporan, Pengguna $akun): bool
    {
        return ! $akun->isKoordinator() && (int) $laporan->pelapor_id === (int) $akun->pengguna_id
            && $laporan->status_laporan === 'masuk' && $laporan->pemeriksaan->isEmpty();
    }

    public static function bolehPeriksa(LaporanKerusakan $laporan): bool
    {
        if (! in_array($laporan->status_laporan, ['masuk', 'diperiksa']) || self::adaPekerjaanAktif($laporan)) {
            return false;
        }
        $p = self::pemeriksaan($laporan);

        return ! $p
            || self::tindakan($laporan)?->konfirmasi?->hasil_konfirmasi === 'masih_bermasalah';
    }

    public static function bolehIsi(LaporanKerusakan $laporan, Pemeriksaan $p, Pengguna $akun): bool
    {
        return $akun->isPetugas() && (int) $p->petugas_id === (int) $akun->pengguna_id
            && $laporan->status_laporan === 'diperiksa'
            && self::pemeriksaan($laporan)?->pemeriksaan_id === $p->pemeriksaan_id
            && (in_array($p->status_pemeriksaan, ['ditugaskan', 'berjalan'])
                || ($p->status_pemeriksaan === 'selesai' && $p->status_persetujuan === 'revisi'))
            && $p->pengajuanDana->isEmpty()
            && $p->penindaklanjutan->isEmpty();
    }

    public static function bolehTinjau(LaporanKerusakan $laporan, Pemeriksaan $p): bool
    {
        return $laporan->status_laporan === 'diperiksa'
            && self::pemeriksaan($laporan)?->pemeriksaan_id === $p->pemeriksaan_id
            && $p->status_pemeriksaan === 'selesai'
            && $p->status_persetujuan === 'menunggu'
            && ! self::adaPekerjaanAktif($laporan)
            && $p->pengajuanDana->isEmpty()
            && $p->penindaklanjutan->isEmpty();
    }

    public static function bolehBuatDana(Pemeriksaan $p, Pengguna $akun): bool 
    {
        $laporan = $p->laporan;

        return $akun->isPetugas()
            && (int) $p->petugas_id === (int) $akun->pengguna_id
            && $laporan->status_laporan === 'disetujui'
            && $p->status_pemeriksaan === 'selesai'
            && $p->status_persetujuan === 'disetujui'
            && in_array($p->rekomendasi, ['perbaikan', 'penggantian'])
            && (int) self::pemeriksaan($laporan)?->pemeriksaan_id
                === (int) $p->pemeriksaan_id
            && ! self::adaPekerjaanAktif($laporan)
            && ! self::tindakan($laporan)
            && $p->pengajuanDana->isEmpty();
    }

    public static function bolehPutusDana(PengajuanDana $pengajuan, Pengguna $akun): bool 
    {
        $pemeriksaan = $pengajuan->pemeriksaan;
        $laporan = $pemeriksaan->laporan;

        return $akun->isKoordinator()
            && $pengajuan->status_pengajuan === 'diajukan'
            && ! $pengajuan->pengajuanBerikutnya
            && $laporan->status_laporan === 'disetujui'
            && $pemeriksaan->status_pemeriksaan === 'selesai'
            && $pemeriksaan->status_persetujuan === 'disetujui'
            && (int) self::pemeriksaan($laporan)?->pemeriksaan_id
                === (int) $pemeriksaan->pemeriksaan_id
            && ! self::adaPekerjaanAktif($laporan)
            && ! self::tindakan($laporan)
            && ! $pengajuan->penindaklanjutan()->exists();
    }

    public static function bolehRevisiDana(PengajuanDana $pengajuan, Pengguna $akun): bool 
    {
        $pemeriksaan = $pengajuan->pemeriksaan;
        $laporan = $pemeriksaan->laporan;

        return $akun->isPetugas()
            && (int) $pengajuan->pembuat_id === (int) $akun->pengguna_id
            && (int) $pemeriksaan->petugas_id === (int) $akun->pengguna_id
            && $pengajuan->status_pengajuan === 'revisi'
            && ! $pengajuan->pengajuanBerikutnya
            && $laporan->status_laporan === 'disetujui'
            && $pemeriksaan->status_pemeriksaan === 'selesai'
            && $pemeriksaan->status_persetujuan === 'disetujui'
            && (int) self::pemeriksaan($laporan)?->pemeriksaan_id
                === (int) $pemeriksaan->pemeriksaan_id
            && ! self::adaPekerjaanAktif($laporan)
            && ! self::tindakan($laporan)
            && ! $pengajuan->penindaklanjutan()->exists();
    }
    
    public static function danaTerakhir(Pemeriksaan $p)
    {
        $dana = $p->pengajuanDana;

        return $dana->whereNotIn('pengajuan_id', $dana->pluck('pengajuan_sebelumnya_id')->filter());
    }

    public static function bolehLaksana(LaporanKerusakan $laporan): bool
    {
        $p = self::pemeriksaan($laporan);
        if ($laporan->status_laporan !== 'disetujui' || ! $p || ! $laporan->prioritas
            || $p->status_pemeriksaan !== 'selesai' || $p->status_persetujuan !== 'disetujui'
            || ! $p->rekomendasi || self::adaPekerjaanAktif($laporan) || self::tindakan($laporan)) {
            return false;
        }
        $dana = self::danaTerakhir($p);
        if ($p->sumber_pengganti === 'pengadaan' && $dana->isEmpty()) {
            return false;
        }

        return $dana->every(fn ($d) => $d->status_pengajuan === 'disetujui');
    }
    public static function bolehCatatProgres(LaporanKerusakan $laporan, Pengguna $akun): bool 
    {
        $pemeriksaan = self::pemeriksaan($laporan);
        $tindakan = self::tindakan($laporan);

        if (! $pemeriksaan || ! $tindakan) {
            return false;
        }

        return $akun->isPetugas()
            && (int) $tindakan->petugas_id === (int) $akun->pengguna_id
            && $laporan->status_laporan === 'ditangani'
            && $pemeriksaan->status_pemeriksaan === 'selesai'
            && $pemeriksaan->status_persetujuan === 'disetujui'
            && in_array($tindakan->status_tindakan, ['berjalan', 'terkendala'])
            && $tindakan->tanggal_mulai !== null
            && ! $tindakan->konfirmasi;
    }

    public static function inventarisSaatIniId(LaporanKerusakan $laporan): int 
    {
        $penggantianTerakhir = $laporan->penindaklanjutan
            ->where('status_tindakan', '!=', 'dibatalkan')
            ->whereNotNull('inventaris_pengganti_id')
            ->sortByDesc('penugasan_id')
            ->first();

        return (int) (
            $penggantianTerakhir?->inventaris_pengganti_id
            ?? $laporan->inventaris_id
        );
    }

    public static function bolehKonfirmasi(LaporanKerusakan $laporan, Pengguna $akun): bool
    {
        $t = self::tindakan($laporan);

        return in_array($akun->role, ['pj_lab', 'petugas']) && (int) $laporan->pelapor_id === (int) $akun->pengguna_id
            && $laporan->status_laporan === 'ditangani' && ! self::adaPekerjaanAktif($laporan)
            && $t?->status_tindakan === 'selesai' && ! $t->konfirmasi;
    }

    public static function bolehTutup(LaporanKerusakan $laporan): bool
    {
        $p = self::pemeriksaan($laporan);
        $t = self::tindakan($laporan);

        return $laporan->status_laporan === 'ditangani' && ! self::adaPekerjaanAktif($laporan)
            && $p?->status_pemeriksaan === 'selesai' && $p->status_persetujuan === 'disetujui'
            && $t?->status_tindakan === 'selesai' && $t->konfirmasi?->hasil_konfirmasi === 'sesuai'
            && (int) $t->konfirmasi->pelapor_id === (int) $laporan->pelapor_id;
    }

    public static function keterangan(LaporanKerusakan $laporan): string
    {
        if ($laporan->status_laporan === 'dihentikan') {
            return 'Laporan dihentikan oleh koordinator. '.$laporan->alasan_penghentian;
        }
        if ($laporan->status_laporan === 'selesai') {
            return 'Laporan sudah ditutup.';
        }
        $p = self::pemeriksaan($laporan);
        $t = self::tindakan($laporan);
        if (! $p) {
            return 'Menunggu koordinator menugaskan pemeriksa.';
        }
        if ($t?->konfirmasi?->hasil_konfirmasi === 'masih_bermasalah') {
            return 'Hasil belum sesuai. Menunggu pemeriksaan ulang.';
        }
        if ($p->status_pemeriksaan === 'ditugaskan') {
            return 'Petugas pemeriksa sudah ditugaskan. Menunggu hasil pemeriksaan.';
        }
        if ($p->status_pemeriksaan === 'berjalan') {
            return 'Petugas sedang memeriksa barang.';
        }
        if ($p->status_persetujuan === 'menunggu') {
            return 'Menunggu persetujuan rekomendasi dari koordinator.';
        }
        if ($p->status_persetujuan === 'revisi') {
            return 'Menunggu petugas pemeriksa memperbaiki rekomendasi sesuai catatan koordinator.';
        }
        if ($t?->status_tindakan === 'selesai') {
            return $t->konfirmasi ? 'Hasil sudah dikonfirmasi sesuai. Menunggu penutupan laporan.' : 'Pekerjaan selesai. Menunggu konfirmasi pelapor.';
        }
        if ($t?->status_tindakan === 'terkendala') {
            return 'Pekerjaan terkendala. Lihat catatan petugas.';
        }
        if ($t?->status_tindakan === 'berjalan') {
            return 'Petugas sedang menangani kerusakan.';
        }
        if ($t?->status_tindakan === 'ditugaskan') {
            return 'Petugas pelaksana sudah ditugaskan. Menunggu pekerjaan dimulai.';
        }
        $dana = self::danaTerakhir($p);
        if ($dana->contains('status_pengajuan', 'revisi')) {
            return 'Menunggu revisi pengajuan dana.';
        }
        if ($dana->contains('status_pengajuan', 'diajukan')) {
            return 'Menunggu keputusan pengajuan dana.';
        }
        if ($p->sumber_pengganti === 'pengadaan' && $dana->isEmpty()) {
            return 'Menunggu pengajuan dana untuk pengadaan.';
        }
        if (! $laporan->prioritas) {
            return 'Prioritas belum tercatat. Data laporan perlu diperiksa.';
        }

        return 'Menunggu koordinator menugaskan pelaksana.';
    }

    public static function bolehMulai(LaporanKerusakan $laporan, Pengguna $akun): bool 
    {
        $pemeriksaan = self::pemeriksaan($laporan);
        $tindakan = self::tindakan($laporan);

        if (! $akun->isPetugas() || ! $pemeriksaan || ! $tindakan) {
            return false;
        }

        if (
            (int) $tindakan->petugas_id !== (int) $akun->pengguna_id
            || $laporan->status_laporan !== 'disetujui'
            || ! $laporan->prioritas
            || $pemeriksaan->status_pemeriksaan !== 'selesai'
            || $pemeriksaan->status_persetujuan !== 'disetujui'
            || $tindakan->status_tindakan !== 'ditugaskan'
            || $tindakan->tanggal_mulai !== null
            || $tindakan->konfirmasi
        ) {
            return false;
        }

        $daftarDana = self::danaTerakhir($pemeriksaan);

        if (
            $pemeriksaan->sumber_pengganti === 'pengadaan'
            && $daftarDana->isEmpty()
        ) {
            return false;
        }

        if ($daftarDana->contains(
            fn ($dana) => $dana->status_pengajuan !== 'disetujui'
        )) {
            return false;
        }

        if ($daftarDana->isNotEmpty()) {
            return $daftarDana->contains(
                fn ($dana) =>
                    (int) $dana->pengajuan_id === (int) $tindakan->pengajuan_id
            );
        }

        return $tindakan->pengajuan_id === null;
    }
}
