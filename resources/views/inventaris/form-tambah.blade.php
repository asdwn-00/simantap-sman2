@if ($akun->isKoordinator() || $akun->isPetugas())
    <details
        class="tambah-barang"
        @if ($errors->tambahBarang->any()) open @endif
    >
        <summary class="btn-primary">
            + Tambah Barang
        </summary>

        <form
            method="POST"
            action="{{ route('inventaris.store') }}"
            class="form-tambah-barang"
        >
            @csrf

            <h3>Tambah Data Barang</h3>

            <p class="muted">
                Satu data mewakili satu unit barang.
                Pilih lokasi sesuai keberadaan barang saat ini.
            </p>

            @if ($errors->tambahBarang->any())
                <div class="alert-error" role="alert">
                    <strong>Barang belum berhasil disimpan:</strong>

                    <ul class="daftar-error-barang">
                        @foreach ($errors->tambahBarang->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-barang-grid">
                <div>
                    <label for="barang-id">ID Barang (Otomatis)</label>
                    <input
                        id="barang-id"
                        type="text"
                        value="ID diberikan otomatis saat barang disimpan"
                        readonly
                    >
                </div>

                <div>
                    <label for="barang-nama">Nama Barang *</label>
                    <input
                        id="barang-nama"
                        name="nama_barang"
                        type="text"
                        maxlength="100"
                        value="{{ old('nama_barang') }}"
                        placeholder="Contoh: Proyektor Epson"
                        required
                    >
                </div>

                <div>
                    <label for="barang-kategori">Kategori *</label>
                    <input
                        id="barang-kategori"
                        name="kategori"
                        type="text"
                        maxlength="30"
                        value="{{ old('kategori') }}"
                        placeholder="Contoh: Elektronik"
                        required
                    >
                </div>

                <div>
                    <label for="barang-seri">Nomor Seri</label>
                    <input
                        id="barang-seri"
                        name="nomor_seri"
                        type="text"
                        maxlength="100"
                        value="{{ old('nomor_seri') }}"
                        placeholder="Boleh dikosongkan jika tidak ada"
                    >
                </div>

                <div>
                    <label for="barang-ruangan">Lokasi / Ruangan *</label>
                    <select id="barang-ruangan" name="ruangan_id" required>
                        <option value="">Pilih ruangan</option>

                        @foreach ($daftarRuangan as $ruangan)
                            <option
                                value="{{ $ruangan->ruangan_id }}"
                                data-jenis="{{ $ruangan->jenis_ruangan }}"
                                @selected(
                                    (string) old('ruangan_id')
                                    === (string) $ruangan->ruangan_id
                                )
                            >
                                {{ $ruangan->kode_ruangan }}
                                | {{ $ruangan->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="barang-kondisi">Kondisi *</label>
                    <select id="barang-kondisi" name="kondisi" required>
                        @foreach ($daftarKondisi as $nilai => $label)
                            <option
                                value="{{ $nilai }}"
                                @selected(
                                    old('kondisi', 'belum_diperiksa') === $nilai
                                )
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="barang-status">Status Penggunaan *</label>
                    <select
                        id="barang-status"
                        name="status_penggunaan"
                        required
                    >
                    <option
                        value=""
                        @selected(old('status_penggunaan', 'tidak_digunakan') === '')
                    >
                        Pilih status penggunaan
                    </option>
                        <option
                            value="tidak_digunakan"
                            @selected(
                                old('status_penggunaan', 'tidak_digunakan')
                                === 'tidak_digunakan'
                            )
                        >
                            Tidak digunakan
                        </option>

                        <option
                            value="tersedia"
                            @selected(old('status_penggunaan') === 'tersedia')
                        >
                            Tersedia
                        </option>

                        <option
                            value="digunakan"
                            @selected(old('status_penggunaan') === 'digunakan')
                        >
                            Digunakan
                        </option>
                    </select>
                    <p id="petunjuk-status-barang" class="muted" aria-live="polite">
                        Status Tersedia hanya untuk barang berkondisi Baik.
                        Barang di gudang tidak boleh berstatus Digunakan.
                    </p>
                </div>

                <div class="form-barang-lebar">
                    <label for="barang-spesifikasi">Spesifikasi</label>
                    <textarea
                        id="barang-spesifikasi"
                        name="spesifikasi"
                        rows="3"
                        maxlength="5000"
                        placeholder="Merek, model, atau ciri barang"
                    >{{ old('spesifikasi') }}</textarea>
                </div>
            </div>

            @if ($daftarRuangan->isEmpty())
                <div class="alert-error">
                    Belum ada ruangan. Data ruangan perlu disiapkan
                    sebelum menambahkan barang.
                </div>
            @endif

            <div class="form-barang-tombol">
                <button
                    type="button"
                    class="btn-primary btn-soft"
                    onclick="this.closest('details').open = false"
                >
                    Tutup Form
                </button>

                <button
                    type="submit"
                    class="btn-primary"
                    @disabled($daftarRuangan->isEmpty())
                >
                    Simpan Barang
                </button>
            </div>
        </form>
    </details>
    <script>
        (() => {
            const pilihanRuangan = document.getElementById('barang-ruangan');
            const pilihanKondisi = document.getElementById('barang-kondisi');
            const pilihanStatus = document.getElementById('barang-status');
            const petunjuk = document.getElementById('petunjuk-status-barang');

            if (!pilihanRuangan || !pilihanKondisi || !pilihanStatus || !petunjuk) {
                return;
            }

            const opsiDigunakan = pilihanStatus.querySelector(
                'option[value="digunakan"]'
            );

            const opsiTersedia = pilihanStatus.querySelector(
                'option[value="tersedia"]'
            );

            function sesuaikanStatusBarang() {
                const ruanganTerpilih = pilihanRuangan.selectedOptions[0];

                const lokasiGudang =
                    ruanganTerpilih?.dataset.jenis === 'gudang';

                const kondisiBaik = pilihanKondisi.value === 'baik';

                opsiDigunakan.disabled = lokasiGudang;
                opsiTersedia.disabled = !kondisiBaik;

                const statusTerpilih = pilihanStatus.selectedOptions[0];

                const pilihanTidakValid =
                    statusTerpilih && statusTerpilih.disabled;

                if (pilihanTidakValid) {
                    pilihanStatus.value = '';
                }

                let pesan = 'Status Tersedia hanya untuk barang berkondisi Baik.';

                if (lokasiGudang) {
                    pesan += ' Barang di gudang tidak boleh berstatus Digunakan.';
                }

                if (pilihanTidakValid) {
                    pesan += ' Pilihan sebelumnya tidak sesuai; pilih status kembali.';
                }

                petunjuk.textContent = pesan;
            }

            pilihanRuangan.addEventListener('change', sesuaikanStatusBarang);
            pilihanKondisi.addEventListener('change', sesuaikanStatusBarang);

            sesuaikanStatusBarang();
        })();
    </script>
@endif
