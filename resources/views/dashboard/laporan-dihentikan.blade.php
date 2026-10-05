@if ($jumlahDihentikan > 0)
    <p class="text-sm text-gray-500 mt-4 mb-4">
        <a href="{{ route('laporan.index', ['status' => 'dihentikan']) }}" class="font-semibold text-[#2B4885]">Laporan dihentikan: {{ $jumlahDihentikan }}</a>
    </p>
@endif
