<?php

namespace Tests\Feature;

use App\Models\LaporanKerusakan;
use App\Models\Pemeriksaan;
use App\Models\Pengguna;
use App\Models\Penindaklanjutan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SimantapTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('simantap_test', DB::connection()->getDatabaseName(), 'Tests only run on the separate test database.');
        $this->withoutVite();
    }

    private function loginAs(int $id): void
    {
        $this->actingAs(Pengguna::findOrFail($id), 'pengguna');
    }

    public static function pages(): array
    {
        $cases = [];
        foreach ([1, 4, 6] as $id) {
            foreach (['/', '/dashboard', '/laporan', '/penugasan', '/inventaris', '/pengajuan-dana'] as $page) {
                $cases["$id $page"] = [$id, $page];
            }
        }

        return $cases;
    }

    #[DataProvider('pages')]
    public function test_pages_for_each_role(int $id, string $url): void
    {
        $this->loginAs($id);
        $this->get($url)->assertOk()->assertSee('SIMAN')->assertDontSee('href="#"', false)->assertDontSee('Profil Akun');
    }

    public function test_guests_login_and_public_privacy(): void
    {
        $home = $this->get('/')->assertOk()->assertSee('images/smada-pic.png', false)->assertSee('Login Sistem');
        foreach (['/dashboard', '/laporan', '/penugasan', '/inventaris', '/pengajuan-dana'] as $url) {
            $home->assertDontSee('href="'.url($url).'"', false);
        }
        $this->assertFileExists(public_path('images/smada-pic.png'));
        foreach (['/', '/login', '/transparansi/7'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/transparansi/4')->assertDontSee('example.test')->assertDontSee('4.500.000')->assertDontSee('Pembelian satu unit');
        foreach (['/dashboard', '/laporan', '/penugasan', '/inventaris', '/pengajuan-dana', '/laporan/1'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->get('/inventaris')->assertRedirect('/login');
        $this->post('/login', ['email' => 'pj.kimia@example.test', 'password' => 'SimantapDev123!'])->assertRedirect('/inventaris');
        $this->assertAuthenticatedAs(Pengguna::find(1), 'pengguna');
        $this->post('/logout')->assertRedirect('/login');
        $this->post('/login', ['email' => 'pj.kimia@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->get('/akun')->assertNotFound();
    }

    public function test_private_scope_is_enforced(): void
    {
        $this->loginAs(1);
        $this->get('/laporan/3')->assertForbidden();
        $this->get('/laporan/1')->assertOk();
        $this->get('/inventaris')->assertSee('Neraca Digital')->assertDontSee('Proyektor Kelas X-6');
        $this->get('/penugasan')->assertDontSee('Stopkontak Aula');
        $this->get('/pengajuan-dana')->assertDontSee('Pembelian satu unit proyektor');
        $this->post('/laporan', ['inventaris_id' => 4, 'kerusakan' => 'Di luar lab'])->assertForbidden();
        $this->post('/penugasan/1/pemeriksaan', ['petugas_id' => 4])->assertForbidden();
        $this->put('/inventaris/1/pj', ['penanggung_id' => 2])->assertForbidden();
        $this->loginAs(4);
        $this->get('/laporan/1')->assertForbidden();
        $this->get('/laporan/2')->assertOk();
        $this->get('/pengajuan-dana')->assertDontSee('Servis pengatur fokus');
    }

    public function test_report_crud_and_spoofed_identity(): void
    {
        $this->loginAs(1);
        $this->get('/laporan/buat')->assertOk();
        $this->get('/laporan/buat?inventaris=1')->assertOk()
            ->assertSee('value="1" data-ruangan="1"', false)
            ->assertDontSee('Proyektor Kelas X-6');
        $this->post('/laporan', ['inventaris_id' => 1, 'kerusakan' => 'Layar kosong', 'pelapor_id' => 6, 'status_laporan' => 'selesai', 'ruangan_id' => 7])->assertRedirect();
        $l = LaporanKerusakan::latest('laporan_id')->first();
        $this->assertSame(1, $l->pelapor_id);
        $this->assertSame(1, $l->ruangan_id);
        $this->assertSame('masuk', $l->status_laporan);
        $this->get('/laporan/'.$l->laporan_id.'/ubah')->assertOk();
        $this->put('/laporan/'.$l->laporan_id, ['kerusakan' => 'Kabel putus'])->assertRedirect();
        $this->assertSame('Kabel putus', $l->fresh()->kerusakan);
        $this->delete('/laporan/'.$l->laporan_id)->assertRedirect('/laporan');
        $this->assertDatabaseMissing('laporan_kerusakan', ['laporan_id' => $l->laporan_id]);
        $this->delete('/laporan/8')->assertForbidden();
        $this->loginAs(6);
        $this->post('/laporan', ['inventaris_id' => 1, 'kerusakan' => 'x'])->assertForbidden();
    }

    public function test_assign_inspect_review_revise_and_reassign(): void
    {
        $this->loginAs(6);
        $this->post('/penugasan/1/pemeriksaan', ['petugas_id' => 1])->assertSessionHasErrors('petugas_id');
        $this->post('/penugasan/1/pemeriksaan', ['petugas_id' => 4])->assertRedirect();
        $p = Pemeriksaan::where('laporan_id', 1)->first();
        $this->assertSame(4, $p->petugas_id);
        $this->assertDatabaseHas('laporan_kerusakan', ['laporan_id' => 1, 'status_laporan' => 'diperiksa']);
        $this->post('/penugasan/1/pemeriksaan', ['petugas_id' => 5])->assertUnprocessable();
        $this->loginAs(5);
        $this->post('/laporan/pemeriksaan/'.$p->pemeriksaan_id.'/isi', ['temuan' => 'x', 'rekomendasi' => 'perbaikan'])->assertForbidden();
        $this->loginAs(4);
        $this->get('/laporan/1')->assertOk()->assertSee('Isi Pemeriksaan');
        $this->get('/laporan?cari=LAP-001&buka=modalPemeriksaan-1')->assertOk()->assertSee('Hasil Pemeriksaan & Rekomendasi', false)->assertSee(route('laporan.isi-pemeriksaan', $p->pemeriksaan_id), false);
        $this->post('/laporan/pemeriksaan/'.$p->pemeriksaan_id.'/isi', ['temuan' => 'Kabel rusak', 'rekomendasi' => 'perbaikan'])->assertRedirect();
        $this->assertSame('menunggu', $p->fresh()->status_persetujuan);
        $this->loginAs(6);
        $this->get('/laporan/1')->assertOk()->assertSee('Tinjau Rekomendasi');
        $this->get('/laporan?cari=LAP-001&buka=modalTinjau-1')->assertOk()->assertSee(route('laporan.tinjau', $p->pemeriksaan_id), false);
        $this->post('/laporan/pemeriksaan/'.$p->pemeriksaan_id.'/tinjau', ['keputusan' => 'revisi', 'catatan_koordinator' => 'Periksa konektor'])->assertRedirect();
        $this->post('/penugasan/1/pemeriksaan', ['petugas_id' => 5])->assertRedirect();
        $baru = Pemeriksaan::where('laporan_id', 1)->latest('pemeriksaan_id')->first();
        $this->assertNotSame($p->pemeriksaan_id, $baru->pemeriksaan_id);
        $this->post('/laporan/pemeriksaan/'.$p->pemeriksaan_id.'/tinjau', ['keputusan' => 'setuju', 'prioritas' => 'tinggi'])->assertUnprocessable();
        $this->loginAs(5);
        $this->post('/laporan/pemeriksaan/'.$baru->pemeriksaan_id.'/isi', ['temuan' => 'Konektor putus', 'rekomendasi' => 'perbaikan'])->assertRedirect();
        $this->loginAs(6);
        $this->post('/laporan/pemeriksaan/'.$baru->pemeriksaan_id.'/tinjau', ['keputusan' => 'setuju', 'prioritas' => 'sedang'])->assertSessionHasErrors('prioritas');
        $this->post('/laporan/pemeriksaan/'.$baru->pemeriksaan_id.'/tinjau', ['keputusan' => 'setuju', 'prioritas' => 'tinggi'])->assertRedirect();
        $this->assertDatabaseHas('laporan_kerusakan', ['laporan_id' => 1, 'status_laporan' => 'disetujui', 'prioritas' => 'tinggi']);
    }

    public function test_execution_waits_for_funds_and_assignment_is_not_start(): void
    {
        $this->loginAs(6);
        $this->post('/penugasan/pemeriksaan/2/pelaksanaan', ['petugas_id' => 4])->assertUnprocessable();
        DB::table('pengajuan_dana')->where('pengajuan_id', 3)->update(['status_pengajuan' => 'diajukan']);
        $this->post('/penugasan/pemeriksaan/3/pelaksanaan', ['petugas_id' => 4])->assertUnprocessable();
        DB::table('pengajuan_dana')->where('pengajuan_id', 3)->delete();
        $this->post('/penugasan/pemeriksaan/3/pelaksanaan', ['petugas_id' => 4])->assertUnprocessable();
        DB::table('pengajuan_dana')->insert(['pengajuan_id' => 3, 'pemeriksaan_id' => 3, 'pembuat_id' => 4, 'koordinator_id' => 6, 'rincian_kebutuhan' => 'Pengganti', 'estimasi_biaya' => 100, 'status_pengajuan' => 'disetujui']);
        $this->post('/penugasan/pemeriksaan/3/pelaksanaan', ['petugas_id' => 4])->assertRedirect();
        $t = Penindaklanjutan::where('pemeriksaan_id', 3)->first();
        $this->assertNull($t->tanggal_mulai);
        $this->assertSame(3, $t->pengajuan_id);
        $this->assertSame('disetujui', LaporanKerusakan::find(4)->status_laporan);
        $this->post('/penugasan/pemeriksaan/3/pelaksanaan', ['petugas_id' => 5])->assertUnprocessable();
    }

    public function test_successful_confirmation_and_close(): void
    {
        $this->loginAs(4);
        $this->post('/konfirmasi/2', ['hasil_konfirmasi' => 'sesuai'])->assertForbidden();
        $this->loginAs(6);
        $this->put('/laporan/6/tutup')->assertUnprocessable();
        $this->loginAs(5);
        $this->get('/dashboard')->assertOk()->assertSee('Konfirmasi Hasil');
        $this->post('/konfirmasi/2', ['hasil_konfirmasi' => 'sesuai'])->assertRedirect();
        $this->post('/konfirmasi/2', ['hasil_konfirmasi' => 'sesuai'])->assertUnprocessable();
        $this->loginAs(6);
        $this->put('/laporan/6/tutup')->assertRedirect();
        $this->assertSame('selesai', LaporanKerusakan::find(6)->status_laporan);
        $this->assertNotNull(LaporanKerusakan::find(6)->tanggal_ditutup);
        $this->put('/laporan/6/tutup')->assertUnprocessable();
    }

    public function test_failed_confirmation_returns_to_inspection(): void
    {
        $this->loginAs(5);
        $this->post('/konfirmasi/2', ['hasil_konfirmasi' => 'masih_bermasalah'])->assertSessionHasErrors('catatan');
        $this->post('/konfirmasi/2', ['hasil_konfirmasi' => 'masih_bermasalah', 'catatan' => 'Masih longgar'])->assertRedirect();
        $this->assertSame('diperiksa', LaporanKerusakan::find(6)->status_laporan);
        $this->loginAs(6);
        $this->put('/laporan/6/tutup')->assertUnprocessable();
        $this->post('/penugasan/6/pemeriksaan', ['petugas_id' => 4])->assertRedirect();
        $this->assertSame(2, Pemeriksaan::where('laporan_id', 6)->count());
    }

    public function test_stale_confirmation_cannot_close_new_work(): void
    {
        $this->loginAs(1);
        $this->post('/konfirmasi/4', ['hasil_konfirmasi' => 'sesuai'])->assertUnprocessable();
        DB::table('konfirmasi_hasil')->where('penugasan_id', 4)->update(['hasil_konfirmasi' => 'sesuai']);
        DB::table('laporan_kerusakan')->where('laporan_id', 8)->update(['status_laporan' => 'ditangani']);
        $this->loginAs(6);
        $this->put('/laporan/8/tutup')->assertUnprocessable();
    }

    public function test_room_responsibility_and_empty_views(): void
    {
        $this->loginAs(6);
        $this->put('/inventaris/1/pj', ['penanggung_id' => 4])->assertSessionHasErrors('penanggung_id');
        $this->put('/inventaris/1/pj', ['penanggung_id' => 2])->assertRedirect();
        $this->assertDatabaseHas('ruangan', ['ruangan_id' => 1, 'pj_id' => 2, 'petugas_id' => null]);
        $this->put('/inventaris/4/pj', ['penanggung_id' => 5])->assertRedirect();
        $this->assertDatabaseHas('ruangan', ['ruangan_id' => 4, 'pj_id' => null, 'petugas_id' => 5]);
        $this->get('/laporan?cari=tidakadabarang')->assertOk()->assertSee('Belum ada laporan');
        $this->get('/inventaris?cari=tidakadabarang')->assertOk()->assertSee('Belum ada inventaris');
        $this->get('/?cari=tidakadabarang')->assertOk()->assertSee('Belum ada laporan');
    }

    public function test_partial_assignment_is_rolled_back_if_report_save_fails(): void
    {
        $this->loginAs(6);
        LaporanKerusakan::updating(function ($laporan) {
            if ($laporan->laporan_id === 1) {
                throw new \RuntimeException('Simulasi gagal menyimpan laporan untuk pengujian transaksi.');
            }
        });
        $this->post('/penugasan/1/pemeriksaan',['petugas_id' => 4])->assertStatus(500);
        LaporanKerusakan::flushEventListeners();
        $this->assertDatabaseMissing('pemeriksaan',['laporan_id' => 1]);
        $this->assertSame('masuk',LaporanKerusakan::find(1)->status_laporan);
    }
}
