<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfirmasiHasil extends Model
{
    protected $table = 'konfirmasi_hasil';

    protected $primaryKey = 'konfirmasi_id';

    public $timestamps = false;

    protected $casts = ['tanggal_konfirmasi' => 'datetime'];

    protected $fillable = ['penugasan_id', 'pelapor_id', 'tanggal_konfirmasi', 'hasil_konfirmasi', 'catatan'];

    public function penindaklanjutan()
    {
        return $this->belongsTo(Penindaklanjutan::class, 'penugasan_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(Pengguna::class, 'pelapor_id');
    }
}
