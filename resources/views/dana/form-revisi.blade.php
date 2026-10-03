@if ($akun->isPetugas())
    @php
        $errorRevisi = $errors->getBag(
            'revisiDana'.$item->pengajuan_id
        );

        $rincianRevisi = $errorRevisi->any()
            ? old('rincian_kebutuhan')
            : $item->rincian_kebutuhan;

        $biayaRevisi = $errorRevisi->any()
            ? old('estimasi_biaya')
            : $item->estimasi_biaya;
    @endphp

    @if ($errorRevisi->any())
        <div
            role="alert"
            class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700"
        >
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errorRevisi->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (\App\Services\AlurLaporan::bolehRevisiDana($item, $akun))
        <form
            action="{{ route('dana.revisi', $item->pengajuan_id) }}"
            method="POST"
            class="mt-6 border-t border-gray-200 pt-5"
        >
            @csrf

            <h4 class="font-bold text-[#2B4885] mb-2">
                Perbaiki Pengajuan Dana
            </h4>

            <p class="text-xs text-gray-500 mb-5">
                Sesuaikan rincian kebutuhan dan estimasi biaya
                berdasarkan catatan koordinator di atas.
                Pengajuan ini akan dikirim sebagai versi baru.
            </p>

            <div class="mb-4">
                <label
                    for="rincian-revisi-{{ $item->pengajuan_id }}"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Rincian Kebutuhan
                </label>

                <textarea
                    id="rincian-revisi-{{ $item->pengajuan_id }}"
                    name="rincian_kebutuhan"
                    rows="4"
                    maxlength="5000"
                    required
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm"
                >{{ $rincianRevisi }}</textarea>
            </div>

            <div class="mb-5">
                <label
                    for="biaya-revisi-{{ $item->pengajuan_id }}"
                    class="block text-sm font-bold text-gray-700 mb-2"
                >
                    Estimasi Biaya (Rp)
                </label>

                <input
                    id="biaya-revisi-{{ $item->pengajuan_id }}"
                    type="number"
                    name="estimasi_biaya"
                    value="{{ $biayaRevisi }}"
                    min="0.01"
                    max="999999999999.99"
                    step="0.01"
                    required
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm"
                >

                <p class="mt-2 text-xs text-gray-500">
                    Contoh: 150000 untuk Rp150.000.
                    Masukkan angka tanpa pemisah ribuan.
                </p>
            </div>

            <button
                type="submit"
                class="rounded-xl bg-[#2B4885] px-5 py-3 text-sm font-bold text-white"
            >
                Kirim Revisi
            </button>
        </form>
    @elseif ($item->status_pengajuan === 'revisi')
        <p class="mt-5 text-sm text-gray-500">
            @if ($item->pengajuanBerikutnya)
                Pengajuan ini sudah memiliki versi lanjutan.
                Buka versi terbaru pada daftar pengajuan.
            @else
                Revisi belum dapat dikirim karena tugas atau
                tahap laporan tidak memenuhi syarat.
            @endif
        </p>
    @endif
@endif