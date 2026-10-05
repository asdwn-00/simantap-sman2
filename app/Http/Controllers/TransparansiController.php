<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanKerusakan;
use App\Services\AlurLaporan;

class TransparansiController extends Controller
{

    private const TAHAPAN = ['masuk', 'diperiksa', 'disetujui', 'ditangani', 'selesai'];

    private const BADGE = ['masuk' => ['Masuk', 'slate'], 'diperiksa' => ['Diperiksa', 'amber'], 'disetujui' => ['Disetujui', 'amber'], 'ditangani' => ['Ditangani', 'blue'], 'selesai' => ['Selesai', 'emerald'], 'dihentikan' => ['Dihentikan', 'red']];

    public function index(\Illuminate\Http\Request $request)
    {
        if (Auth::guard('pengguna')->check()) {
            return redirect()->route('dashboard');
        }

        $laporan = DB::table('laporan_kerusakan as l')
            ->when($request->filled('cari'), fn ($q) => $q->whereIn('l.laporan_id', LaporanKerusakan::filter($request)->pluck('laporan_id')))
            ->join('inventaris as i', 'i.inventaris_id', '=', 'l.inventaris_id')
            ->join('ruangan as r', 'r.ruangan_id', '=', 'l.ruangan_id')
            ->orderByDesc('l.tanggal_laporan')
            ->select('l.laporan_id', 'l.kerusakan', 'l.prioritas', 'l.status_laporan', 'l.tanggal_laporan', 'i.nama_barang', 'r.nama_ruangan')
            ->paginate(9);

        $laporan->through(fn ($l) => $this->lengkapiRingkasan($l));

        return view('transparansi.index', ['laporan' => $laporan]);
    }

    public function show(int $laporan)
    {
        $l = DB::table('laporan_kerusakan as l')
            ->join('inventaris as i', 'i.inventaris_id', '=', 'l.inventaris_id')
            ->join('ruangan as r', 'r.ruangan_id', '=', 'l.ruangan_id')
            ->where('l.laporan_id', $laporan)
            ->select('l.*', 'i.nama_barang', 'r.nama_ruangan')
            ->first();

        abort_if(! $l, 404);

        foreach (['nama_barang', 'nama_ruangan', 'kerusakan'] as $kolom) {
            $l->$kolom = trim(str_ireplace(['(Dummy)', '[DUMMY]'], '', $l->$kolom ?? ''));
        }

        [$label, $warna] = self::BADGE[$l->status_laporan] ?? ['-', 'slate'];
        [$doneNodes, $persenSelesai] = $this->hitungProgres($l->status_laporan);

        $pemeriksaan = DB::table('pemeriksaan')
            ->where('laporan_id', $laporan)
            ->orderByDesc('pemeriksaan_id')
            ->get();

        $penindaklanjutan = DB::table('penindaklanjutan as t')
            ->join('pemeriksaan as p', 'p.pemeriksaan_id', '=', 't.pemeriksaan_id')
            ->where('p.laporan_id', $laporan)
            ->orderByDesc('t.penugasan_id')
            ->select('t.*')
            ->get();

        $konfirmasi = DB::table('konfirmasi_hasil as k')
            ->join('penindaklanjutan as t', 't.penugasan_id', '=', 'k.penugasan_id')
            ->join('pemeriksaan as p', 'p.pemeriksaan_id', '=', 't.pemeriksaan_id')
            ->where('p.laporan_id', $laporan)
            ->orderByDesc('k.konfirmasi_id')
            ->select('k.*')
            ->get();

        $pemeriksaanTerbaru = $pemeriksaan->first(fn ($p) => $p->status_pemeriksaan !== 'dibatalkan');

        $pembaruan = $this->susunLini($l, $pemeriksaan, $penindaklanjutan, $konfirmasi);

        return view('transparansi.show', [
            'l' => $l,
            'label' => $label,
            'warna' => $warna,
            'doneNodes' => $doneNodes,
            'persenSelesai' => $persenSelesai,
            'pemeriksaanTerbaru' => $pemeriksaanTerbaru,
            'pembaruan' => $pembaruan,
        ]);
    }

