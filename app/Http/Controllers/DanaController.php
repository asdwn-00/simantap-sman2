<?php

namespace App\Http\Controllers;

use App\Models\LaporanKerusakan;
use App\Models\PengajuanDana;
use App\Models\Pemeriksaan;
use App\Services\AlurLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class DanaController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $akun = Auth::guard('pengguna')->user();
        $q = PengajuanDana::with(['pembuat', 'pemeriksaan.laporan.ruangan', 'pengajuanBerikutnya']);
        if ($akun->isPetugas()) {
            $q->where('pembuat_id', $akun->pengguna_id);
        }
        if ($akun->isPjLab()) {
            $q->whereHas('pemeriksaan.laporan.ruangan', fn ($r) => $r->where('pj_id', $akun->pengguna_id));
        }
        $ringkasan = ['total' => (clone $q)->count()];
        foreach (['diajukan', 'disetujui', 'revisi'] as $status) {
            $ringkasan[$status] = (clone $q)->where('status_pengajuan', $status)->whereDoesntHave('pengajuanBerikutnya')->count();
        }
        if (in_array($request->query('status'), ['diajukan','disetujui','revisi','ditolak'])) {
            $q->where('status_pengajuan', $request->query('status'));
        }
        $dana = $q->latest('pengajuan_id')->paginate(12)->withQueryString();
        $bolehLihatLaporan = LaporanKerusakan::terlihat($akun)->pluck('laporan_id');

        $pilihanPemeriksaan = collect();

        if ($akun->isPetugas()) {
            $pilihanPemeriksaan = Pemeriksaan::with([
                'laporan.inventaris',
                'laporan.ruangan',
                'laporan.pemeriksaan',
                'laporan.penindaklanjutan',
                'pengajuanDana',
            ])
                ->where('petugas_id', $akun->pengguna_id)
                ->where('status_pemeriksaan', 'selesai')
                ->where('status_persetujuan', 'disetujui')
                ->whereDoesntHave('pengajuanDana')
                ->whereHas('laporan', function ($query) {
                    $query->where('status_laporan', 'disetujui');
                })
                ->latest('pemeriksaan_id')
                ->get()
                ->filter(function ($pemeriksaan) use ($akun) {
                    return AlurLaporan::bolehBuatDana($pemeriksaan, $akun);
                })
                ->values();
        }

        return view('dana.index', compact(
            'akun',
            'dana',
            'bolehLihatLaporan',
            'ringkasan',
            'pilihanPemeriksaan'
        ));
    }
    public function store(Request $request)
    {
        $akun = Auth::guard('pengguna')->user();

        abort_unless(
            $akun->isPetugas(),
            403,
            'Hanya petugas sarpras yang dapat membuat pengajuan dana.'
        );

        $data = $request->validateWithBag('buatDana', [
            'pemeriksaan_id' => [
                'required',
                'integer',
                'exists:pemeriksaan,pemeriksaan_id',
            ],
            'rincian_kebutuhan' => [
                'required',
                'string',
                'max:5000',
            ],
            'estimasi_biaya' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:999999999999.99',
            ],
        ], [
            'pemeriksaan_id.required' => 'Pilih laporan yang membutuhkan dana.',
            'pemeriksaan_id.exists' => 'Pemeriksaan yang dipilih tidak ditemukan.',
            'rincian_kebutuhan.required' => 'Rincian kebutuhan wajib diisi.',
            'estimasi_biaya.required' => 'Estimasi biaya wajib diisi.',
            'estimasi_biaya.numeric' => 'Estimasi biaya harus berupa angka.',
            'estimasi_biaya.decimal' => 'Gunakan maksimal dua angka desimal.',
            'estimasi_biaya.min' => 'Estimasi biaya harus lebih besar dari nol.',
        ]);

        DB::transaction(function () use ($data, $akun) {
            $pemeriksaan = Pemeriksaan::findOrFail(
                $data['pemeriksaan_id']
            );

            $laporan = LaporanKerusakan::whereKey(
                $pemeriksaan->laporan_id
            )->lockForUpdate()->firstOrFail();

            $pemeriksaan = $pemeriksaan->fresh();
            $pemeriksaan->setRelation('laporan', $laporan);

            if (! AlurLaporan::bolehBuatDana($pemeriksaan, $akun)) {
                throw ValidationException::withMessages([
                    'pemeriksaan_id' =>
                        'Laporan tidak lagi memenuhi syarat. '
                        .'Pastikan ini tugas pemeriksaan Anda, rencananya '
                        .'sudah disetujui, belum memiliki pengajuan dana, '
                        .'dan belum masuk pelaksanaan.',
                ])->errorBag('buatDana');
            }

            PengajuanDana::create([
                'pemeriksaan_id' => $pemeriksaan->pemeriksaan_id,
                'pengajuan_sebelumnya_id' => null,
                'pembuat_id' => $akun->pengguna_id,
                'koordinator_id' => null,
                'tanggal_dibuat' => now(),
                'rincian_kebutuhan' => $data['rincian_kebutuhan'],
                'estimasi_biaya' => $data['estimasi_biaya'],
                'status_pengajuan' => 'diajukan',
                'catatan' => null,
            ]);
        });

        return redirect()
            ->route('dana.index')
            ->with('sukses', 'Pengajuan dana berhasil dikirim.');
    }

    public function keputusan(Request $request, PengajuanDana $pengajuan)
{
    $akun = Auth::guard('pengguna')->user();

    abort_unless(
        $akun->isKoordinator(),
        403,
        'Hanya koordinator yang dapat memutuskan pengajuan dana.'
    );

    $namaError = 'keputusanDana'.$pengajuan->pengajuan_id;

    $data = $request->validateWithBag($namaError, [
        'keputusan' => [
            'required',
            'in:disetujui,revisi,ditolak',
        ],
        'catatan' => [
            'nullable',
            'required_if:keputusan,revisi,ditolak',
            'string',
            'max:1000',
        ],
    ], [
        'keputusan.required' => 'Pilih keputusan terlebih dahulu.',
        'keputusan.in' => 'Pilihan keputusan tidak sesuai.',
        'catatan.required_if' =>
            'Catatan wajib diisi untuk meminta revisi atau menolak pengajuan.',
        'catatan.max' => 'Catatan maksimal 1.000 karakter.',
    ]);

    DB::transaction(function () use (
        $pengajuan,
        $akun,
        $data,
        $namaError
    ) {
        $pemeriksaan = Pemeriksaan::findOrFail(
            $pengajuan->pemeriksaan_id
        );

        $laporan = LaporanKerusakan::whereKey(
            $pemeriksaan->laporan_id
        )->lockForUpdate()->firstOrFail();

        $pengajuan = PengajuanDana::whereKey(
            $pengajuan->pengajuan_id
        )->lockForUpdate()->firstOrFail();

        $pemeriksaan = $pemeriksaan->fresh();
        $pemeriksaan->setRelation('laporan', $laporan);
        $pengajuan->setRelation('pemeriksaan', $pemeriksaan);

        if (! AlurLaporan::bolehPutusDana($pengajuan, $akun)) {
            throw ValidationException::withMessages([
                'keputusan' =>
                    'Pengajuan tidak lagi dapat diputuskan. '
                    .'Pengajuan mungkin sudah diputuskan, memiliki '
                    .'versi lanjutan, atau proses laporan sudah berubah.',
            ])->errorBag($namaError);
        }

        $pengajuan->update([
            'status_pengajuan' => $data['keputusan'],
            'koordinator_id' => $akun->pengguna_id,
            'catatan' => $data['catatan'] ?? null,
        ]);
    });

    $pesan = [
        'disetujui' => 'Pengajuan dana disetujui.',
        'revisi' => 'Permintaan revisi pengajuan dana telah disimpan.',
        'ditolak' => 'Pengajuan dana ditolak.',
    ];

    return redirect()
        ->route('dana.index')
        ->with('sukses', $pesan[$data['keputusan']]);
}

