@if ($akun->isKoordinator())
    @php
        $errorKeputusan = $errors->getBag(
            'keputusanDana'.$item->pengajuan_id
        );

        $catatanSebelumnya = $errorKeputusan->any()
            ? old('catatan')
            : '';
    @endphp

    @if ($errorKeputusan->any())
        <div
            role="alert"
            class="bg-red-50 text-red-700 rounded-xl p-4 mt-5 text-sm"
        >
            <p class="font-bold">Keputusan belum berhasil disimpan:</p>

            <ul class="list-disc pl-5 mt-2">
                @foreach ($errorKeputusan->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (\App\Services\AlurLaporan::bolehPutusDana($item, $akun))
        <form
            action="{{ route('dana.keputusan', $item->pengajuan_id) }}"
            method="POST"
            class="mt-6 pt-5 border-t border-gray-200"
        >
            @csrf

            <label
                for="catatan-dana-{{ $item->pengajuan_id }}"
                class="block text-sm font-bold text-[#2B4885] mb-2"
            >
                Catatan Koordinator
            </label>

            <textarea
                id="catatan-dana-{{ $item->pengajuan_id }}"
                name="catatan"
                rows="3"
                maxlength="1000"
                placeholder="Jelaskan hal yang perlu direvisi atau alasan penolakan."
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm"
            >{{ $catatanSebelumnya }}</textarea>

            <p class="text-xs text-gray-500 mt-2">
                Catatan wajib untuk Minta Revisi dan Tolak.
                Untuk Setujui, catatan boleh dikosongkan.
            </p>

            <div class="flex flex-wrap gap-2 mt-4">
                <button
                    type="submit"
                    name="keputusan"
                    value="disetujui"
                    class="bg-green-100 hover:bg-green-200 text-green-700 font-bold py-2.5 px-4 rounded-xl text-sm"
                >
                    Setujui
                </button>

                <button
                    type="submit"
                    name="keputusan"
                    value="revisi"
                    class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2.5 px-4 rounded-xl text-sm"
                >
                    Minta Revisi
                </button>

                <button
                    type="submit"
                    name="keputusan"
                    value="ditolak"
                    class="bg-red-100 hover:bg-red-200 text-red-700 font-bold py-2.5 px-4 rounded-xl text-sm"
                >
                    Tolak
                </button>
            </div>
        </form>
    @else
        <p class="text-xs text-gray-500 mt-5">
            Keputusan tidak tersedia: pengajuan sudah diputuskan,
            memiliki versi lanjutan, atau tahap laporan tidak lagi
            memenuhi syarat peninjauan.
        </p>
    @endif
@endif