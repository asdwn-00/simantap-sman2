<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Laporan - SIMANTAP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-main:#F4F7FE; --bg-card:#FFFFFF; --text-dark:#2B3674; --text-muted:#A3AED0; --accent-main:#FFB800; --accent-hover:#E5A600; --input-border:#E2E8F0; --pastel-red:#FF6B6B; --pastel-red-light:#FFE5E5; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-dark); min-height: 100vh; padding: 0; }
        .container { width: calc(100% - 40px); max-width: 600px; margin: 40px auto; }
        .back-link { display:inline-block; margin-bottom:20px; color:var(--text-muted); text-decoration:none; font-size:13px; font-weight:600; }
        .card { background:var(--bg-card); border-radius:20px; padding:35px; box-shadow:0 5px 20px rgba(0,0,0,0.05); }
        h1 { font-size:22px; margin-bottom:20px; }
        .form-group { margin-bottom:20px; }
        label { display:block; font-size:13px; font-weight:600; margin-bottom:8px; }
        textarea { width:100%; padding:13px 15px; border:2px solid var(--input-border); border-radius:12px; font-size:14px; font-family:inherit; min-height:120px; resize:vertical; outline:none; }
        .btn-submit { width:100%; background:var(--accent-main); color:var(--text-dark); padding:15px; border-radius:12px; border:none; font-size:14px; font-weight:600; cursor:pointer; }
        .error { background:var(--pastel-red-light); color:var(--pastel-red); padding:10px 14px; border-radius:8px; font-size:12px; margin-top:6px; }
        </style>

    <link
        rel="stylesheet"
        href="{{ asset('css/navbar-internal.css') }}"
    >
</head>
<body>
    @include('partials.navbar-internal')

    <div class="container">
        <a href="{{ route('laporan.show', $laporan) }}" class="back-link">&larr; Kembali ke Detail</a>
        <div class="card">
            <h1>Ubah Laporan {{ $laporan->kode_laporan }}</h1>
            <form action="{{ route('laporan.update', $laporan) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="kerusakan">Keterangan Kerusakan</label>
                    <textarea id="kerusakan" name="kerusakan" required>{{ old('kerusakan', $laporan->kerusakan) }}</textarea>
                    @error('kerusakan') <div class="error">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</body>
</html>
