@php
    $errorProgres = $errors->getBag('progres'.$pl->penugasan_id);

    $tindakanTerakhir = \App\Services\AlurLaporan::tindakan($laporan);

    $bolehMengisi =
        \App\Services\AlurLaporan::bolehCatatProgres($laporan, $akun)
        && (int) $tindakanTerakhir?->penugasan_id
            === (int) $pl->penugasan_id;
@endphp

@if ($errorProgres->any())
    <div role="alert" class="progres-error">
        <ul>
            @foreach ($errorProgres->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($bolehMengisi)
    @php
        $statusIsian = $errorProgres->any()
            ? old('status_tindakan')
            : $pl->status_tindakan;

        $catatanIsian = $errorProgres->any()
            ? old('catatan_tindakan')
            : $pl->catatan_tindakan;

        $kendalaIsian = $errorProgres->any()
            ? old('kendala')
            : $pl->kendala;

        $hasilIsian = $errorProgres->any()
            ? old('hasil')
            : $pl->hasil;

        $jenisRekomendasi =
            \App\Services\AlurLaporan::pemeriksaan($laporan)?->rekomendasi;
    @endphp

    <form
        action="{{ route('penugasan.progres', $pl->penugasan_id) }}"
        method="POST"
        class="form-progres"
    >
        @csrf

        <h3>Catat Progres Pekerjaan</h3>

        <label for="status-progres-{{ $pl->penugasan_id }}">
            Status Pekerjaan
        </label>

        <select
            id="status-progres-{{ $pl->penugasan_id }}"
            name="status_tindakan"
            required
        >
            <option value="berjalan" @selected($statusIsian === 'berjalan')>
                Berjalan
            </option>

            <option value="terkendala" @selected($statusIsian === 'terkendala')>
                Terkendala
            </option>

            <option value="selesai" @selected($statusIsian === 'selesai')>
                Selesai
            </option>
        </select>

        @if ($jenisRekomendasi === 'penggantian')
            @php
                $penggantiIsian = $errorProgres->any()
                    ? old('inventaris_pengganti_id')
                    : '';
            @endphp

            <label for="pengganti-{{ $pl->penugasan_id }}">
                Unit Inventaris Pengganti
            </label>

            <select
                id="pengganti-{{ $pl->penugasan_id }}"
                name="inventaris_pengganti_id"
            >
                <option value="">Pilih barang pengganti</option>

                @foreach ($pilihanPengganti as $barang)
                    <option
                        value="{{ $barang->inventaris_id }}"
                        @selected(
                            (string) $penggantiIsian
                            === (string) $barang->inventaris_id
                        )
                    >
                        ID: {{ $barang->inventaris_id }}
                        | {{ $barang->nama_barang }}
                        | {{ $barang->ruangan->nama_ruangan }}
                    </option>
                @endforeach
            </select>

            <p class="progres-petunjuk">
                Wajib dipilih saat status Selesai.
                Pastikan jenis dan spesifikasi barang sesuai kebutuhan.
                Penyimpanan sebagai Selesai berarti penggantian sudah dilakukan
                dan fungsi barang sudah diperiksa.
                Untuk kelas/lab, barang pengganti harus sudah dipasang.
                Untuk gudang, barang tetap dicatat sebagai Tidak digunakan.
            </p>

            @php
                $kondisiLamaIsian = $errorProgres->any()
                    ? old('kondisi_barang_lama', '')
                    : '';
            @endphp

            <label for="kondisi-lama-{{ $pl->penugasan_id }}">
                Kondisi Barang Lama Setelah Dilepas
            </label>

            <select
                id="kondisi-lama-{{ $pl->penugasan_id }}"
                name="kondisi_barang_lama"
            >
                <option value="">Pilih kondisi hasil pemeriksaan</option>

                <option
                    value="baik"
                    @selected($kondisiLamaIsian === 'baik')
                >
                    Baik
                </option>

                <option
                    value="rusak_ringan"
                    @selected($kondisiLamaIsian === 'rusak_ringan')
                >
                    Rusak ringan
                </option>

                <option
                    value="rusak_berat"
                    @selected($kondisiLamaIsian === 'rusak_berat')
                >
                    Rusak berat
                </option>
            </select>

            <p class="progres-petunjuk">
                Wajib dipilih ketika pekerjaan penggantian selesai.
                Pilih berdasarkan kondisi fisik barang lama.
                Jelaskan kerusakannya pada Catatan Pengerjaan.
                Barang lama tetap dicatat sebagai Tidak digunakan.
            </p>

            @php
                $gudangLamaIsian = $errorProgres->any()
                    ? old('gudang_barang_lama_id', '')
                    : '';
            @endphp

            <label for="gudang-lama-{{ $pl->penugasan_id }}">
                Lokasi Barang Lama Setelah Penggantian
            </label>

            <select
                id="gudang-lama-{{ $pl->penugasan_id }}"
                name="gudang_barang_lama_id"
            >
                <option value="">
                    Tetap di ruangan asal (belum dipindahkan)
                </option>

                @foreach ($daftarGudang as $gudang)
                    <option
                        value="{{ $gudang->ruangan_id }}"
                        @selected(
                            (string) $gudangLamaIsian
                            === (string) $gudang->ruangan_id
                        )
                    >
                        Sudah dipindahkan ke {{ $gudang->nama_ruangan }}
                    </option>
                @endforeach
            </select>

            <p class="progres-petunjuk">
                Pilih gudang hanya jika barang lama sudah benar-benar dipindahkan.
                Lokasi disimpan ketika status pekerjaan dipilih Selesai.
                Jika belum dipindahkan, biarkan pilihan Tetap di ruangan asal.
            </p>

            @if ($pilihanPengganti->isEmpty())
                <p class="progres-petunjuk">
                    Belum ada barang pengganti yang memenuhi syarat.
                    Untuk pengadaan, catat barang yang sudah diterima
                    melalui menu Inventaris Barang dengan lokasi Gudang,
                    kondisi Baik, dan status Tersedia.
                </p>
            @endif
        @endif

        <label for="catatan-progres-{{ $pl->penugasan_id }}">
            Catatan Pengerjaan
        </label>

        <textarea
            id="catatan-progres-{{ $pl->penugasan_id }}"
            name="catatan_tindakan"
            rows="3"
            maxlength="5000"
            required
            placeholder="Tuliskan pekerjaan yang sudah dilakukan."
        >{{ $catatanIsian }}</textarea>

        <label for="kendala-progres-{{ $pl->penugasan_id }}">
            Kendala
        </label>

        <textarea
            id="kendala-progres-{{ $pl->penugasan_id }}"
            name="kendala"
            rows="2"
            maxlength="5000"
            placeholder="Wajib diisi jika status pekerjaan Terkendala."
        >{{ $kendalaIsian }}</textarea>

        <label for="hasil-progres-{{ $pl->penugasan_id }}">
            Hasil Pekerjaan
        </label>

        <textarea
            id="hasil-progres-{{ $pl->penugasan_id }}"
            name="hasil"
            rows="3"
            maxlength="5000"
            placeholder="Wajib diisi jika pekerjaan selesai, termasuk hasil pengecekan fungsi barang."
        >{{ $hasilIsian }}</textarea>

        <p class="progres-petunjuk">
            Pilih Selesai setelah pekerjaan dan pengecekan fungsi selesai.
            Setelah dikirim sebagai Selesai, formulir ini tidak dapat diubah.
        </p>

        <button type="submit" class="btn btn-edit">
            Simpan Progres
        </button>
    </form>
@endif