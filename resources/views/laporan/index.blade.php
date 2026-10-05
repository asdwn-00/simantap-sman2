<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pengajuan Laporan - Petugas Sarpras - SIMANTAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fb; }
        .modal-backdrop { background-color: rgba(15, 23, 42, 0.45); }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/status-simantap.css') }}">
</head>
<body class="min-h-screen text-gray-800">

    @include('partials.navbar-internal')

    <main class="max-w-[1400px] mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-[#2B4885]">Pengajuan Laporan & Rekomendasi</h2>
                <p class="text-sm text-gray-500 mt-1">Ajukan laporan kerusakan, isi hasil pemeriksaan, dan konfirmasi penyelesaian.</p>
            </div>
            <button onclick="openModal('modalLaporan')" class="bg-[#F5C518] hover:bg-[#e3b60f] text-[#2B4885] text-sm font-extrabold py-3 px-6 rounded-full shadow-md flex items-center gap-2">
                <span class="text-lg leading-none">+</span> Buat Laporan Baru
            </button>
        </div>

        @if (session('sukses'))
            <div class="bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-xl px-4 py-3 mb-6">{{ session('sukses') }}</div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 text-sm font-semibold rounded-xl px-4 py-3 mb-6">{{ $errors->first() }}</div>
        @endif

        <form method="GET" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between gap-4">
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari berdasarkan barang atau ruangan..." class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm w-full md:w-96 focus:outline-none focus:border-[#2B4885]">
            <div class="flex items-center space-x-3">
                <select name="status" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-600 font-medium focus:outline-none">
        <option value="">Semua Status</option>
        @foreach (config('simantap.status') as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ ucfirst($value) }}</option>
        @endforeach
        </select>
                <button type="submit" class="bg-[#2B4885] text-white text-sm font-bold py-2.5 px-5 rounded-xl">Cari</button>
            </div>
        </form>

        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="py-4 px-2">Kode Laporan</th>
                            <th class="py-4 px-2">Barang / Ruangan</th>
                            <th class="py-4 px-2">Tanggal Lapor</th>
                            <th class="py-4 px-2">Tanggal Berakhir</th>
                            <th class="py-4 px-2">Status Laporan</th>
                            <th class="py-4 px-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-50">
                        @forelse ($baris as $b)
                            @php $l = $b['laporan']; @endphp
                            <tr class="hover:bg-gray-50 transition-colors {{ $b['bisa_konfirmasi'] ? 'bg-orange-50/30' : '' }}">
                                <td class="py-4 px-2 font-bold text-gray-800">{{ $l->kode_laporan }}</td>
                                <td class="py-4 px-2">
                                    <p class="font-medium text-gray-800">{{ $l->inventaris->nama_barang ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $l->ruangan->nama_ruangan ?? '-' }}</p>
                                </td>
                                <td class="py-4 px-2 text-gray-600 font-medium">{{ $l->tanggal_laporan->translatedFormat('d M Y') }}</td>
                                <td class="py-4 px-2 font-medium {{ $l->status_laporan === 'dihentikan' ? 'text-red-600 font-bold' : ($l->tanggal_ditutup ? 'text-green-600 font-bold' : 'text-gray-400') }}">
                                    {{ $l->tanggal_ditutup?->translatedFormat('d M Y') ?? 'Belum berakhir' }}
                                </td>
                                <td class="py-4 px-2">
                                    <span class="{{ $b['warna_status'] }} text-xs font-bold px-3 py-1.5 rounded-full">{{ $b['label_status'] }}</span>
                                    @if ($b['bisa_konfirmasi'])
                                        <p class="text-[10px] text-gray-500 mt-1">Pekerjaan fisik selesai.</p>
                                    @endif
                                </td>
                                <td class="py-4 px-2 text-center space-x-1 flex justify-center flex-wrap gap-1">
                                    @if ($b['bisa_ubah_hapus'])
                                        <button onclick="openModal('modalUbah-{{ $l->laporan_id }}')" class="bg-white border border-gray-200 hover:border-[#2B4885] text-[#2B4885] text-xs font-bold py-2 px-3 rounded-lg">Ubah</button>
                                        <button onclick="openModal('modalHapus-{{ $l->laporan_id }}')" class="bg-white border border-gray-200 hover:border-red-400 text-red-500 text-xs font-bold py-2 px-3 rounded-lg">Hapus</button>
                                    @elseif ($b['bisa_isi_pemeriksaan'])
                                        <button onclick="openModal('modalPemeriksaan-{{ $l->laporan_id }}')" class="bg-[#2B4885] hover:bg-[#213868] text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm">Isi Rekomendasi</button>
                                    @elseif ($b['bisa_konfirmasi'])
                                        <button onclick="openModal('modalDetail-{{ $l->laporan_id }}')" class="bg-white border border-gray-200 text-gray-500 text-xs font-bold py-2 px-2 rounded-lg">Detail</button>
                                        <button onclick="openModal('modalKonfirmasi-{{ $l->laporan_id }}')" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm">Konfirmasi Hasil</button>
                                    @else
                                        <button onclick="openModal('modalDetail-{{ $l->laporan_id }}')" class="bg-white border border-gray-200 text-gray-500 text-xs font-bold py-2 px-3 rounded-lg">Lihat Detail</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-10 text-center text-gray-400">Belum ada laporan yang cocok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $baris->links() }}</div>
        </div>
    </main>

    <div id="modalLaporan" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 modal-backdrop">
        <div class="bg-white w-full max-w-lg rounded-[1.75rem] shadow-xl p-8">
            <div class="flex justify-between items-start mb-1">
                <h3 class="text-xl font-extrabold text-[#2B4885]">Buat Laporan Baru</h3>
                <button type="button" onclick="closeModal('modalLaporan')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
            <p class="text-xs text-gray-500 mb-6">Laporkan kerusakan barang di ruangan tanggung jawab Anda.</p>

            <form method="POST" action="{{ route('laporan.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Ruangan</label>
                    <select name="ruangan_id" id="ruanganSelect" required class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]">
                        <option value="">Pilih ruangan tanggung jawab Anda</option>
                        @foreach ($pengguna->ruanganTanggungJawab as $r)
                            <option value="{{ $r->ruangan_id }}">{{ $r->nama_ruangan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Barang</label>
                    <select name="inventaris_id" id="inventarisSelect" required class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]" disabled>
                        <option value="">Pilih ruangan dahulu</option>
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Hanya menampilkan barang yang tercatat di ruangan yang dipilih.</p>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Keterangan Kerusakan</label>
                    <textarea name="kerusakan" rows="3" required placeholder="Jelaskan kerusakan yang ditemukan..." class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Pelapor</label>
                        <div class="mt-1.5 w-full bg-gray-100 text-gray-500 rounded-xl px-4 py-2.5 text-sm">{{ $pengguna->nama }}</div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Tanggal Laporan</label>
                        <div class="mt-1.5 w-full bg-gray-100 text-gray-500 rounded-xl px-4 py-2.5 text-sm">{{ now()->translatedFormat('d M Y') }} (Otomatis)</div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal('modalLaporan')" class="text-gray-500 hover:text-gray-700 text-sm font-bold py-2.5 px-4 rounded-xl">Batal</button>
                    <button type="submit" class="bg-[#F5C518] hover:bg-[#e3b60f] text-[#2B4885] text-sm font-extrabold py-2.5 px-5 rounded-full shadow-sm">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($baris as $b)
        @php $l = $b['laporan']; $pem = $b['pemeriksaan_terakhir']; $tin = $b['tindakan_terakhir']; $konf = $b['konfirmasi_terakhir']; @endphp

        @if ($b['bisa_ubah_hapus'])
            <div id="modalUbah-{{ $l->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 modal-backdrop">
                <div class="bg-white w-full max-w-lg rounded-[1.75rem] shadow-xl p-8">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Ubah Laporan</h3>
                        <button type="button" onclick="closeModal('modalUbah-{{ $l->laporan_id }}')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-6">{{ $l->kode_laporan }} &middot; {{ $l->inventaris->nama_barang ?? '-' }}. Hanya dapat diubah karena belum ada penugasan pemeriksaan.</p>

                    <form method="POST" action="{{ route('laporan.update', $l->laporan_id) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Ruangan</label>
                            <div class="mt-1.5 w-full bg-gray-100 text-gray-600 rounded-xl px-4 py-2.5 text-sm">{{ $l->ruangan->nama_ruangan ?? '-' }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Nama Barang</label>
                            <div class="mt-1.5 w-full bg-gray-100 text-gray-600 rounded-xl px-4 py-2.5 text-sm">{{ $l->inventaris->nama_barang ?? '-' }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Keterangan Kerusakan</label>
                            <textarea name="kerusakan" rows="3" required class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]">{{ old('kerusakan', $l->kerusakan) }}</textarea>
                        </div>
                        <div class="bg-yellow-50 text-yellow-700 text-[11px] p-3 rounded-xl border border-yellow-100">
                            Setelah laporan mendapat penugasan pemeriksaan, tombol Ubah tidak akan tersedia lagi.
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="closeModal('modalUbah-{{ $l->laporan_id }}')" class="text-gray-500 hover:text-gray-700 text-sm font-bold py-2.5 px-4 rounded-xl">Batal</button>
                            <button type="submit" class="bg-[#2B4885] hover:bg-[#213868] text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="modalHapus-{{ $l->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 modal-backdrop">
                <div class="bg-white w-full max-w-sm rounded-[1.75rem] shadow-xl p-8 text-center">
                    <h3 class="text-lg font-extrabold text-gray-800 mb-2">Hapus Laporan Ini?</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ $l->kode_laporan }} &middot; {{ $l->inventaris->nama_barang ?? '-' }} akan dihapus permanen. Tindakan ini hanya bisa dilakukan karena laporan belum diperiksa.</p>
                    <form method="POST" action="{{ route('laporan.destroy', $l->laporan_id) }}" class="flex justify-center gap-3">
                        @csrf @method('DELETE')
                        <button type="button" onclick="closeModal('modalHapus-{{ $l->laporan_id }}')" class="text-gray-500 hover:text-gray-700 text-sm font-bold py-2.5 px-5 rounded-xl border border-gray-200">Batal</button>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($b['bisa_isi_pemeriksaan'])
            <div id="modalPemeriksaan-{{ $l->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 py-6 modal-backdrop overflow-y-auto">
                <div class="bg-white w-full max-w-lg rounded-[1.75rem] shadow-xl p-8 my-auto">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Hasil Pemeriksaan & Rekomendasi</h3>
                        <button type="button" onclick="closeModal('modalPemeriksaan-{{ $l->laporan_id }}')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-6">Isi temuan dan rekomendasi penanganan. Laporan: {{ $l->kode_laporan }}.</p>

                    @php
                        $isianLama = (int) old('pemeriksaan_id') === (int) $pem->pemeriksaan_id;
                        $rekomendasiIsian = $isianLama ? old('rekomendasi') : ($pem->rekomendasi ?? 'perbaikan');
                        $jenisIsian = $isianLama ? old('jenis_penggantian') : $pem->jenis_penggantian;
                        $sumberIsian = $isianLama ? old('sumber_pengganti') : $pem->sumber_pengganti;
                        if ($jenisIsian === 'sparepart') $sumberIsian = 'pengadaan';
                    @endphp
                    @if ($pem->status_persetujuan === 'revisi')
                        <p class="text-sm bg-yellow-50 text-yellow-800 rounded-xl p-3 mb-4">Catatan revisi: {{ $pem->catatan_koordinator }}</p>
                    @endif
                    <form method="POST" action="{{ route('laporan.isi-pemeriksaan', $pem->pemeriksaan_id) }}" class="space-y-4">
                        <input type="hidden" name="pemeriksaan_id" value="{{ $pem->pemeriksaan_id }}">
                        @csrf
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Temuan Pemeriksaan Fisik</label>
                            <textarea name="temuan" rows="3" required placeholder="Contoh: Pipa pembuangan retak..." class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]">{{ $isianLama ? old('temuan') : $pem->temuan }}</textarea>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Rekomendasi Tindakan</label>
                            <div class="mt-2 flex gap-3">
                                <label class="flex-1 flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-2.5 text-sm cursor-pointer has-[:checked]:border-[#2B4885] has-[:checked]:bg-blue-50">
                                    <input type="radio" name="rekomendasi" value="perbaikan" @checked($rekomendasiIsian === 'perbaikan') onchange="toggleSumberPengganti('{{ $l->laporan_id }}', false)" class="accent-[#2B4885]"> Perbaikan / servis
                                </label>
                                <label class="flex-1 flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-2.5 text-sm cursor-pointer has-[:checked]:border-[#2B4885] has-[:checked]:bg-blue-50">
                                    <input type="radio" name="rekomendasi" value="penggantian" @checked($rekomendasiIsian === 'penggantian') onchange="toggleSumberPengganti('{{ $l->laporan_id }}', true)" class="accent-[#2B4885]"> Penggantian
                                </label>
                            </div>
                        </div>
                        <div id="sumberPenggantiField-{{ $l->laporan_id }}" class="{{ $rekomendasiIsian === 'penggantian' ? '' : 'hidden' }}">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Jenis Penggantian</label>
                            <select name="jenis_penggantian" onchange="sesuaikanJenisPenggantian('{{ $l->laporan_id }}')" @disabled($rekomendasiIsian !== 'penggantian') @required($rekomendasiIsian === 'penggantian') class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]">
                                <option value="">Pilih jenis penggantian</option>
                                <option value="unit" @selected($jenisIsian === 'unit')>Unit utuh</option>
                                <option value="sparepart" @selected($jenisIsian === 'sparepart')>Sparepart</option>
                            </select>
                            <label class="block mt-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Sumber Pengganti</label>
                            <select name="sumber_pengganti" @disabled($rekomendasiIsian !== 'penggantian') @required($rekomendasiIsian === 'penggantian') class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#2B4885]">
                                <option value="stok_gudang" @disabled($jenisIsian === 'sparepart') @selected($sumberIsian === 'stok_gudang')>Ambil Stok Gudang</option>
                                <option value="pengadaan" @selected($sumberIsian === 'pengadaan')>Butuh Pengajuan Dana (Pengadaan)</option>
                            </select>
                            <label class="block mt-3 text-xs font-bold text-gray-500 uppercase tracking-wide">Alasan Penggantian</label>
                            <textarea name="alasan_penggantian" rows="3" maxlength="2000" @disabled($rekomendasiIsian !== 'penggantian') @required($rekomendasiIsian === 'penggantian') placeholder="Jelaskan kerusakan dan mengapa unit atau sparepart perlu diganti." class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm">{{ $isianLama ? old('alasan_penggantian') : $pem->alasan_penggantian }}</textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" onclick="closeModal('modalPemeriksaan-{{ $l->laporan_id }}')" class="bg-white border border-gray-200 hover:border-[#2B4885] text-[#2B4885] text-sm font-bold py-2.5 px-4 rounded-xl">Batal</button>
                            <button type="submit" class="bg-[#2B4885] hover:bg-[#213868] text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm">Simpan & Ajukan ke Koordinator</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($b['bisa_konfirmasi'])
            <div id="modalKonfirmasi-{{ $l->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 modal-backdrop">
                <div class="bg-white w-full max-w-lg rounded-[1.75rem] shadow-xl p-8">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="text-xl font-extrabold text-green-700">Konfirmasi Penyelesaian Laporan</h3>
                        <button type="button" onclick="closeModal('modalKonfirmasi-{{ $l->laporan_id }}')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-6">{{ $l->kode_laporan }} &middot; {{ $l->inventaris->nama_barang ?? '-' }}. Pastikan pekerjaan lapangan sudah tuntas.</p>

                    <form method="POST" action="{{ route('konfirmasi.store', $tin->penugasan_id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Status Hasil Pekerjaan</label>
                            <div class="mt-2 flex gap-3">
                                <label class="flex-1 flex items-center gap-2 border border-green-200 rounded-xl px-4 py-2.5 text-sm cursor-pointer has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="hasil_konfirmasi" value="sesuai" checked class="accent-green-600"> Sudah Sesuai
                                </label>
                                <label class="flex-1 flex items-center gap-2 border border-red-200 rounded-xl px-4 py-2.5 text-sm cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                                    <input type="radio" name="hasil_konfirmasi" value="masih_bermasalah" class="accent-red-600"> Belum Sesuai
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Catatan Konfirmasi</label>
                            <textarea name="catatan" rows="3" placeholder="Berikan ulasan atau alasan jika belum sesuai..." class="mt-1.5 w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-green-600"></textarea>
                        </div>
                        <div class="bg-yellow-50 text-yellow-700 text-[11px] p-3 rounded-xl border border-yellow-100">
                            Jika memilih "Sudah Sesuai", Koordinator akan memverifikasi dan menutup laporan. Jika "Belum Sesuai", laporan akan dikembalikan ke antrean pemeriksaan ulang.
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="closeModal('modalKonfirmasi-{{ $l->laporan_id }}')" class="text-gray-500 hover:text-gray-700 text-sm font-bold py-2.5 px-4 rounded-xl">Batal</button>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm">Kirim Konfirmasi</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div id="modalDetail-{{ $l->laporan_id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 py-6 modal-backdrop overflow-y-auto">
            <div class="bg-white w-full max-w-2xl rounded-[1.75rem] shadow-xl p-8 my-auto relative">
                @include('partials.penghentian-laporan', ['laporan' => $l])

                <div class="flex justify-between items-start mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-xl font-extrabold text-[#2B4885]">Detail Riwayat Laporan ({{ $l->kode_laporan }})</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $l->inventaris->nama_barang ?? '-' }} &middot; {{ $l->ruangan->nama_ruangan ?? '-' }} &middot; Status Saat Ini: <span class="font-bold text-gray-800">{{ $b['label_status'] }}</span></p>
                    </div>
                    <button type="button" onclick="closeModal('modalDetail-{{ $l->laporan_id }}')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-2">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2"><span class="bg-gray-300 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">1</span> Laporan Awal</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Kerusakan</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $l->kerusakan }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Tanggal Lapor</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $l->tanggal_laporan->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($pem)
                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <h4 class="text-sm font-bold text-[#2B4885] mb-3 flex items-center gap-2"><span class="bg-[#2B4885] text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">2</span> Pemeriksaan & Keputusan</h4>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Temuan Petugas</p>
                                    <p class="text-sm text-gray-700 mt-0.5">
                                        {{ $pem->temuan ?: 'Belum diisi.' }}
                                        @if ($pem->tanggal_pemeriksaan) (Diperiksa: {{ $pem->tanggal_pemeriksaan->translatedFormat('d M Y') }}) @endif
                                    </p>
                                @if ($pem->alasan_penggantian)
                                    <p class="text-sm mt-2">Alasan penggantian: {{ $pem->alasan_penggantian }}</p>
                                @endif
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase">Rekomendasi</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-0.5">
                                            {{ $pem->label_rekomendasi }}
                                            @if ($pem->sumber_pengganti) &middot; {{ $pem->sumber_pengganti === 'stok_gudang' ? 'Ambil Stok Gudang' : 'Pengadaan' }} @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase">Keputusan Koordinator</p>
                                        <p class="text-sm font-bold mt-0.5 {{ $pem->status_persetujuan === 'disetujui' ? 'text-green-600' : ($pem->status_persetujuan === 'dihentikan' ? 'text-red-600' : 'text-gray-500') }}">
                                            @switch($pem->status_persetujuan)
                                                @case('disetujui') Disetujui @if ($l->prioritas) &middot; Prioritas: {{ ucfirst($l->prioritas) }} @endif @break
                                                @case('revisi') Diminta Revisi @break
                                                @case('dihentikan') Dihentikan @break
                                                @case('menunggu') Menunggu Tinjauan @break
                                                @default Belum Diajukan
                                            @endswitch
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($tin)
                        <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100">
                            <h4 class="text-sm font-bold text-purple-800 mb-3 flex items-center gap-2"><span class="bg-purple-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">3</span> Pelaksanaan (Penugasan)</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Hasil Penanganan</p>
                                    <p class="text-sm text-gray-700 mt-0.5">{{ $tin->hasil ?: ($tin->catatan_tindakan ?: 'Belum ada catatan.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Tanggal Mulai</p>
                                    <p class="text-sm text-gray-700 mt-0.5">{{ $tin->tanggal_mulai?->translatedFormat('d M Y') ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($konf)
                        <div class="bg-green-50/50 p-4 rounded-xl border border-green-200">
                            <h4 class="text-sm font-bold text-green-800 mb-3 flex items-center gap-2"><span class="bg-green-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">4</span> Penyelesaian</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Konfirmasi Pelapor</p>
                                    <p class="text-sm font-bold mt-0.5 {{ $konf->hasil_konfirmasi === 'sesuai' ? 'text-green-700' : 'text-red-600' }}">
                                        {{ $konf->hasil_konfirmasi === 'sesuai' ? 'Sesuai' : 'Masih Bermasalah' }}
                                        @if ($konf->catatan) ({{ $konf->catatan }}) @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Tanggal Laporan Berakhir</p>
                                    <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $l->tanggal_ditutup?->translatedFormat('d M Y') ?? 'Belum ditutup koordinator' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-100 mt-6">
                    <button type="button" onclick="closeModal('modalDetail-{{ $l->laporan_id }}')" class="bg-white border border-gray-200 hover:border-[#2B4885] text-[#2B4885] text-sm font-bold py-2.5 px-6 rounded-xl">Tutup Jendela</button>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function toggleSumberPengganti(laporanId, show) {
            const field = document.getElementById('sumberPenggantiField-' + laporanId);
            field.classList.toggle('hidden', !show);
            field.querySelectorAll('select, textarea').forEach(input => {
                input.required = show;
                input.disabled = !show;
            });
            sesuaikanJenisPenggantian(laporanId);
        }

        function sesuaikanJenisPenggantian(laporanId) {
            const field = document.getElementById('sumberPenggantiField-' + laporanId);
            const jenis = field.querySelector('[name=jenis_penggantian]').value;
            const sumber = field.querySelector('[name=sumber_pengganti]');
            sumber.querySelector('[value=stok_gudang]').disabled = jenis === 'sparepart';
            if (jenis === 'sparepart') sumber.value = 'pengadaan';
        }

        const dataRuangan = @json($pengguna->ruanganTanggungJawab->mapWithKeys(fn ($r) => [
            $r->ruangan_id => $r->inventaris->map(fn ($i) => ['id' => $i->inventaris_id, 'nama' => $i->nama_barang]),
        ]));

        document.getElementById('ruanganSelect').addEventListener('change', function () {
            const target = document.getElementById('inventarisSelect');
            const items = dataRuangan[this.value] || [];
            target.innerHTML = '';
            if (!this.value) {
                target.innerHTML = '<option value="">Pilih ruangan dahulu</option>';
                target.disabled = true;
                return;
            }
            if (items.length === 0) {
                target.innerHTML = '<option value="">Tidak ada barang tercatat di ruangan ini</option>';
                target.disabled = true;
                return;
            }
            target.replaceChildren(new Option('Pilih barang', ''));
            items.forEach(i => target.add(new Option(i.nama, i.id)));
            target.disabled = false;
        });
    </script>

<script>
        const requestedModal = @json(request('buka'));
        const previousInspection = @json(old('pemeriksaan_id'));
        if (previousInspection) {
            document.querySelectorAll('input[name="pemeriksaan_id"]').forEach(input => {
                if (input.value === String(previousInspection)) {
                    input.closest('.modal-backdrop').classList.remove('hidden');
                }
            });
        }
        if (typeof requestedModal === 'string' && /^(modalLaporan|modal(?:Pemeriksaan|Tinjau|Konfirmasi|Tutup)-[0-9]+)$/.test(requestedModal)) {
            const modal = document.getElementById(requestedModal);
            if (modal) modal.classList.remove('hidden');
        }
    </script>
</body>
</html>
