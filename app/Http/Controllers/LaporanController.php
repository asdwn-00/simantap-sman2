<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\LaporanKerusakan;
use App\Models\Pemeriksaan;
use App\Models\Ruangan;
use App\Services\AlurLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $akun = Auth::guard('pengguna')->user();
        $query = LaporanKerusakan::terlihat($akun)->lengkap()->filter($request);
        if ($akun->isKoordinator()) {
            $query->urutAntreanKoordinator();
        } else {
            $query->latest('laporan_id');
        }
        $daftar = $query->paginate(12)->withQueryString();

        $pengguna = $akun;
        $baris = $daftar->through(function ($l) use ($akun) {
            $p = AlurLaporan::pemeriksaan($l);
            $t = AlurLaporan::tindakan($l);
            $warna = config('simantap.warna_laporan');
            $aksi = AlurLaporan::bolehTutup($l) ? 'tutup' : (($l->status_laporan === 'diperiksa' && $p?->status_persetujuan === 'menunggu' && $p?->status_pemeriksaan === 'selesai') ? 'tinjau' : ((AlurLaporan::bolehPeriksa($l) || AlurLaporan::bolehLaksana($l)) ? 'penugasan' : 'detail'));
            return ['laporan' => $l, 'pemeriksaan_terakhir' => $p, 'tindakan_terakhir' => $t, 'konfirmasi_terakhir' => $t?->konfirmasi,
                'label_status' => ucfirst($l->status_laporan), 'warna_status' => $warna[$l->status_laporan],
                'rekomendasi_text' => e(AlurLaporan::keterangan($l)), 'aksi' => $aksi,
                'bisa_ubah_hapus' => AlurLaporan::bolehEdit($l, $akun), 'bisa_isi_pemeriksaan' => $p && AlurLaporan::bolehIsi($l, $p, $akun),
                'bisa_konfirmasi' => AlurLaporan::bolehKonfirmasi($l, $akun)];
        });
        $view = $akun->isKoordinator() ? 'laporan.index-koordinator' : ($akun->isPjLab() ? 'laporan.pjlab-index' : 'laporan.index');
        return view($view, compact('baris', 'pengguna'));
    }

    public function create(Request $request)
    {
        $akun = Auth::guard('pengguna')->user();
        abort_if($akun->isKoordinator(), 403);
        $inventaris = Inventaris::with('ruangan')->whereHas('ruangan', function ($q) use ($akun) {
            $q->where($akun->isPjLab() ? 'pj_id' : 'petugas_id', $akun->pengguna_id);
        })->orderBy('nama_barang')->get();
        if ($inventaris->contains('inventaris_id', (int) $request->query('inventaris'))) {
            session()->flash('_old_input.inventaris_id', $request->query('inventaris'));
        }

        $ruangan = $inventaris->pluck('ruangan')->unique('ruangan_id');
        return view('laporan.create', compact('inventaris', 'ruangan', 'akun'));
    }

    public function store(Request $request)
    {
        $akun = Auth::guard('pengguna')->user();
        abort_if($akun->isKoordinator(), 403);
        $data = $request->validate(['inventaris_id' => 'required|integer|exists:inventaris,inventaris_id', 'kerusakan' => 'required|string|max:1000']);
        $laporan = DB::transaction(function () use ($data, $akun) {
            $i = Inventaris::whereKey($data['inventaris_id'])->lockForUpdate()->firstOrFail();
            $r = $i->ruangan()->lockForUpdate()->firstOrFail();
            abort_unless((int) ($akun->isPjLab() ? $r->pj_id : $r->petugas_id) === (int) $akun->pengguna_id, 403, 'Barang di luar ruangan tanggung jawab Anda.');

            return LaporanKerusakan::create(['pelapor_id' => $akun->pengguna_id, 'inventaris_id' => $i->inventaris_id, 'ruangan_id' => $r->ruangan_id,
                'tanggal_laporan' => now(), 'kerusakan' => $data['kerusakan'], 'status_laporan' => 'masuk']);
        });

        return redirect()->route('laporan.show', $laporan)->with('sukses', 'Laporan berhasil dibuat.');
    }

    public function show(LaporanKerusakan $laporan)
    {
        $akun = Auth::guard('pengguna')->user();

        abort_unless(
            LaporanKerusakan::terlihat($akun)
                ->whereKey($laporan->laporan_id)
                ->exists(),
            403
        );

        $laporan = LaporanKerusakan::lengkap()
            ->findOrFail($laporan->laporan_id);

        $bisaUbahHapus = AlurLaporan::bolehEdit($laporan, $akun);

        $inventarisSaatIni = Inventaris::findOrFail(
            AlurLaporan::inventarisSaatIniId($laporan)
        );

        $pilihanPengganti = collect();
        $daftarGudang = collect();

        $pemeriksaan = AlurLaporan::pemeriksaan($laporan);

        if (
            AlurLaporan::bolehCatatProgres($laporan, $akun)
            && $pemeriksaan?->rekomendasi === 'penggantian'
        ) {
            $daftarGudang = Ruangan::where('jenis_ruangan', 'gudang')
                ->orderBy('nama_ruangan')
                ->get();

            $pilihanPengganti = Inventaris::with('ruangan')
                ->whereNotIn('inventaris_id', [
                    $laporan->inventaris_id,
                    $inventarisSaatIni->inventaris_id,
                ])
                ->where('kondisi', 'baik')
                ->where('status_penggunaan', 'tersedia')
                ->whereHas('ruangan', function ($query) {
                    $query->where('jenis_ruangan', 'gudang');
                })
                ->whereDoesntHave('laporan', function ($query) {
                    $query->where('status_laporan', '!=', 'selesai');
                })
                ->orderBy('inventaris_id')
                ->get();
        }

        return view('laporan.show', compact(
            'laporan',
            'akun',
            'bisaUbahHapus',
            'inventarisSaatIni',
            'pilihanPengganti',
            'daftarGudang'
        ));
    }

    public function edit(LaporanKerusakan $laporan)
    {
        $akun = Auth::guard('pengguna')->user();
        abort_unless(AlurLaporan::bolehEdit($laporan, $akun), 403, 'Laporan hanya dapat diubah pelapor sebelum diproses.');

        return view('laporan.edit', compact('laporan', 'akun'));
    }

    public function update(Request $request, LaporanKerusakan $laporan)
    {
        $data = $request->validate(['kerusakan' => 'required|string|max:1000']);
        DB::transaction(function () use ($laporan, $data) {
            $laporan = LaporanKerusakan::whereKey($laporan->laporan_id)->lockForUpdate()->firstOrFail();
            abort_unless(AlurLaporan::bolehEdit($laporan, Auth::guard('pengguna')->user()), 403);
            $laporan->update($data);
        });

        return redirect()->route('laporan.show', $laporan)->with('sukses', 'Laporan berhasil diperbarui.');
    }

    public function destroy(LaporanKerusakan $laporan)
    {
        DB::transaction(function () use ($laporan) {
            $laporan = LaporanKerusakan::whereKey($laporan->laporan_id)->lockForUpdate()->firstOrFail();
            abort_unless(AlurLaporan::bolehEdit($laporan, Auth::guard('pengguna')->user()), 403);
            $laporan->delete();
        });

        return redirect()->route('laporan.index')->with('sukses', 'Laporan berhasil dihapus.');
    }

    public function isiPemeriksaan(Request $request, Pemeriksaan $pemeriksaan)
    {
        $akun = Auth::guard('pengguna')->user();
        abort_unless($akun->isPetugas() && (int) $pemeriksaan->petugas_id === (int) $akun->pengguna_id, 403);
        $data = $request->validate(['temuan' => 'required|string|max:1000', 'rekomendasi' => 'required|in:perbaikan,penggantian', 'sumber_pengganti' => 'nullable|required_if:rekomendasi,penggantian|in:stok_gudang,pengadaan']);
        DB::transaction(function () use ($pemeriksaan, $data, $akun) {
            $laporan = LaporanKerusakan::whereKey($pemeriksaan->laporan_id)->lockForUpdate()->firstOrFail();
            $pemeriksaan = $pemeriksaan->fresh();
            abort_unless(AlurLaporan::bolehIsi($laporan, $pemeriksaan, $akun), 422, 'Pemeriksaan ini bukan tahap aktif.');
            $pemeriksaan->update(['temuan' => $data['temuan'], 'rekomendasi' => $data['rekomendasi'],
                'sumber_pengganti' => $data['rekomendasi'] === 'penggantian' ? $data['sumber_pengganti'] : null,
                'status_pemeriksaan' => 'selesai', 'tanggal_pemeriksaan' => now(), 'status_persetujuan' => 'menunggu']);
            $laporan->update(['status_laporan' => 'diperiksa']);
        });

        return back()->with('sukses', 'Hasil pemeriksaan dikirim untuk ditinjau koordinator.');
    }

    public function tinjau(Request $request, Pemeriksaan $pemeriksaan)
    {
        abort_unless(Auth::guard('pengguna')->user()->isKoordinator(), 403);

        $data = $request->validate([
            'keputusan' => 'required|in:setuju,revisi,tolak',
            'prioritas' => 'nullable|in:tinggi,rendah',
            'catatan_koordinator' => 'nullable|required_if:keputusan,revisi,tolak|string|max:1000',
        ]);

        DB::transaction(function () use ($pemeriksaan, $data) {
            $laporan = LaporanKerusakan::whereKey($pemeriksaan->laporan_id)
                ->lockForUpdate()
                ->firstOrFail();
            $p = $pemeriksaan->fresh();

            abort_unless(
                $laporan->status_laporan === 'diperiksa'
                && AlurLaporan::pemeriksaan($laporan)?->pemeriksaan_id === $p->pemeriksaan_id
                && $p->status_pemeriksaan === 'selesai'
                && $p->status_persetujuan === 'menunggu',
                422,
                'Rencana bukan versi aktif yang menunggu tinjauan.'
            );

            $prioritasIsian = $data['prioritas'] ?? null;

            if (
                $laporan->prioritas !== null
                && $prioritasIsian !== null
                && $prioritasIsian !== $laporan->prioritas
            ) {
                throw ValidationException::withMessages([
                    'prioritas' => 'Prioritas laporan sudah ditetapkan dan tidak dapat diubah.',
                ]);
            }

            if (
                $data['keputusan'] === 'setuju'
                && $laporan->prioritas === null
                && $prioritasIsian === null
            ) {
                throw ValidationException::withMessages([
                    'prioritas' => 'Pilih prioritas Tinggi atau Rendah saat menyetujui rekomendasi.',
                ]);
            }

            $status = [
                'setuju' => 'disetujui',
                'revisi' => 'revisi',
                'tolak' => 'ditolak',
            ][$data['keputusan']];

            $p->update([
                'status_persetujuan' => $status,
                'catatan_koordinator' => $data['catatan_koordinator'] ?? null,
            ]);

            $perubahanLaporan = [
                'status_laporan' => $status === 'disetujui' ? 'disetujui' : 'diperiksa',
            ];

            if ($status === 'disetujui' && $laporan->prioritas === null) {
                $perubahanLaporan['prioritas'] = $prioritasIsian;
            }

            $laporan->update($perubahanLaporan);
        });

        return back()->with('sukses', 'Keputusan rencana disimpan. Revisi atau penolakan memerlukan penugasan pemeriksaan ulang.');
    }

    public function tutup(LaporanKerusakan $laporan)
    {
        abort_unless(Auth::guard('pengguna')->user()->isKoordinator(), 403);
        DB::transaction(function () use ($laporan) {
            $laporan = LaporanKerusakan::whereKey($laporan->laporan_id)->lockForUpdate()->firstOrFail();
            abort_unless(AlurLaporan::bolehTutup($laporan), 422, 'Hasil terakhir belum sesuai atau masih ada pekerjaan aktif.');
            $t = AlurLaporan::tindakan($laporan);
            if (AlurLaporan::pemeriksaan($laporan)->rekomendasi === 'perbaikan') {
                $barang = Inventaris::whereKey(
                    AlurLaporan::inventarisSaatIniId($laporan)
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                $ruanganBarang = $barang->ruangan()
                    ->lockForUpdate()
                    ->firstOrFail();

                $statusPenggunaan = 'digunakan';

                if ($ruanganBarang->jenis_ruangan === 'gudang') {
                    $statusPenggunaan = 'tidak_digunakan';
                }

                $barang->update([
                    'kondisi' => 'baik',
                    'status_penggunaan' => $statusPenggunaan,
                ]);
            } else {
                abort_unless($t->inventaris_pengganti_id && (int) $t->inventaris_pengganti_id !== (int) $laporan->inventaris_id, 422, 'Unit pengganti belum dicatat.');
                $pengganti = $t->inventarisPengganti()
                    ->lockForUpdate()
                    ->firstOrFail();

                $ruanganPengganti = $pengganti->ruangan()
                    ->lockForUpdate()
                    ->firstOrFail();

                $statusPenggantiSesuai = false;

                if ($ruanganPengganti->jenis_ruangan === 'gudang') {
                    $statusPenggantiSesuai = in_array(
                        $pengganti->status_penggunaan,
                        ['tersedia', 'tidak_digunakan'],
                        true
                    );
                } else {
                    $statusPenggantiSesuai =
                        $pengganti->status_penggunaan === 'digunakan';
                }

                abort_unless(
                    $pengganti->kondisi === 'baik'
                    && (int) $pengganti->ruangan_id === (int) $laporan->ruangan_id
                    && $statusPenggantiSesuai,
                    422,
                    'Kondisi, lokasi, atau status penggunaan barang pengganti belum sesuai.'
                );
            }
            $laporan->update(['status_laporan' => 'selesai', 'tanggal_ditutup' => now()]);
        });

        return back()->with('sukses','Laporan ditutup setelah hasil dikonfirmasi sesuai.');
    }
}
