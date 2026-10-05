@if ($laporan->status_laporan === 'dihentikan')
    <div class="bg-red-50 text-red-800 rounded-xl p-4 mb-4" role="status">
        <p class="font-bold">Laporan Dihentikan</p>
        <p class="text-sm mt-2">{{ $laporan->alasan_penghentian }}</p>
        <p class="text-xs mt-2">Tanggal penghentian: {{ $laporan->tanggal_ditutup?->format('d M Y H:i') }}</p>
        <p class="text-xs mt-2">Proses laporan berakhir tanpa konfirmasi keberhasilan penanganan.</p>
    </div>
@endif
