<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $table = 'inventaris';

    protected $primaryKey = 'inventaris_id';

    public $timestamps = false;

    protected $fillable = [
        'nama_barang', 'kategori', 'ruangan_id',
        'kondisi', 'status_penggunaan', 'spesifikasi', 'nomor_seri',
    ];

    public function getNamaBarangAttribute($nilai): string
    {
        return trim(str_ireplace(['(Dummy)', '[DUMMY]'], '', $nilai ?? ''));
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function laporan()
    {
        return $this->hasMany(LaporanKerusakan::class, 'inventaris_id');
    }
}