public function revisi(Request $request, PengajuanDana $pengajuan)
{
    $akun = Auth::guard('pengguna')->user();

    abort_unless(
        $akun->isPetugas()
        && (int) $pengajuan->pembuat_id === (int) $akun->pengguna_id,
        403,
        'Anda hanya dapat merevisi pengajuan dana milik sendiri.'
    );

    $namaError = 'revisiDana'.$pengajuan->pengajuan_id;

    $data = $request->validateWithBag($namaError, [
        'rincian_kebutuhan' => [
            'required',
            'string',
            'max:5000',
        ],
        'estimasi_biaya' => [
            'required',
            'numeric',
            'decimal:0,2',
            'min:0.01',
            'max:999999999999.99',
        ],
    ], [
        'rincian_kebutuhan.required' => 'Rincian kebutuhan wajib diisi.',
        'rincian_kebutuhan.max' => 'Rincian kebutuhan maksimal 5.000 karakter.',
        'estimasi_biaya.required' => 'Estimasi biaya wajib diisi.',
        'estimasi_biaya.numeric' => 'Estimasi biaya harus berupa angka.',
        'estimasi_biaya.decimal' => 'Gunakan maksimal dua angka desimal.',
        'estimasi_biaya.min' => 'Estimasi biaya harus lebih besar dari nol.',
        'estimasi_biaya.max' => 'Estimasi biaya melebihi batas yang diperbolehkan.',
    ]);

    DB::transaction(function () use (
        $pengajuan,
        $akun,
        $data,
        $namaError
    ) {
        $pemeriksaan = Pemeriksaan::findOrFail(
            $pengajuan->pemeriksaan_id
        );

        $laporan = LaporanKerusakan::whereKey(
            $pemeriksaan->laporan_id
        )->lockForUpdate()->firstOrFail();

        $pengajuan = PengajuanDana::whereKey(
            $pengajuan->pengajuan_id
        )->lockForUpdate()->firstOrFail();

        $pemeriksaan = $pemeriksaan->fresh();
        $pemeriksaan->setRelation('laporan', $laporan);
        $pengajuan->setRelation('pemeriksaan', $pemeriksaan);

        if (! AlurLaporan::bolehRevisiDana($pengajuan, $akun)) {
            throw ValidationException::withMessages([
                'pengajuan' =>
                    'Pengajuan tidak dapat direvisi. '
                    .'Pastikan statusnya Revisi, belum memiliki versi '
                    .'lanjutan, dan proses laporan belum masuk pelaksanaan.',
            ])->errorBag($namaError);
        }

        PengajuanDana::create([
            'pemeriksaan_id' => $pengajuan->pemeriksaan_id,
            'pengajuan_sebelumnya_id' => $pengajuan->pengajuan_id,
            'pembuat_id' => $akun->pengguna_id,
            'koordinator_id' => null,
            'tanggal_dibuat' => now(),
            'rincian_kebutuhan' => $data['rincian_kebutuhan'],
            'estimasi_biaya' => $data['estimasi_biaya'],
            'status_pengajuan' => 'diajukan',
            'catatan' => null,
        ]);
    });

    return redirect()
        ->route('dana.index')
        ->with(
            'sukses',
            'Revisi berhasil dikirim sebagai versi baru. '
            .'Pengajuan sebelumnya tetap tersimpan.'
        );
}

}
