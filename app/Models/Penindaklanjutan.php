<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penindaklanjutan extends Model
{
    protected $table = 'penindaklanjutan';

    protected $primaryKey = 'penugasan_id';

    public $timestamps = false;

    protected $casts = ['tanggal_mulai' => 'datetime'];

    protected $fillable = [
        'pemeriksaan_id', 'pengajuan_id', 'petugas_id', 'tanggal_mulai',
        'catatan_tindakan', 'kendala', 'hasil', 'inventaris_pengganti_id', 'status_tindakan',
    ];

    public function pemeriksaan()
    {
        return $this->belongsTo(Pemeriksaan::class, 'pemeriksaan_id');
    }

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanDana::class, 'pengajuan_id');
    }

    public function petugas()
    {
        return $this->belongsTo(Pengguna::class, 'petugas_id');
    }

    public function inventarisPengganti()
    {
        return $this->belongsTo(Inventaris::class, 'inventaris_pengganti_id');
    }

    public function konfirmasi()
    {
        return $this->hasOne(KonfirmasiHasil::class, 'penugasan_id');
    }
}
