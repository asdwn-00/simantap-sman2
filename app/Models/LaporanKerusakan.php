<?php

namespace App\Models;

use App\Services\AlurLaporan;
use Illuminate\Database\Eloquent\Model;

class LaporanKerusakan extends Model
{
    protected $table = 'laporan_kerusakan';

    protected $primaryKey = 'laporan_id';

    public $timestamps = false;

    protected $casts = [
        'tanggal_laporan' => 'datetime',
        'tanggal_ditutup' => 'datetime',
    ];

    protected $fillable = [
        'pelapor_id', 'inventaris_id', 'ruangan_id', 'tanggal_laporan',
        'kerusakan', 'prioritas', 'status_laporan',
        'tanggal_ditutup', 'alasan_penghentian',
    ];

    public function getKerusakanAttribute($nilai): string
    {
        return trim(str_ireplace(['(Dummy)', '[DUMMY]'], '', $nilai ?? ''));
    }

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class, 'inventaris_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(Pengguna::class, 'pelapor_id');
    }

    public function getKodeLaporanAttribute(): string
    {
        return 'LAP-'.str_pad((string) $this->laporan_id, 3, '0', STR_PAD_LEFT);
    }

    public function pemeriksaan()
    {
        return $this->hasMany(Pemeriksaan::class, 'laporan_id');
    }

    public function pengajuanDana()
    {
        return $this->hasManyThrough(
            PengajuanDana::class, Pemeriksaan::class,
            'laporan_id', 'pemeriksaan_id', 'laporan_id', 'pemeriksaan_id'
        );
    }

    public function penindaklanjutan()
    {
        return $this->hasManyThrough(
            Penindaklanjutan::class, Pemeriksaan::class,
            'laporan_id', 'pemeriksaan_id', 'laporan_id', 'pemeriksaan_id'
        );
    }

    public function scopeTerlihat($query, Pengguna $akun)
    {
        if ($akun->isPjLab()) {
            $query->where(function ($q) use ($akun) {
                $q->whereHas('ruangan', fn ($r) => $r->where('pj_id', $akun->pengguna_id))
                    ->orWhere('pelapor_id', $akun->pengguna_id);
            });
        } elseif ($akun->isPetugas()) {
            $query->where(function ($q) use ($akun) {
                $q->where('pelapor_id', $akun->pengguna_id)
                    ->orWhereHas('pemeriksaan', fn ($p) => $p->where('petugas_id', $akun->pengguna_id))
                    ->orWhereHas('penindaklanjutan', fn ($t) => $t->where('penindaklanjutan.petugas_id', $akun->pengguna_id));
            });
        } elseif (! $akun->isKoordinator()) {
            $query->whereRaw('1=0');
        }

        return $query;
    }

    public function scopeLengkap($query)
    {
        return $query->with(['inventaris', 'ruangan', 'pelapor', 'pemeriksaan.petugas', 'pemeriksaan.pengajuanDana',
            'penindaklanjutan.petugas', 'penindaklanjutan.konfirmasi']);
    }

    public function scopeUrutAntreanKoordinator($query)
    {
        $pemeriksaanTerakhir = <<<'SQL'
            SELECT MAX(p2.pemeriksaan_id) FROM pemeriksaan p2
            WHERE p2.laporan_id = laporan_kerusakan.laporan_id
              AND p2.status_pemeriksaan <> 'dibatalkan'
        SQL;

        $siapDitinjau = "EXISTS (
            SELECT 1 FROM pemeriksaan p
            WHERE p.pemeriksaan_id = ($pemeriksaanTerakhir)
              AND p.status_pemeriksaan = 'selesai'
              AND p.status_persetujuan = 'menunggu'
        )";

        $siapDitutup = "EXISTS (
            SELECT 1 FROM pemeriksaan p
            JOIN penindaklanjutan t ON t.pemeriksaan_id = p.pemeriksaan_id
            JOIN konfirmasi_hasil k ON k.penugasan_id = t.penugasan_id
            WHERE p.pemeriksaan_id = ($pemeriksaanTerakhir)
              AND p.status_pemeriksaan = 'selesai'
              AND p.status_persetujuan = 'disetujui'
              AND t.penugasan_id = (
                  SELECT MAX(t2.penugasan_id) FROM penindaklanjutan t2
                  WHERE t2.pemeriksaan_id = p.pemeriksaan_id
                    AND t2.status_tindakan <> 'dibatalkan'
              )
              AND t.status_tindakan = 'selesai'
              AND k.hasil_konfirmasi = 'sesuai'
              AND k.pelapor_id = laporan_kerusakan.pelapor_id
        ) AND NOT EXISTS (
            SELECT 1 FROM pemeriksaan pa
            WHERE pa.laporan_id = laporan_kerusakan.laporan_id
              AND pa.status_pemeriksaan IN ('ditugaskan', 'berjalan')
        ) AND NOT EXISTS (
            SELECT 1 FROM penindaklanjutan ta
            JOIN pemeriksaan pt ON pt.pemeriksaan_id = ta.pemeriksaan_id
            WHERE pt.laporan_id = laporan_kerusakan.laporan_id
              AND ta.status_tindakan IN ('ditugaskan', 'berjalan', 'terkendala')
        )";

        return $query
            ->orderByRaw("CASE laporan_kerusakan.status_laporan
                WHEN 'masuk' THEN 0 WHEN 'diperiksa' THEN 1
                WHEN 'disetujui' THEN 2 WHEN 'ditangani' THEN 3
                WHEN 'selesai' THEN 4 WHEN 'dihentikan' THEN 5 ELSE 6 END")
            ->orderByRaw("CASE
                WHEN laporan_kerusakan.status_laporan = 'diperiksa' AND ($siapDitinjau) THEN 0
                WHEN laporan_kerusakan.status_laporan = 'ditangani' AND ($siapDitutup) THEN 0
                ELSE 1 END")
            ->orderBy('laporan_kerusakan.tanggal_laporan')
            ->orderBy('laporan_kerusakan.laporan_id');
    }

    public function scopeFilter($query, $request)
    {
        if ($cari = trim((string) $request->query('cari'))) {
            $query->where(function ($q) use ($cari) {
                if (preg_match('/^(?:LAP-)?(\d+)$/i', $cari, $m)) { $q->orWhere('laporan_id', (int) $m[1]); }
                $q->orWhere('kerusakan', 'like', "%$cari%")
                    ->orWhereHas('inventaris', fn ($i) => $i->where('nama_barang', 'like', "%$cari%"))
                    ->orWhereHas('ruangan', fn ($r) => $r->where('nama_ruangan', 'like', "%$cari%"));
            });
        }
        if (array_key_exists((string) $request->query('status'), config('simantap.status'))) {
            $query->where('status_laporan', $request->query('status'));
        }
        if (in_array($request->query('prioritas'), ['tinggi', 'rendah'])) {
            $query->where('prioritas', $request->query('prioritas'));
        }
        if ($request->query('prioritas') === 'belum') {
            $query->whereNull('prioritas');
        }

        return $query;
    }

    public function getPersentaseAttribute(): ?int
    {
        return config('simantap.status')[$this->status_laporan];
    }

    public function getKeteranganProsesAttribute(): string
    {
        return AlurLaporan::keterangan($this);
    }
}
