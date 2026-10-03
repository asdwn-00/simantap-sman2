<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangan';

    protected $primaryKey = 'ruangan_id';

    public $timestamps = false;

    protected $fillable = ['kode_ruangan', 'nama_ruangan', 'jenis_ruangan', 'lantai', 'pj_id', 'petugas_id'];

    public function getNamaRuanganAttribute($nilai): string
    {
        return trim(str_ireplace(['(Dummy)', '[DUMMY]'], '', $nilai ?? ''));
    }

    public function pj()
    {
        return $this->belongsTo(Pengguna::class, 'pj_id');
    }

    public function petugasPenanggungJawab()
    {
        return $this->belongsTo(Pengguna::class, 'petugas_id');
    }

    public function inventaris()
    {
        return $this->hasMany(Inventaris::class, 'ruangan_id');
    }

    public function laporan()
    {
        return $this->hasMany(LaporanKerusakan::class, 'ruangan_id');
    }
}
