<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanDana extends Model
{
    protected $table = 'pengajuan_dana';

    protected $primaryKey = 'pengajuan_id';

    public $timestamps = false;

    protected $casts = ['tanggal_dibuat' => 'datetime'];

    protected $fillable = [
        'pemeriksaan_id', 'pengajuan_sebelumnya_id', 'pembuat_id', 'koordinator_id',
        'tanggal_dibuat', 'rincian_kebutuhan', 'estimasi_biaya', 'status_pengajuan', 'catatan',
    ];

    public function pemeriksaan()
    {
        return $this->belongsTo(Pemeriksaan::class, 'pemeriksaan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(Pengguna::class, 'pembuat_id');
    }

    public function koordinator()
    {
        return $this->belongsTo(Pengguna::class, 'koordinator_id');
    }

    public function pengajuanSebelumnya()
    {
        return $this->belongsTo(PengajuanDana::class, 'pengajuan_sebelumnya_id');
    }

    public function pengajuanBerikutnya()
    {
        return $this->hasOne(PengajuanDana::class, 'pengajuan_sebelumnya_id');
    }

    public function penindaklanjutan()
    {
        return $this->hasMany(Penindaklanjutan::class, 'pengajuan_id');
    }
}
