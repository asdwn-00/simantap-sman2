<div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
    <h3 class="text-lg font-bold text-[#2B4885] mb-1">
        Menunggu Konfirmasi Saya
    </h3>

    <p class="text-[11px] text-gray-400 mb-4">
        Periksa hasil pekerjaan pada laporan yang Anda buat.
    </p>

    <div class="space-y-3">
        @forelse ($perluDikonfirmasi as $tugas)
            @php
                $laporanKonfirmasi = $tugas->pemeriksaan->laporan;
                $formId = 'konfirmasi-pj-'.$tugas->penugasan_id;

                $formTerbuka = $errors->any()
                    && (string) old('form_penugasan_id')
                        === (string) $tugas->penugasan_id;
            @endphp

            <div class="border border-gray-100 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-800">
                    {{ $laporanKonfirmasi->kode_laporan }}
                    &middot;
                    {{ $laporanKonfirmasi->inventaris->nama_barang ?? '-' }}
                </p>

                <p class="text-[11px] text-gray-400 mt-1">
                    {{ $laporanKonfirmasi->ruangan->nama_ruangan ?? '-' }}
                </p>

                <p class="text-[11px] text-gray-500 mt-1">
                    Petugas: {{ $tugas->petugas->nama ?? '-' }}
                </p>

                <div class="bg-gray-50 rounded-lg p-3 my-3">
                    <p class="text-[11px] font-semibold text-gray-700">
                        Hasil pekerjaan:
                    </p>

                    <p class="text-[11px] text-gray-600 mt-1">
                        {{ $tugas->hasil ?: 'Belum ada catatan hasil.' }}
                    </p>
                </div>

                <button
                    type="button"
                    onclick="document.getElementById('{{ $formId }}').classList.toggle('hidden')"
                    class="w-full bg-[#F5C518] hover:bg-[#e3b615] text-[#2B4885] text-xs font-bold py-2 px-3 rounded-lg"
                >
                    Konfirmasi Hasil
                </button>

                <form
                    id="{{ $formId }}"
                    action="{{ route('konfirmasi.store', $tugas->penugasan_id) }}"
                    method="POST"
                    class="{{ $formTerbuka ? '' : 'hidden' }} mt-3 pt-3 border-t border-gray-100 space-y-3"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="form_penugasan_id"
                        value="{{ $tugas->penugasan_id }}"
                    >

                    <p class="text-[11px] text-gray-500">
                        Periksa kondisi barang sebelum memberikan konfirmasi.
                    </p>

                    <div class="space-y-2 text-xs">
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="hasil_konfirmasi"
                                value="sesuai"
                                required
                                @checked($formTerbuka && old('hasil_konfirmasi') === 'sesuai')
                            >
                            Sesuai
                        </label>

                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="hasil_konfirmasi"
                                value="masih_bermasalah"
                                required
                                @checked($formTerbuka && old('hasil_konfirmasi') === 'masih_bermasalah')
                            >
                            Masih Bermasalah
                        </label>
                    </div>

                    <textarea
                        name="catatan"
                        rows="3"
                        maxlength="1000"
                        placeholder="Catatan wajib diisi jika masih bermasalah."
                        class="w-full text-xs border border-gray-200 rounded-lg p-3"
                    >{{ $formTerbuka ? old('catatan') : '' }}</textarea>

                    <button
                        type="submit"
                        class="w-full bg-[#2B4885] hover:bg-blue-800 text-white text-xs font-bold py-2 px-3 rounded-lg"
                    >
                        Kirim Konfirmasi
                    </button>
                </form>
            </div>
        @empty
            <p class="text-xs text-gray-400 text-center py-4">
                Belum ada hasil pekerjaan yang menunggu konfirmasi Anda.
            </p>
        @endforelse
    </div>
</div>