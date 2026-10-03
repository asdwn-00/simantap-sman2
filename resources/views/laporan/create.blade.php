<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - SIMANTAP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-main:#F4F7FE; --bg-card:#FFFFFF; --text-dark:#2B3674; --text-muted:#A3AED0; --accent-main:#FFB800; --accent-hover:#E5A600; --input-border:#E2E8F0; --pastel-red:#FF6B6B; --pastel-red-light:#FFE5E5; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-dark); min-height: 100vh; padding: 0; }
        .container { width: calc(100% - 40px); max-width: 600px; margin: 40px auto; }
        .back-link { display:inline-block; margin-bottom:20px; color:var(--text-muted); text-decoration:none; font-size:13px; font-weight:600; }
        .card { background:var(--bg-card); border-radius:20px; padding:35px; box-shadow:0 5px 20px rgba(0,0,0,0.05); }
        h1 { font-size:22px; margin-bottom:6px; }
        p.subtitle { color:var(--text-muted); font-size:13px; margin-bottom:25px; }
        .form-group { margin-bottom:20px; }
        label { display:block; font-size:13px; font-weight:600; margin-bottom:8px; }
        select, textarea { width:100%; padding:13px 15px; border:2px solid var(--input-border); border-radius:12px; font-size:14px; font-family:inherit; outline:none; }
        select:focus, textarea:focus { border-color:var(--accent-main); }
        textarea { min-height:100px; resize:vertical; }
        .btn-submit { width:100%; background:var(--accent-main); color:var(--text-dark); padding:15px; border-radius:12px; border:none; font-size:14px; font-weight:600; cursor:pointer; }
        .btn-submit:hover { background:var(--accent-hover); }
        .error { background:var(--pastel-red-light); color:var(--pastel-red); padding:10px 14px; border-radius:8px; font-size:12px; margin-top:6px; }
        .hint { font-size:12px; color:var(--text-muted); margin-top:6px; }
        </style>

    <link
        rel="stylesheet"
        href="{{ asset('css/navbar-internal.css') }}"
    >
</head>
<body>
    @include('partials.navbar-internal')

    <div class="container">
        <a href="{{ route('laporan.index') }}" class="back-link">&larr; Kembali ke Daftar Laporan</a>
        <div class="card">
            <h1>Buat Laporan Kerusakan</h1>
            <p class="subtitle">Pilih ruangan dan barang yang rusak. Ruangan hanya menampilkan yang menjadi tanggung jawab Anda.</p>

            <form action="{{ route('laporan.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="ruangan_id">Ruangan</label>
                    <select id="ruangan_id" name="ruangan_id" required onchange="filterInventaris()">
                        <option value="">-- Pilih Ruangan --</option>
                        @forelse ($ruangan as $r)
                            <option value="{{ $r->ruangan_id }}" @selected(old('ruangan_id', $inventaris->firstWhere('inventaris_id', old('inventaris_id'))?->ruangan_id) == $r->ruangan_id)>{{ $r->nama_ruangan }} ({{ $r->kode_ruangan }})</option>
                        @empty
                            <option value="" disabled>Tidak ada ruangan yang menjadi tanggung jawab Anda</option>
                        @endforelse
                    </select>
                    @error('ruangan_id') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="inventaris_id">Barang</label>
                    <select id="inventaris_id" name="inventaris_id" required>
                        <option value="">-- Pilih Ruangan Dulu --</option>
                        @foreach ($inventaris as $i)
                            <option value="{{ $i->inventaris_id }}" data-ruangan="{{ $i->ruangan_id }}" @selected(old('inventaris_id') == $i->inventaris_id) style="display:none;">
                                {{ $i->nama_barang }} (ID: {{ $i->inventaris_id }})
                            </option>
                        @endforeach
                    </select>
                    <p class="hint">Daftar barang otomatis menyesuaikan ruangan yang dipilih.</p>
                    @error('inventaris_id') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="kerusakan">Keterangan Kerusakan</label>
                    <textarea id="kerusakan" name="kerusakan" placeholder="Jelaskan kerusakan yang terjadi..." required>{{ old('kerusakan') }}</textarea>
                    @error('kerusakan') <div class="error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-submit">Kirim Laporan</button>
            </form>
        </div>
    </div>

    <script>
        function filterInventaris() {
            const ruanganId = document.getElementById('ruangan_id').value;
            const select = document.getElementById('inventaris_id');
            let firstVisible = null;

            [...select.options].forEach(opt => {
                if (!opt.dataset.ruangan) return; 
                const cocok = opt.dataset.ruangan === ruanganId;
                opt.style.display = cocok ? '' : 'none';
                opt.disabled = !cocok;
                if (cocok && !firstVisible) firstVisible = opt;
            });

            select.value = firstVisible ? firstVisible.value : '';
        }

        filterInventaris();
        const pilihanSebelumnya = @json(old('inventaris_id'));
        if (pilihanSebelumnya) document.getElementById('inventaris_id').value = pilihanSebelumnya;
    </script>
</body>
</html>
