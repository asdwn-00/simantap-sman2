<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\Ruangan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $akun = Auth::guard('pengguna')->user();
        $q = Inventaris::with(['ruangan.pj', 'ruangan.petugasPenanggungJawab']);
        if ($akun->isPjLab()) {
            $q->whereHas('ruangan', fn ($r) => $r->where('pj_id', $akun->pengguna_id));
        }
        if ($cari = trim((string) $request->query('cari'))) {
            $q->where(function ($s) use ($cari) {
                $s->where('nama_barang', 'like', "%$cari%");
                if (ctype_digit($cari)) {
                    $s->orWhere('inventaris_id', $cari);
                }
            });
        }
        if (array_key_exists((string) $request->query('jenis'), config('simantap.ruangan'))) {
            $q->whereHas('ruangan', fn ($r) => $r->where('jenis_ruangan', $request->query('jenis')));
        }
        if ($request->filled('kondisi')) { $q->where('kondisi', $request->query('kondisi')); }
        if ($request->filled('ruangan')) { $q->where('ruangan_id', $request->query('ruangan')); }
        $inventaris = $q->orderBy('inventaris_id')->paginate(15)->withQueryString();

        $inventaris->through(function ($i) {
            $i->nama_ruangan = $i->ruangan->nama_ruangan;
            $i->jenis_ruangan = $i->ruangan->jenis_ruangan;
            $i->pj_nama = $i->ruangan->pj?->nama;
            $i->petugas_nama = $i->ruangan->petugasPenanggungJawab?->nama;
            $i->pj_id = $i->ruangan->pj_id;
            $i->petugas_id = $i->ruangan->petugas_id;
            return $i;
        });
        $daftarRuangan = \App\Models\Ruangan::when($akun->isPjLab(), fn ($q) => $q->where('pj_id', $akun->pengguna_id))->orderBy('nama_ruangan')->get();
        $daftarKondisi = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak ringan', 'rusak_berat' => 'Rusak berat', 'belum_diperiksa' => 'Belum diperiksa'];
        return view('inventaris.index', compact('akun', 'inventaris', 'daftarRuangan', 'daftarKondisi'));
    }

    public function store(Request $request)
    {
        $akun = Auth::guard('pengguna')->user();

        abort_unless(
            $akun->isKoordinator() || $akun->isPetugas(),
            403,
            'Anda tidak memiliki akses untuk menambahkan barang.'
        );

        $data = $request->validateWithBag('tambahBarang', [
            'nama_barang' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:30'],
            'spesifikasi' => ['nullable', 'string', 'max:5000'],
            'nomor_seri' => ['nullable', 'string', 'max:100'],
            'ruangan_id' => [
                'required',
                'integer',
                Rule::exists('ruangan', 'ruangan_id'),
            ],
            'kondisi' => [
                'required',
                Rule::in([
                    'baik',
                    'rusak_ringan',
                    'rusak_berat',
                    'belum_diperiksa',
                ]),
            ],
            'status_penggunaan' => [
                'required',
                Rule::in([
                    'tersedia',
                    'digunakan',
                    'tidak_digunakan',
                ]),
            ],
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'kategori.required' => 'Kategori wajib diisi.',
            'ruangan_id.required' => 'Pilih lokasi barang terlebih dahulu.',
            'ruangan_id.exists' => 'Ruangan yang dipilih tidak ditemukan.',
            'kondisi.in' => 'Pilihan kondisi tidak sesuai.',
            'status_penggunaan.required' => 'Pilih status penggunaan yang sesuai dengan lokasi dan kondisi barang.',
        ]);

        $barang = DB::transaction(function () use ($data) {

            $ruangan = Ruangan::whereKey($data['ruangan_id'])
                ->lockForUpdate()
                ->first();

            if (! $ruangan) {
                throw ValidationException::withMessages([
                    'ruangan_id' =>
                        'Ruangan yang dipilih sudah tidak tersedia. Pilih kembali lokasi barang.',
                ])->errorBag('tambahBarang');
            }

            if (
                $ruangan->jenis_ruangan === 'gudang'
                && $data['status_penggunaan'] === 'digunakan'
            ) {
                throw ValidationException::withMessages([
                    'status_penggunaan' =>
                        'Barang di gudang tidak boleh berstatus Digunakan. '
                        .'Pilih Tersedia atau Tidak digunakan sesuai keadaan barang.',
                ])->errorBag('tambahBarang');
            }

            if (
                $data['status_penggunaan'] === 'tersedia'
                && $data['kondisi'] !== 'baik'
            ) {
                throw ValidationException::withMessages([
                    'status_penggunaan' =>
                        'Status Tersedia hanya boleh untuk barang berkondisi Baik.',
                ])->errorBag('tambahBarang');
            }

            return Inventaris::create($data);
        });

        return redirect()
            ->route('inventaris.index', [
                'cari' => $barang->inventaris_id,
            ])
            ->with('sukses', 'Barang berhasil ditambahkan dengan ID '.$barang->inventaris_id.'.');
    }

}
