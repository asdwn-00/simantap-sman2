<?php

namespace Tests\Feature;

use App\Models\Inventaris;
use App\Models\LaporanKerusakan;
use App\Models\Pemeriksaan;
use App\Models\PengajuanDana;
use App\Models\Pengguna;
use App\Models\Penindaklanjutan;
use App\Services\AlurLaporan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AlurRekomendasiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('simantap_alur_test_20261004', DB::connection()->getDatabaseName());
        $this->withoutVite();
    }

    private function sebagai(int $id): void
    {
        $this->actingAs(Pengguna::findOrFail($id), 'pengguna');
    }

    private function pemeriksaanBaru(): Pemeriksaan
    {
        $laporan = LaporanKerusakan::create([
            'pelapor_id' => 1, 'inventaris_id' => 1, 'ruangan_id' => 1,
            'kerusakan' => 'Layar neraca mati', 'tanggal_laporan' => now(), 'status_laporan' => 'diperiksa',
        ]);
        return Pemeriksaan::create([
            'laporan_id' => $laporan->laporan_id, 'petugas_id' => 4,
            'tanggal_penugasan' => now(), 'status_pemeriksaan' => 'ditugaskan',
            'status_persetujuan' => 'belum_diajukan',
        ]);
    }

    private function rekomendasiMenunggu(): Pemeriksaan
    {
        $p = $this->pemeriksaanBaru();
        $p->update([
            'status_pemeriksaan' => 'selesai', 'status_persetujuan' => 'menunggu',
            'temuan' => 'Komponen utama rusak', 'rekomendasi' => 'penggantian',
            'sumber_pengganti' => 'pengadaan', 'alasan_penggantian' => 'Komponen tidak dapat diperbaiki.',
            'tanggal_pemeriksaan' => now(),
        ]);
        return $p;
    }

    public function test_penggantian_wajib_alasan_dan_petugas_yang_ditugaskan(): void
    {
        $p = $this->pemeriksaanBaru();
        $data = ['temuan' => 'Komponen utama rusak', 'rekomendasi' => 'penggantian', 'sumber_pengganti' => 'pengadaan'];
        $this->sebagai(5);
        $this->post(route('laporan.isi-pemeriksaan', $p), $data)->assertForbidden();
        $this->sebagai(4);
        $this->post(route('laporan.isi-pemeriksaan', $p), $data)->assertSessionHasErrors('alasan_penggantian');
        $this->assertSame('belum_diajukan', $p->fresh()->status_persetujuan);
        $this->post(route('laporan.isi-pemeriksaan', $p), $data + ['alasan_penggantian' => 'Komponen tidak dapat diperbaiki.'])->assertRedirect();
        $this->assertSame('menunggu', $p->fresh()->status_persetujuan);
        $this->assertSame('Komponen tidak dapat diperbaiki.', $p->fresh()->alasan_penggantian);
    }

    public function test_revisi_memakai_pemeriksaan_dan_petugas_yang_sama(): void
    {
        $p = $this->rekomendasiMenunggu();
        $id = $p->laporan_id;
        $this->sebagai(6);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'revisi'])->assertSessionHasErrors('catatan_koordinator');
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'revisi', 'catatan_koordinator' => 'Periksa konektor dahulu'])->assertRedirect();
        $this->post('/penugasan/'.$id.'/pemeriksaan', ['petugas_id' => 5])->assertUnprocessable();
        $this->get('/penugasan')->assertOk()->assertSee('Revisi');
        $this->sebagai(4);
        $this->get('/dashboard')->assertOk()->assertSee('Perlu Revisi');
        $this->get('/laporan?cari=LAP-'.$id)->assertOk()->assertSee('Periksa konektor dahulu')->assertSee('Komponen utama rusak')->assertSee('Komponen tidak dapat diperbaiki.');
        $this->post(route('laporan.isi-pemeriksaan', $p), ['temuan' => 'Konektor longgar', 'rekomendasi' => 'perbaikan'])->assertRedirect();
        $this->assertSame(1, Pemeriksaan::where('laporan_id', $id)->count());
        $this->assertSame(4, $p->fresh()->petugas_id);
        $this->assertSame('menunggu', $p->fresh()->status_persetujuan);
        $this->assertNull($p->fresh()->alasan_penggantian);
        $this->assertNull($p->fresh()->sumber_pengganti);
        $this->sebagai(6);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'setuju'])->assertSessionHasErrors('prioritas');
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'setuju', 'prioritas' => 'tinggi'])->assertRedirect();
        $this->assertSame('disetujui', $p->laporan->fresh()->status_laporan);
    }

    public function test_hentikan_bukan_selesai_dan_tidak_mengubah_barang(): void
    {
        $p = $this->rekomendasiMenunggu();
        $barang = Inventaris::find(1)->getAttributes();
        $this->sebagai(4);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'hentikan', 'catatan_koordinator' => 'Tidak dilanjutkan'])->assertForbidden();
        $this->sebagai(6);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'hentikan'])->assertSessionHasErrors('catatan_koordinator');
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'hentikan', 'catatan_koordinator' => 'Penggantian belum dapat dibiayai.'])->assertRedirect();
        $l = $p->laporan->fresh();
        $this->assertSame('dihentikan', $l->status_laporan);
        $this->assertSame('dihentikan', $p->fresh()->status_persetujuan);
        $this->assertSame('Penggantian belum dapat dibiayai.', $l->alasan_penghentian);
        $this->assertNotNull($l->tanggal_ditutup);
        $this->assertNull($l->persentase);
        $this->assertSame($barang, Inventaris::find(1)->getAttributes());
        $this->assertFalse(AlurLaporan::bolehPeriksa($l));
        $this->assertFalse(AlurLaporan::bolehLaksana($l));
        $this->assertFalse(AlurLaporan::bolehTutup($l));
        $this->assertFalse(AlurLaporan::bolehKonfirmasi($l, Pengguna::find(1)));
        $this->post('/penugasan/'.$l->laporan_id.'/pemeriksaan', ['petugas_id' => 4])->assertUnprocessable();
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'setuju', 'prioritas' => 'tinggi'])->assertUnprocessable();
        $this->post(route('penugasan.tugaskan-pelaksanaan', $p), ['petugas_id' => 4])->assertUnprocessable();
        $this->put(route('laporan.tutup', $l))->assertUnprocessable();
        foreach ([1, 4, 6] as $akun) {
            $this->sebagai($akun);
            $this->get('/laporan/'.$l->laporan_id)->assertOk()->assertSee('Penggantian belum dapat dibiayai.');
            $this->get('/laporan?status=dihentikan')->assertOk()->assertSee('Penggantian belum dapat dibiayai.');
            $this->get('/dashboard')->assertOk()->assertSee('Laporan dihentikan: 1');
        }
        $this->sebagai(1);
        $this->get('/dashboard')->assertViewHas('laporanTerbuka', 2)->assertViewHas('laporanSelesai', 0);
        $this->sebagai(4);
        $this->post(route('dana.store'), ['pemeriksaan_id' => $p->pemeriksaan_id, 'rincian_kebutuhan' => 'Barang baru', 'estimasi_biaya' => 100])->assertSessionHasErrors('pemeriksaan_id', null, 'buatDana');
        $this->post(route('laporan.isi-pemeriksaan', $p), ['temuan' => 'Perbaikan', 'rekomendasi' => 'perbaikan'])->assertUnprocessable();
        $this->get('/transparansi/'.$l->laporan_id)->assertOk()->assertSee('Penggantian belum dapat dibiayai.')->assertDontSee('100%')->assertDontSee('Laporan dinyatakan selesai oleh koordinator.');
    }

    public function test_hentikan_dilarang_setelah_dana_dan_pelaksanaan(): void
    {
        $this->sebagai(6);
        $this->post('/laporan/pemeriksaan/3/tinjau', ['keputusan' => 'hentikan', 'catatan_koordinator' => 'Tidak dilanjutkan'])->assertUnprocessable();
        $p = $this->rekomendasiMenunggu();
        PengajuanDana::create(['pemeriksaan_id' => $p->pemeriksaan_id, 'pembuat_id' => 4, 'rincian_kebutuhan' => 'Konflik dana', 'estimasi_biaya' => 100, 'status_pengajuan' => 'diajukan', 'tanggal_dibuat' => now()]);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'hentikan', 'catatan_koordinator' => 'Tidak dilanjutkan'])->assertUnprocessable();
        $this->assertSame('diperiksa', $p->laporan->fresh()->status_laporan);
        $p2 = $this->rekomendasiMenunggu();
        Penindaklanjutan::create(['pemeriksaan_id' => $p2->pemeriksaan_id, 'petugas_id' => 4, 'status_tindakan' => 'ditugaskan']);
        $this->post(route('laporan.tinjau', $p2), ['keputusan' => 'hentikan', 'catatan_koordinator' => 'Tidak dilanjutkan'])->assertUnprocessable();
    }

    public function test_dana_revisi_berulang_dan_tolak_diblokir_server(): void
    {
        $this->sebagai(6);
        $this->post('/pengajuan-dana/2/keputusan', ['keputusan' => 'ditolak', 'catatan' => 'Tidak'])->assertSessionHasErrors('keputusan', null, 'keputusanDana2');
        $this->assertSame('diajukan', PengajuanDana::find(2)->status_pengajuan);
        $this->get('/pengajuan-dana')->assertOk()->assertDontSee('value="ditolak"', false);
        $danaId = 2;
        for ($putaran = 0; $putaran < 2; $putaran++) {
            $this->sebagai(6);
            $this->post('/pengajuan-dana/'.$danaId.'/keputusan', ['keputusan' => 'revisi', 'catatan' => 'Sesuaikan rincian biaya'])->assertRedirect();
            $this->sebagai(4);
            $data = ['rincian_kebutuhan' => 'Servis dan transportasi', 'estimasi_biaya' => 300000];
            $this->post('/pengajuan-dana/'.$danaId.'/revisi', $data)->assertForbidden();
            $this->sebagai(5);
            $this->post('/pengajuan-dana/'.$danaId.'/revisi', $data)->assertRedirect();
            $this->post('/pengajuan-dana/'.$danaId.'/revisi', $data)->assertSessionHasErrors('pengajuan', null, 'revisiDana'.$danaId);
            $baru = PengajuanDana::where('pengajuan_sebelumnya_id', $danaId)->sole();
            $this->assertSame(2, $baru->pemeriksaan_id);
            $this->assertSame('Sesuaikan rincian biaya', PengajuanDana::find($danaId)->catatan);
            $danaId = $baru->pengajuan_id;
        }
        $this->sebagai(6);
        $this->post('/pengajuan-dana/'.$danaId.'/keputusan', ['keputusan' => 'disetujui'])->assertRedirect();
        $this->assertSame('disetujui', PengajuanDana::find($danaId)->status_pengajuan);
    }

    public function test_konfirmasi_bermasalah_membuat_pemeriksaan_baru(): void
    {
        $this->sebagai(5);
        $this->post('/konfirmasi/2', ['hasil_konfirmasi' => 'masih_bermasalah', 'catatan' => 'Masih longgar'])->assertRedirect();
        $this->sebagai(6);
        $this->post('/penugasan/6/pemeriksaan', ['petugas_id' => 4])->assertRedirect();
        $this->assertSame(2, Pemeriksaan::where('laporan_id', 6)->count());
        $this->assertDatabaseHas('konfirmasi_hasil', ['penugasan_id' => 2, 'hasil_konfirmasi' => 'masih_bermasalah']);
        $p = Pemeriksaan::where('laporan_id', 6)->latest('pemeriksaan_id')->first();
        $this->sebagai(4);
        $this->post(route('laporan.isi-pemeriksaan', $p), ['temuan' => 'Konektor perlu dikencangkan', 'rekomendasi' => 'perbaikan'])->assertRedirect();
        $this->sebagai(6);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'setuju', 'prioritas' => 'rendah'])->assertSessionHasErrors('prioritas');
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'setuju'])->assertRedirect();
        $this->assertSame('tinggi', LaporanKerusakan::find(6)->prioritas);
    }

    public function test_unit_pengganti_tetap_acuan_pemeriksaan_ulang(): void
    {
        DB::table('laporan_kerusakan')->where('laporan_id', 7)->update(['status_laporan' => 'diperiksa', 'tanggal_ditutup' => null]);
        DB::table('konfirmasi_hasil')->where('penugasan_id', 3)->update(['hasil_konfirmasi' => 'masih_bermasalah']);
        $l = LaporanKerusakan::find(7);
        $this->assertSame(8, AlurLaporan::inventarisSaatIniId($l));
        $this->sebagai(6);
        $this->post('/penugasan/7/pemeriksaan', ['petugas_id' => 5])->assertRedirect();
        $this->assertSame(8, AlurLaporan::inventarisSaatIniId($l->fresh()));
        $this->get('/laporan/7')->assertOk()->assertSee('Kipas Pengganti Kelas X-6');
    }

    public function test_halaman_semua_role_dan_ruang_lingkup(): void
    {
        foreach ([1, 4, 6] as $id) {
            $this->sebagai($id);
            foreach (['/dashboard', '/laporan', '/penugasan', '/inventaris', '/pengajuan-dana'] as $url) {
                $this->get($url)->assertOk();
            }
        }
        $this->sebagai(1);
        $this->get('/laporan/3')->assertForbidden();
        $this->get('/laporan?status=dihentikan')->assertOk()->assertSee('Belum ada laporan');
    }

    public function test_persetujuan_penggantian_lama_tanpa_alasan_memerlukan_revisi(): void
    {
        $p = $this->rekomendasiMenunggu();
        $p->update(['alasan_penggantian' => null]);
        $this->sebagai(6);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'setuju', 'prioritas' => 'tinggi'])->assertSessionHasErrors('rekomendasi');
        $this->assertSame('menunggu', $p->fresh()->status_persetujuan);
        $this->assertNull($p->laporan->fresh()->prioritas);
        $this->post(route('laporan.tinjau', $p), ['keputusan' => 'tolak', 'catatan_koordinator' => 'Tolak'])->assertSessionHasErrors('keputusan');
    }
}