    private function lengkapiRingkasan(object $l): object
    {
        foreach (['nama_barang', 'nama_ruangan', 'kerusakan'] as $kolom) {
            $l->$kolom = trim(str_ireplace(['(Dummy)', '[DUMMY]'], '', $l->$kolom ?? ''));
        }
        [$label, $warna] = self::BADGE[$l->status_laporan] ?? ['-', 'slate'];
        [, $persen] = $this->hitungProgres($l->status_laporan);

        $l->label_status = $label;
        $l->warna_status = $warna;
        $l->persen = $persen;

        $model = LaporanKerusakan::lengkap()->findOrFail($l->laporan_id);
        $l->judul_ringkasan = match ($l->status_laporan) {
            'selesai' => 'Hasil Penanganan',
            'dihentikan' => 'Alasan Penghentian',
            default => 'Kendala / Progress',
        };
        $t = AlurLaporan::tindakan($model);
        $l->teks_ringkasan = $l->status_laporan === 'selesai'
            ? ($t?->hasil ?: AlurLaporan::keterangan($model))
            : AlurLaporan::keterangan($model).($t?->kendala ? '. '.$t->kendala : '');

        return $l;
    }

    private function hitungProgres(string $status): array
    {
        if ($status === 'dihentikan') {
            return [0, null];
        }
        $persen = config('simantap.status')[$status] ?? 0;
        return [(int) ($persen / 25) + 1, $persen];
    }

    private function susunLini(object $l, $pemeriksaan, $penindaklanjutan, $konfirmasi): array
    {
        $lini = [[
            'tanggal' => $l->tanggal_laporan,
            'judul' => 'Laporan Diterima',
            'teks' => 'Laporan kerusakan masuk ke sistem dan menunggu penugasan petugas pemeriksa.',
        ]];

        foreach ($pemeriksaan as $p) {
            if ($p->tanggal_pemeriksaan) {
                $teks = $p->temuan ?: 'Pemeriksaan selesai dilakukan.';
                if ($p->rekomendasi) {
                    $teks .= ' Rekomendasi: ' . $p->label_rekomendasi . '.';
                }
                $lini[] = ['tanggal' => $p->tanggal_pemeriksaan, 'judul' => 'Pemeriksaan Selesai', 'teks' => $teks];
            } else {
                $lini[] = ['tanggal' => $p->tanggal_penugasan, 'judul' => 'Pemeriksaan Ditugaskan', 'teks' => 'Petugas sedang melakukan pemeriksaan lapangan.'];
            }
        }

        foreach ($penindaklanjutan as $t) {
            if (! $t->tanggal_mulai) {
                continue;
            }

            $teks = $t->hasil ?: ($t->catatan_tindakan ?: 'Pekerjaan sedang berjalan.');
            if ($t->kendala) {
                $teks .= ' Kendala: ' . $t->kendala;
            }
            $judul = match ($t->status_tindakan) {
                'selesai' => 'Penanganan Selesai',
                'terkendala' => 'Penanganan Terkendala',
                'ditugaskan' => 'Pelaksanaan Ditugaskan',
                default => 'Penanganan Berjalan',
            };
            $lini[] = ['tanggal' => $t->tanggal_mulai, 'judul' => $judul, 'teks' => $teks];
        }

        foreach ($konfirmasi as $k) {
            $lini[] = [
                'tanggal' => $k->tanggal_konfirmasi,
                'judul' => $k->hasil_konfirmasi === 'sesuai' ? 'Dikonfirmasi Selesai oleh Pelapor' : 'Pelapor Menyatakan Masih Bermasalah',
                'teks' => $k->catatan ?: '-',
            ];
        }

        if ($l->status_laporan === 'dihentikan') {
            $lini[] = ['tanggal' => $l->tanggal_ditutup, 'judul' => 'Laporan Dihentikan', 'teks' => $l->alasan_penghentian];
        } elseif ($l->tanggal_ditutup) {
            $lini[] = ['tanggal' => $l->tanggal_ditutup, 'judul' => 'Laporan Ditutup', 'teks' => 'Laporan dinyatakan selesai oleh koordinator.'];
        }

        usort($lini, fn ($a, $b) => strcmp((string) $b['tanggal'], (string) $a['tanggal']));

        return $lini;
    }
}
