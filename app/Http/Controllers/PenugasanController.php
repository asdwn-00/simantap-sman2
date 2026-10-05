<?php

namespace App\Http\Controllers;

use App\Models\LaporanKerusakan;
use App\Models\Pemeriksaan;
use App\Models\Pengguna;
use App\Models\Penindaklanjutan;
use App\Models\Inventaris;
use App\Models\Ruangan;
use App\Services\AlurLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenugasanController extends Controller
{
    public function index(Request $request)
    {
        $akun = Auth::guard('pengguna')->user();
        $daftar = LaporanKerusakan::terlihat($akun)->lengkap()->orderByRaw("CASE prioritas WHEN 'tinggi' THEN 0 WHEN 'rendah' THEN 1 ELSE 2 END")->orderBy('tanggal_laporan')->get();
        
        if ($akun->isPjLab()) {
            $daftar = $daftar->filter(fn ($l) => (int) $l->ruangan->pj_id === (int) $akun->pengguna_id);
        }
        $petugas = Pengguna::where('role', 'petugas')->withCount([
            'pemeriksaanDitugaskan as periksa_aktif' => fn ($q) => $q->perluDikerjakan(),
            'penindaklanjutanDitugaskan as laksana_aktif' => fn ($q) => $q->whereIn('status_tindakan', ['ditugaskan', 'berjalan', 'terkendala']),
        ])->get();
        foreach ($petugas as $p) {
            $p->tugas_aktif = $p->periksa_aktif + $p->laksana_aktif;
        }
        $perluTugas = $akun->isKoordinator() ? $daftar->filter(fn ($l) => AlurLaporan::bolehPeriksa($l) || AlurLaporan::bolehLaksana($l)) : collect();
        $tugas = collect();
        foreach ($daftar as $l) {
            foreach (['Pemeriksaan' => $l->pemeriksaan, 'Pelaksanaan' => $l->penindaklanjutan] as $jenis => $items) {
                foreach ($items as $t) {
                    if ($akun->isPetugas() && (int) $t->petugas_id !== (int) $akun->pengguna_id) {
                        continue;
                    }
                    if ($akun->isKoordinator() && $request->filled('petugas') && (int) $t->petugas_id !== (int) $request->query('petugas')) {
                        continue;
                    }
                    $tugas->push(['jenis' => $jenis, 'laporan' => $l, 'petugas' => $t->petugas, 'status' => $jenis === 'Pemeriksaan' ? (in_array($t->status_persetujuan, ['revisi', 'dihentikan']) ? $t->status_persetujuan : $t->status_pemeriksaan) : $t->status_tindakan]);
                }
            }
        }

        $petugasList = $petugas->each(fn ($p) => $p->jumlah_tugas_aktif = $p->tugas_aktif);
        if (! $akun->isKoordinator()) {
            $ids = $tugas->pluck('petugas.pengguna_id');
            $petugasList = $petugasList->whereIn('pengguna_id', $ids);
        }
        $perluPemeriksaan = $perluTugas->filter(fn ($l) => AlurLaporan::bolehPeriksa($l));
        $perluPelaksanaan = $perluTugas->filter(fn ($l) => AlurLaporan::bolehLaksana($l));
        foreach ($perluTugas as $l) {
            $l->nama_barang = $l->inventaris->nama_barang;
            $l->nama_ruangan = $l->ruangan->nama_ruangan;
            $p = AlurLaporan::pemeriksaan($l);
            $l->pemeriksaan_id = $p?->pemeriksaan_id;
            $l->rekomendasi = $p?->rekomendasi;
            $l->label_rekomendasi = $p?->label_rekomendasi;
            $l->sumber_pengganti = $p?->sumber_pengganti;
        }
        $map = fn ($t) => (object) ['petugas_nama' => $t['petugas']->nama, 'petugas_email' => $t['petugas']->email,
            'laporan_id' => $t['laporan']->laporan_id, 'status' => $t['status'], 'nama_ruangan' => $t['laporan']->ruangan->nama_ruangan];
        $tugasPemeriksaanBerjalan = $tugas->where('jenis', 'Pemeriksaan')->map($map);
        $tugasPelaksanaanBerjalan = $tugas->where('jenis', 'Pelaksanaan')->map($map);
        return view('penugasan.index', compact('akun', 'petugasList', 'perluPemeriksaan', 'perluPelaksanaan', 'tugasPemeriksaanBerjalan', 'tugasPelaksanaanBerjalan'));
    }

    private function pilihPetugas(Request $request): int
    {
        abort_unless(Auth::guard('pengguna')->user()->isKoordinator(), 403);
        $data = $request->validate(['petugas_id' => ['required', 'integer', Rule::exists('pengguna', 'pengguna_id')->where('role', 'petugas')]]);

        return (int) $data['petugas_id'];
    }

    public function tugaskanPemeriksaan(Request $request, LaporanKerusakan $laporan)
    {
        $petugasId = $this->pilihPetugas($request);
        DB::transaction(function () use ($laporan, $petugasId) {
            $laporan = LaporanKerusakan::whereKey($laporan->laporan_id)->lockForUpdate()->firstOrFail();
            abort_unless(AlurLaporan::bolehPeriksa($laporan), 422, 'Laporan belum memenuhi syarat pemeriksaan baru.');
            Pemeriksaan::create(['laporan_id' => $laporan->laporan_id, 'petugas_id' => $petugasId, 'tanggal_penugasan' => now(),
                'status_pemeriksaan' => 'ditugaskan', 'status_persetujuan' => 'belum_diajukan']);
            $laporan->update(['status_laporan' => 'diperiksa']);
        });

        return back()->with('sukses', 'Petugas pemeriksa berhasil ditugaskan.');
    }

    public function tugaskanPelaksanaan(Request $request, Pemeriksaan $pemeriksaan)
    {
        $petugasId = $this->pilihPetugas($request);
        DB::transaction(function () use ($pemeriksaan, $petugasId) {
            $laporan = LaporanKerusakan::whereKey($pemeriksaan->laporan_id)->lockForUpdate()->firstOrFail();
            abort_unless(AlurLaporan::pemeriksaan($laporan)?->pemeriksaan_id === $pemeriksaan->pemeriksaan_id && AlurLaporan::bolehLaksana($laporan), 422, 'Rencana, prioritas, atau dana belum siap; atau pelaksana sudah ditugaskan.');
            $dana = AlurLaporan::danaTerakhir(AlurLaporan::pemeriksaan($laporan))->sortByDesc('pengajuan_id')->first();
            Penindaklanjutan::create(['pemeriksaan_id' => $pemeriksaan->pemeriksaan_id, 'pengajuan_id' => $dana?->pengajuan_id,
                'petugas_id' => $petugasId, 'tanggal_mulai' => null, 'status_tindakan' => 'ditugaskan']);
        });

        return back()->with('sukses','Pelaksana ditugaskan. Pekerjaan belum dimulai.');
    }

    public function mulai(Penindaklanjutan $penindaklanjutan)
    {
        $akun = Auth::guard('pengguna')->user();

        abort_unless(
            $akun->isPetugas()
            && (int) $penindaklanjutan->petugas_id === (int) $akun->pengguna_id,
            403,
            'Hanya petugas yang ditugaskan dapat memulai pekerjaan.'
        );

        $laporanId = $penindaklanjutan->pemeriksaan->laporan_id;

        DB::transaction(function () use (
            $laporanId,
            $penindaklanjutan,
            $akun
        ) {
            $laporan = LaporanKerusakan::whereKey($laporanId)
                ->lockForUpdate()
                ->firstOrFail();

            $tindakan = Penindaklanjutan::whereKey(
                $penindaklanjutan->penugasan_id
            )->lockForUpdate()->firstOrFail();

            $tindakanTerakhir = AlurLaporan::tindakan($laporan);

            if (
                ! AlurLaporan::bolehMulai($laporan, $akun)
                || (int) $tindakanTerakhir?->penugasan_id
                    !== (int) $tindakan->penugasan_id
            ) {
                throw ValidationException::withMessages([
                    'pekerjaan' =>
                        'Pekerjaan belum dapat dimulai. '
                        .'Periksa penugasan, persetujuan rencana, dan dana. '
                        .'Pekerjaan yang sudah dimulai tidak dapat dimulai ulang.',
                ]);
            }

            $tindakan->update([
                'status_tindakan' => 'berjalan',
                'tanggal_mulai' => now(),
            ]);

            $laporan->update([
                'status_laporan' => 'ditangani',
            ]);
        });

        return redirect()
            ->route('laporan.show', $laporanId)
            ->with('sukses', 'Pekerjaan berhasil dimulai.');
    }

    public function simpanProgres(Request $request, Penindaklanjutan $penindaklanjutan) 
    {
        $akun = Auth::guard('pengguna')->user();

        abort_unless(
            $akun->isPetugas()
            && (int) $penindaklanjutan->petugas_id === (int) $akun->pengguna_id,
            403,
            'Anda hanya dapat mencatat pekerjaan yang ditugaskan kepada Anda.'
        );

        $namaError = 'progres'.$penindaklanjutan->penugasan_id;

        $data = $request->validateWithBag($namaError, [
            'status_tindakan' => [
                'required',
                'in:berjalan,terkendala,selesai',
            ],
            'catatan_tindakan' => [
                'required',
                'string',
                'max:5000',
            ],
            'kendala' => [
                'nullable',
                'required_if:status_tindakan,terkendala',
                'string',
                'max:5000',
            ],
            'hasil' => [
                'nullable',
                'required_if:status_tindakan,selesai',
                'string',
                'max:5000',
            ],
            'inventaris_pengganti_id' => [
                'nullable',
                'integer',
                'exists:inventaris,inventaris_id',
            ],
            'kondisi_barang_lama' => [
                'nullable',
                'string',
                'in:baik,rusak_ringan,rusak_berat',
            ],
            'gudang_barang_lama_id' => [
                'nullable',
                'integer',
                Rule::exists('ruangan', 'ruangan_id')
                    ->where('jenis_ruangan', 'gudang'),
            ],
        ], [
            'status_tindakan.required' => 'Pilih status pekerjaan.',
            'status_tindakan.in' => 'Status pekerjaan tidak sesuai.',
            'catatan_tindakan.required' => 'Catatan pengerjaan wajib diisi.',
            'kendala.required_if' =>
                'Jelaskan kendalanya jika pekerjaan berstatus Terkendala.',
            'hasil.required_if' =>
                'Hasil pekerjaan wajib diisi sebelum ditandai Selesai.',
        ]);

        $laporanId = $penindaklanjutan->pemeriksaan->laporan_id;

        DB::transaction(function () use (
            $laporanId,
            $penindaklanjutan,
            $akun,
            $data,
            $namaError
        ) {
            $laporan = LaporanKerusakan::whereKey($laporanId)
                ->lockForUpdate()
                ->firstOrFail();

            $tindakan = Penindaklanjutan::whereKey(
                $penindaklanjutan->penugasan_id
            )->lockForUpdate()->firstOrFail();

            $tindakanTerakhir = AlurLaporan::tindakan($laporan);

            if (
                ! AlurLaporan::bolehCatatProgres($laporan, $akun)
                || (int) $tindakanTerakhir?->penugasan_id
                    !== (int) $tindakan->penugasan_id
            ) {
                throw ValidationException::withMessages([
                    'pekerjaan' =>
                        'Progres tidak dapat diubah. Pastikan pekerjaan '
                        .'masih berjalan atau terkendala dan merupakan tugas Anda.',
                ])->errorBag($namaError);
            }

            $pemeriksaan = AlurLaporan::pemeriksaan($laporan);

            if ($pemeriksaan->rekomendasi === 'penggantian'
                && ! in_array($pemeriksaan->jenis_penggantian, ['unit', 'sparepart'], true)) {
                throw ValidationException::withMessages([
                    'pekerjaan' => 'Jenis penggantian pada rekomendasi belum tercatat. Periksa pembaruan database.',
                ])->errorBag($namaError);
            }

            if ($pemeriksaan->rekomendasi === 'penggantian'
                && $pemeriksaan->jenis_penggantian === 'sparepart') {
                $dana = AlurLaporan::danaTerakhir($pemeriksaan);
                if ($pemeriksaan->sumber_pengganti !== 'pengadaan'
                    || $dana->isEmpty()
                    || $dana->contains(fn ($d) => $d->status_pengajuan !== 'disetujui')) {
                    throw ValidationException::withMessages([
                        'pekerjaan' => 'Penggantian sparepart memerlukan pengajuan dana yang sudah disetujui.',
                    ])->errorBag($namaError);
                }

                if (! empty($data['inventaris_pengganti_id'])
                    || ! empty($data['kondisi_barang_lama'])
                    || ! empty($data['gudang_barang_lama_id'])) {
                    throw ValidationException::withMessages([
                        'pekerjaan' => 'Penggantian sparepart tetap memakai unit dan lokasi yang sama. Muat ulang formulir.',
                    ])->errorBag($namaError);
                }
            }

            $penggantiId = null;

            if ($data['status_tindakan'] === 'selesai') {
                if (! in_array($pemeriksaan->rekomendasi, [
                    'perbaikan',
                    'penggantian',
                ])) {
                    throw ValidationException::withMessages([
                        'status_tindakan' => 'Rekomendasi pekerjaan belum sesuai.',
                    ])->errorBag($namaError);
                }

                if ($pemeriksaan->rekomendasi === 'penggantian'
                    && $pemeriksaan->jenis_penggantian === 'unit') {
                    if (empty($data['kondisi_barang_lama'])) {
                        throw ValidationException::withMessages([
                            'kondisi_barang_lama' =>
                                'Pilih kondisi barang lama sebelum menyelesaikan penggantian.',
                        ])->errorBag($namaError);
                    }

                    $penggantiId = (int) ($data['inventaris_pengganti_id'] ?? 0);
                    $barangLamaId = AlurLaporan::inventarisSaatIniId($laporan);

                    if (
                        $penggantiId === 0
                        || $penggantiId === $barangLamaId
                        || $penggantiId === (int) $laporan->inventaris_id
                    ) {
                        throw ValidationException::withMessages([
                            'inventaris_pengganti_id' =>
                                'Pilih unit pengganti yang berbeda dari barang lama.',
                        ])->errorBag($namaError);
                    }

                    $daftarBarang = Inventaris::whereIn('inventaris_id', [
                        $barangLamaId,
                        $penggantiId,
                    ])
                        ->orderBy('inventaris_id')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('inventaris_id');

                    $barangLama = $daftarBarang->get($barangLamaId);
                    $barangPengganti = $daftarBarang->get($penggantiId);

                    if (! $barangLama || ! $barangPengganti) {
                        throw ValidationException::withMessages([
                            'inventaris_pengganti_id' =>
                                'Data barang lama atau barang pengganti tidak ditemukan.',
                        ])->errorBag($namaError);
                    }

                    if (
                        (int) $barangLama->ruangan_id
                        !== (int) $laporan->ruangan_id
                    ) {
                        throw ValidationException::withMessages([
                            'inventaris_pengganti_id' =>
                                'Lokasi barang lama sudah berubah. '
                                .'Periksa kembali lokasi barang sebelum mengganti.',
                        ])->errorBag($namaError);
                    }

                    $adaLaporanAktif = $barangPengganti->laporan()
                        ->whereNotIn('status_laporan', ['selesai', 'dihentikan'])
                        ->exists();

                    if (
                        $barangPengganti->kondisi !== 'baik'
                        || $barangPengganti->status_penggunaan !== 'tersedia'
                        || $barangPengganti->ruangan?->jenis_ruangan !== 'gudang'
                        || $adaLaporanAktif
                    ) {
                        throw ValidationException::withMessages([
                            'inventaris_pengganti_id' =>
                                'Barang pengganti tidak lagi tersedia atau tidak layak. '
                                .'Pilih barang baik yang tersedia di gudang '
                                .'dan tidak memiliki laporan aktif.',
                        ])->errorBag($namaError);
                    }

                    $ruanganTujuan = Ruangan::whereKey($laporan->ruangan_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $statusPengganti = 'digunakan';

                    if ($ruanganTujuan->jenis_ruangan === 'gudang') {
                        $statusPengganti = 'tidak_digunakan';
                    }

                    $barangPengganti->update([
                        'ruangan_id' => $ruanganTujuan->ruangan_id,
                        'status_penggunaan' => $statusPengganti,
                    ]);

                    $lokasiBarangLamaId = $barangLama->ruangan_id;

                    if (! empty($data['gudang_barang_lama_id'])) {
                        $gudangTujuan = Ruangan::whereKey(
                            $data['gudang_barang_lama_id']
                        )
                            ->lockForUpdate()
                            ->first();

                        if (
                            ! $gudangTujuan
                            || $gudangTujuan->jenis_ruangan !== 'gudang'
                        ) {
                            throw ValidationException::withMessages([
                                'gudang_barang_lama_id' =>
                                    'Gudang tujuan tidak tersedia. Pilih kembali lokasi barang lama.',
                            ])->errorBag($namaError);
                        }

                        $lokasiBarangLamaId = $gudangTujuan->ruangan_id;
                    }

                    $barangLama->update([
                        'kondisi' => $data['kondisi_barang_lama'],
                        'status_penggunaan' => 'tidak_digunakan',
                        'ruangan_id' => $lokasiBarangLamaId,
                    ]);
                }
            }

            $tindakan->update([
                'status_tindakan' => $data['status_tindakan'],
                'catatan_tindakan' => $data['catatan_tindakan'],
                'kendala' => $data['kendala'] ?? null,
                'hasil' => $data['hasil'] ?? null,
                'inventaris_pengganti_id' => $penggantiId,
            ]);
        });

        $pesan = $data['status_tindakan'] === 'selesai'
            ? 'Pekerjaan selesai dicatat. Menunggu konfirmasi pelapor.'
            : 'Progres pekerjaan berhasil disimpan.';

        return redirect()
            ->route('laporan.show', $laporanId)
            ->with('sukses', $pesan);
    }
}
