@if ($akun->isPetugas())
    <div
        id="modalBuatDana"
        role="dialog"
        aria-modal="true"
        aria-labelledby="judulBuatDana"
        class="{{ $errors->buatDana->any() ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-6"
    >
        <div class="bg-white rounded-[2rem] w-full max-w-2xl p-8 shadow-2xl max-h-[85vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5">
                <h3
                    id="judulBuatDana"
                    class="text-xl font-extrabold text-[#2B4885]"
                >
                    Buat Pengajuan Dana
                </h3>

                <button
                    type="button"
                    aria-label="Tutup form"
                    onclick="document.getElementById('modalBuatDana').classList.add('hidden')"
                    class="text-gray-400 hover:text-red-500 text-2xl"
                >
                    &times;
                </button>
            </div>

            @if ($errors->buatDana->any())
                <div
                    role="alert"
                    class="bg-red-50 text-red-700 rounded-xl p-4 mb-5 text-sm"
                >
                    <p class="font-bold">Pengajuan belum berhasil disimpan:</p>

                    <ul class="list-disc pl-5 mt-2">
                        @foreach ($errors->buatDana->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($pilihanPemeriksaan->isEmpty())
                <div class="bg-yellow-50 text-yellow-800 rounded-xl p-4 mb-5 text-sm">
                    Belum ada laporan yang dapat dipilih.
                    Laporan harus berasal dari pemeriksaan yang ditugaskan
                    kepada Anda, rencananya sudah disetujui,
                    belum memiliki pengajuan dana, dan belum masuk pelaksanaan.
                </div>
            @endif

            <form
                action="{{ route('dana.store') }}"
                method="POST"
                class="space-y-5"
            >
                @csrf

                <div>
                    <label
                        for="dana-pemeriksaan"
                        class="block text-sm font-bold text-gray-700 mb-2"
                    >
                        Laporan yang Membutuhkan Dana
                    </label>

                    <select
                        id="dana-pemeriksaan"
                        name="pemeriksaan_id"
                        required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm"
                    >
                        <option value="">Pilih laporan</option>

                        @foreach ($pilihanPemeriksaan as $pemeriksaan)
                            <option
                                value="{{ $pemeriksaan->pemeriksaan_id }}"
                                @selected(
                                    (string) old('pemeriksaan_id')
                                    === (string) $pemeriksaan->pemeriksaan_id
                                )
                            >
                                {{ $pemeriksaan->laporan->kode_laporan }}
                                | {{ $pemeriksaan->laporan->inventaris->nama_barang }}
                                | {{ $pemeriksaan->laporan->ruangan->nama_ruangan }}
                                | {{ $pemeriksaan->label_rekomendasi }}
                            </option>
                        @endforeach
                    </select>

                    <p class="text-xs text-gray-500 mt-2">
                        Barang dan ruangan mengikuti laporan.
                    </p>
                </div>

                <div>
                    <label
                        for="dana-kebutuhan"
                        class="block text-sm font-bold text-gray-700 mb-2"
                    >
                        Rincian Kebutuhan
                    </label>

                    <textarea
                        id="dana-kebutuhan"
                        name="rincian_kebutuhan"
                        rows="4"
                        maxlength="5000"
                        required
                        placeholder="Contoh: pembelian kabel HDMI 5 meter untuk mengganti kabel proyektor yang rusak."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm"
                    >{{ old('rincian_kebutuhan') }}</textarea>
                </div>

                <div>
                    <label
                        for="dana-biaya"
                        class="block text-sm font-bold text-gray-700 mb-2"
                    >
                        Estimasi Biaya (Rp)
                    </label>

                    <input
                        id="dana-biaya"
                        name="estimasi_biaya"
                        type="number"
                        min="0.01"
                        max="999999999999.99"
                        step="0.01"
                        value="{{ old('estimasi_biaya') }}"
                        placeholder="Contoh: 150000"
                        required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm"
                    >

                    <p class="text-xs text-gray-500 mt-2">
                        Masukkan angka tanpa tulisan Rp atau pemisah ribuan.
                    </p>
                </div>

                <div class="bg-blue-50 text-[#2B4885] rounded-xl p-4 text-xs">
                    Pembuat dan tanggal diisi otomatis.
                    Pengajuan akan dikirim dengan status Diajukan
                    untuk ditinjau koordinator.
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        onclick="document.getElementById('modalBuatDana').classList.add('hidden')"
                        class="border border-gray-200 text-gray-600 font-bold py-2.5 px-5 rounded-xl text-sm"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        @disabled($pilihanPemeriksaan->isEmpty())
                        class="bg-[#F5C518] text-[#2B4885] font-bold py-2.5 px-5 rounded-xl text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif