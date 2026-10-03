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
        'status_persetujuan', 'catatan_koordinator',
    ];

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
