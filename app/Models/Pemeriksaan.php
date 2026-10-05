<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemeriksaan extends Model
{
    protected $table = 'pemeriksaan';

    protected $primaryKey = 'pemeriksaan_id';

    public $timestamps = false;

    protected $casts = [
        'tanggal_penugasan' => 'datetime',
        'tanggal_pemeriksaan' => 'datetime',
    ];

    protected $fillable = [
        'laporan_id', 'petugas_id', 'tanggal_penugasan', 'tanggal_pemeriksaan',
        'status_pemeriksaan', 'temuan', 'rekomendasi', 'sumber_pengganti',
        'status_persetujuan', 'catatan_koordinator', 'alasan_penggantian', 'jenis_penggantian',
    ];

    public function getLabelRekomendasiAttribute(): string
    {
        if ($this->rekomendasi === 'penggantian') {
            return match ($this->jenis_penggantian) {
                'unit' => 'Penggantian unit utuh',
                'sparepart' => 'Penggantian sparepart',
                default => 'Penggantian (jenis belum ditentukan)',
            };
        }

        return $this->rekomendasi === 'perbaikan' ? 'Perbaikan / servis' : 'Belum ditentukan';
    }

    public function scopePerluDikerjakan($query)
    {
        return $query->whereHas('laporan', fn ($q) => $q->where('status_laporan', 'diperiksa'))
            ->whereRaw('pemeriksaan_id = (SELECT MAX(p2.pemeriksaan_id) FROM pemeriksaan p2 WHERE p2.laporan_id = pemeriksaan.laporan_id AND p2.status_pemeriksaan <> ?)', ['dibatalkan'])
            ->where(function ($q) {
                $q->whereIn('status_pemeriksaan', ['ditugaskan', 'berjalan'])
                    ->orWhere(function ($revisi) {
                        $revisi->where('status_pemeriksaan', 'selesai')->where('status_persetujuan', 'revisi');
                    });
            });
    }

    public function laporan()
    {
        return $this->belongsTo(LaporanKerusakan::class, 'laporan_id');
    }

    public function petugas()
    {
        return $this->belongsTo(Pengguna::class, 'petugas_id');
    }

    public function pengajuanDana()
    {
        return $this->hasMany(PengajuanDana::class, 'pemeriksaan_id');
    }

    public function penindaklanjutan()
    {
        return $this->hasMany(Penindaklanjutan::class, 'pemeriksaan_id');
    }
}
