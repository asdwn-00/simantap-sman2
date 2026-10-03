<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $primaryKey = 'pengguna_id';

    public $timestamps = false;

    protected $fillable = ['nama', 'email', 'password_hash', 'role'];

    protected $hidden = ['password_hash'];

    public function getNamaAttribute($nilai): string
    {
        return trim(str_ireplace(['(Dummy)', '[DUMMY]'], '', $nilai ?? ''));
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function getRememberTokenName()
    {
        return '';
    }

    public function ruanganLab()
    {
        return $this->hasMany(Ruangan::class, 'pj_id');
    }

    public function ruanganTanggungJawab()
    {
        return $this->hasMany(Ruangan::class, 'petugas_id');
    }

    public function laporanDibuat()
    {
        return $this->hasMany(LaporanKerusakan::class, 'pelapor_id');
    }

    public function pemeriksaanDitugaskan()
    {
        return $this->hasMany(Pemeriksaan::class, 'petugas_id');
    }

    public function penindaklanjutanDitugaskan()
    {
        return $this->hasMany(Penindaklanjutan::class, 'petugas_id');
    }

    public function pengajuanDibuat()
    {
        return $this->hasMany(PengajuanDana::class, 'pembuat_id');
    }

    public function isPjLab(): bool
    {
        return $this->role === 'pj_lab';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isKoordinator(): bool
    {
        return $this->role === 'koordinator';
    }
}
