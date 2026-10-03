<?php

namespace App\Http\Controllers;

use App\Models\KonfirmasiHasil;
use App\Models\LaporanKerusakan;
use App\Models\Penindaklanjutan;
use App\Services\AlurLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KonfirmasiController extends Controller
{
    public function store(Request $request, Penindaklanjutan $penindaklanjutan)
    {
        $akun = Auth::guard('pengguna')->user();
        abort_unless(in_array($akun->role, ['pj_lab', 'petugas']) && (int) $penindaklanjutan->pemeriksaan->laporan->pelapor_id === (int) $akun->pengguna_id, 403);
        $data = $request->validate(['hasil_konfirmasi' => 'required|in:sesuai,masih_bermasalah', 'catatan' => 'nullable|required_if:hasil_konfirmasi,masih_bermasalah|string|max:1000']);
        DB::transaction(function () use ($penindaklanjutan, $akun, $data) {
            $laporan = LaporanKerusakan::whereKey($penindaklanjutan->pemeriksaan->laporan_id)->lockForUpdate()->firstOrFail();
            abort_unless(AlurLaporan::bolehKonfirmasi($laporan, $akun) && AlurLaporan::tindakan($laporan)->penugasan_id === $penindaklanjutan->penugasan_id, 422, 'Hanya hasil terakhir yang selesai dan belum dikonfirmasi dapat dikonfirmasi.');
            KonfirmasiHasil::create(['penugasan_id' => $penindaklanjutan->penugasan_id, 'pelapor_id' => $akun->pengguna_id,
                'tanggal_konfirmasi' => now(), 'hasil_konfirmasi' => $data['hasil_konfirmasi'], 'catatan' => $data['catatan'] ?? null]);
            if ($data['hasil_konfirmasi'] === 'masih_bermasalah') {
                $laporan->update(['status_laporan' => 'diperiksa']);
            }
        });

        return back()->with('sukses', 'Konfirmasi disimpan. Hasil sesuai menunggu penutupan koordinator; hasil bermasalah diperiksa ulang.');
    }
}
