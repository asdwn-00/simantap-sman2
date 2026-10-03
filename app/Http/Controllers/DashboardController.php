<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Services\AlurLaporan;
use App\Models\LaporanKerusakan;
use App\Models\Pemeriksaan;
use App\Models\PengajuanDana;
use App\Models\Pengguna;
use App\Models\Penindaklanjutan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private const TUGAS_AKTIF_PEMERIKSAAN = ['ditugaskan', 'berjalan'];
    private const TUGAS_AKTIF_TINDAKAN = ['ditugaskan', 'berjalan', 'terkendala'];

    public function index()
    {
        $pengguna = Auth::guard('pengguna')->user();

        return match (true) {
            $pengguna->isKoordinator() => $this->koordinator($pengguna),
            $pengguna->isPjLab() => $this->pjLab($pengguna),
            default => $this->petugas($pengguna),
        };
    }

    private function pjLab($pengguna)
    {
        $ruanganLabIds = $pengguna->ruanganLab()->pluck('ruangan_id');

        $laporanLab = LaporanKerusakan::terlihat($pengguna);

        $laporanTerbuka = (clone $laporanLab)->where('status_laporan', '!=', 'selesai')->count();
        $laporanSelesai = (clone $laporanLab)->where('status_laporan', 'selesai')->count();
        $totalLaporanLab = (clone $laporanLab)->count();

        $sedangDikerjakan = (clone $laporanLab)->where('status_laporan', 'ditangani')->count();

        $perluDikonfirmasi = Penindaklanjutan::where('status_tindakan', 'selesai')
            ->whereDoesntHave('konfirmasi')
            ->whereHas('pemeriksaan.laporan', fn ($q) => $q->where('pelapor_id', $pengguna->pengguna_id))
            ->with('pemeriksaan.laporan.inventaris', 'pemeriksaan.laporan.ruangan', 'petugas')
            ->get()->filter(fn ($t) => AlurLaporan::bolehKonfirmasi($t->pemeriksaan->laporan, $pengguna) && AlurLaporan::tindakan($t->pemeriksaan->laporan)?->penugasan_id === $t->penugasan_id);

        $daftarLaporan = (clone $laporanLab)
            ->where('status_laporan', '!=', 'selesai')
            ->with('inventaris', 'ruangan')
            ->latest('tanggal_laporan')
            ->take(5)
            ->get();

        $labs = $pengguna->ruanganLab()->orderBy('nama_ruangan')->get();
        $infoLab = $labs->map(function ($lab) {
            return (object) [
                'nama_ruangan' => $lab->nama_ruangan,
                'total' => Inventaris::where('ruangan_id', $lab->ruangan_id)->count(),
                'baik' => Inventaris::where('ruangan_id', $lab->ruangan_id)->where('kondisi', 'baik')->count(),
                'dalamPerbaikan' => Inventaris::where('ruangan_id', $lab->ruangan_id)
                    ->whereHas('laporan', fn ($q) => $q->where('status_laporan', '!=', 'selesai'))
                    ->count(),
            ];
        });

        $barangPerluPerhatian = Inventaris::whereIn('ruangan_id', $ruanganLabIds)
            ->where('kondisi', '!=', 'baik')
            ->whereDoesntHave('laporan', fn ($q) => $q->where('status_laporan', '!=', 'selesai'))
            ->get();

        return view('dashboard.pjlab', [
            'pengguna' => $pengguna,
            'sapaan' => $this->sapaanWaktu(),
            'laporanTerbuka' => $laporanTerbuka,
            'laporanSelesai' => $laporanSelesai,
            'totalLaporanLab' => $totalLaporanLab,
            'perluDikonfirmasi' => $perluDikonfirmasi,
            'daftarLaporan' => $daftarLaporan,
            'sedangDikerjakan' => $sedangDikerjakan,
            'infoLab' => $infoLab,
            'barangPerluPerhatian' => $barangPerluPerhatian,
        ]);
    }

    private function koordinator($pengguna)
    {
        $belumAdaPemeriksa = LaporanKerusakan::lengkap()->get()->filter(fn ($l) => AlurLaporan::bolehPeriksa($l))->count();

        $rekomendasiMenungguTinjauan = Pemeriksaan::where('status_persetujuan', 'menunggu')
            ->with('laporan.inventaris', 'laporan.ruangan')
            ->orderByDesc('tanggal_penugasan')
            ->get();

        $danaMenunggu = PengajuanDana::where('status_pengajuan', 'diajukan')->whereDoesntHave('pengajuanBerikutnya')
            ->with('pemeriksaan.laporan.inventaris', 'pembuat')
            ->orderByDesc('tanggal_dibuat')
            ->get();

        $siapDitutup = LaporanKerusakan::lengkap()->get()->filter(fn ($l) => AlurLaporan::bolehTutup($l));

        $bebanTugasPetugas = Pengguna::where('role', 'petugas')
            ->withCount([
                'pemeriksaanDitugaskan as tugas_pemeriksaan_aktif' => fn ($q) => $q->whereIn('status_pemeriksaan', self::TUGAS_AKTIF_PEMERIKSAAN),
                'penindaklanjutanDitugaskan as tugas_pelaksanaan_aktif' => fn ($q) => $q->whereIn('status_tindakan', self::TUGAS_AKTIF_TINDAKAN),
            ])
            ->get()
            ->map(function ($p) {
                $p->tugas_aktif = $p->tugas_pemeriksaan_aktif + $p->tugas_pelaksanaan_aktif;
                return $p;
            });

        return view('dashboard.koordinator', [
            'pengguna' => $pengguna,
            'sapaan' => $this->sapaanWaktu(),
            'belumAdaPemeriksa' => $belumAdaPemeriksa,
            'rekomendasiMenungguTinjauan' => $rekomendasiMenungguTinjauan,
            'danaMenunggu' => $danaMenunggu,
            'siapDitutup' => $siapDitutup,
            'bebanTugasPetugas' => $bebanTugasPetugas,
        ]);
    }

    private function petugas($pengguna)
    {
        $pemeriksaanAktif = Pemeriksaan::where('petugas_id', $pengguna->pengguna_id)
            ->whereIn('status_pemeriksaan', self::TUGAS_AKTIF_PEMERIKSAAN)
            ->with('laporan.inventaris', 'laporan.ruangan')
            ->get();

        $pemeriksaanMenungguReview = Pemeriksaan::where('petugas_id', $pengguna->pengguna_id)
            ->where('status_pemeriksaan', 'selesai')
            ->where('status_persetujuan', 'menunggu')
            ->with('laporan.inventaris', 'laporan.ruangan')
            ->get();

        $tugasPelaksanaan = Penindaklanjutan::where('petugas_id', $pengguna->pengguna_id)
            ->whereIn('status_tindakan', self::TUGAS_AKTIF_TINDAKAN)
            ->with('pemeriksaan.laporan.inventaris', 'pemeriksaan.laporan.ruangan')
            ->get();

        $tugasAktif = $pemeriksaanAktif->count() + $tugasPelaksanaan->count();

        $danaSaya = PengajuanDana::where('pembuat_id', $pengguna->pengguna_id)->whereDoesntHave('pengajuanBerikutnya')
            ->whereIn('status_pengajuan', ['diajukan', 'revisi'])
            ->orderByDesc('tanggal_dibuat')
            ->get();

        $danaDiajukan = $danaSaya->where('status_pengajuan', 'diajukan')->count();
        $danaRevisi = $danaSaya->where('status_pengajuan', 'revisi')->count();

        $perluDikonfirmasi = Penindaklanjutan::where('status_tindakan', 'selesai')
            ->whereDoesntHave('konfirmasi')
            ->whereHas('pemeriksaan.laporan', fn ($q) => $q->where('pelapor_id', $pengguna->pengguna_id))
            ->with('pemeriksaan.laporan.inventaris', 'pemeriksaan.laporan.ruangan', 'petugas')
            ->get()->filter(fn ($t) => AlurLaporan::bolehKonfirmasi($t->pemeriksaan->laporan, $pengguna) && AlurLaporan::tindakan($t->pemeriksaan->laporan)?->penugasan_id === $t->penugasan_id);

        $labelPemeriksaan = [
            'ditugaskan' => ['Baru Ditugaskan', config('simantap.warna_tugas.ditugaskan'), 'Isi Pemeriksaan'],
            'berjalan' => ['Sedang Diperiksa', config('simantap.warna_tugas.berjalan'), 'Isi Pemeriksaan'],
        ];
        $labelTindakan = [
            'ditugaskan' => ['Baru Ditugaskan', config('simantap.warna_tugas.ditugaskan'), 'Detail Tugas'],
            'berjalan' => ['Sedang Dikerjakan', config('simantap.warna_tugas.berjalan'), 'Detail Tugas'],
            'terkendala' => ['Terkendala', config('simantap.warna_tugas.terkendala'), 'Detail Tugas'],
        ];

        $daftarTugas = $pemeriksaanAktif->map(function ($t) use ($labelPemeriksaan) {
            [$label, $warna, $aksi] = $labelPemeriksaan[$t->status_pemeriksaan] ?? ['Aktif', 'bg-gray-100 text-gray-700', 'Detail Tugas'];
            return (object) [
                'jenis' => 'Pemeriksaan & Rekomendasi',
                'lokasi' => $t->laporan->ruangan->nama_ruangan ?? '-',
                'kasus' => \Illuminate\Support\Str::limit($t->laporan->kerusakan ?? '-', 40),
                'laporan_id' => $t->laporan_id,
                'label' => $label,
                'warna' => $warna,
                'aksi' => $aksi,
            ];
        })->concat($pemeriksaanMenungguReview->map(fn ($t) => (object) [
            'jenis' => 'Pemeriksaan & Rekomendasi',
            'lokasi' => $t->laporan->ruangan->nama_ruangan ?? '-',
            'kasus' => \Illuminate\Support\Str::limit($t->laporan->kerusakan ?? '-', 40),
            'laporan_id' => $t->laporan_id,
            'label' => 'Menunggu Persetujuan',
            'warna' => config('simantap.warna_laporan.diperiksa'),
            'aksi' => 'Detail Tugas',
        ]))->concat($tugasPelaksanaan->map(function ($t) use ($labelTindakan) {
            [$label, $warna, $aksi] = $labelTindakan[$t->status_tindakan] ?? ['Aktif', 'bg-gray-100 text-gray-700', 'Detail Tugas'];
            return (object) [
                'jenis' => 'Pelaksanaan Penanganan',
                'lokasi' => $t->pemeriksaan->laporan->ruangan->nama_ruangan ?? '-',
                'kasus' => \Illuminate\Support\Str::limit($t->pemeriksaan->laporan->kerusakan ?? '-', 40),
                'laporan_id' => $t->pemeriksaan->laporan_id ?? null,
                'label' => $label,
                'warna' => $warna,
                'aksi' => $aksi,
            ];
        }));

        return view('dashboard.petugas', [
            'pengguna' => $pengguna,
            'sapaan' => $this->sapaanWaktu(),
            'tugasAktif' => $tugasAktif,
            'danaDiajukan' => $danaDiajukan,
            'danaRevisi' => $danaRevisi,
            'perluDikonfirmasi' => $perluDikonfirmasi,
            'daftarTugas' => $daftarTugas,
            'danaSaya' => $danaSaya,
        ]);
    }

    private function sapaanWaktu(): string
    {
        $jam = now()->hour;

        return match (true) {
            $jam < 11 => 'Selamat Pagi',
            $jam < 15 => 'Selamat Siang',
            $jam < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };
    }
}
