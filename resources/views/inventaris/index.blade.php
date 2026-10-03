<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris - SIMANTAP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #F4F7FE; --bg-card: #FFFFFF; --text-dark: #2B3674; --text-muted: #A3AED0;
            --accent-main: #FFB800; --accent-hover: #E5A600;
            --pastel-blue-light: #E9F2FF; --pastel-blue: #4A85D9;
            --pastel-yellow-light: #FFF6D9; --pastel-yellow: #D4A017;
            --pastel-green-light: #D9F4EE; --pastel-green: #00B69B;
            --pastel-red-light: #FFE5E5; --pastel-red: #FF6B6B;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color:var(--bg-main); color:var(--text-dark); min-height:100vh; }
        .navbar { background-color:rgba(255,255,255,0.85); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); padding:20px 60px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 30px rgba(0,0,0,0.04); position:sticky; top:0; z-index:100; border-bottom:1px solid rgba(255,255,255,0.3); }
        .logo { font-size:24px; font-weight:700; } .logo span { color:var(--accent-main); }
        .nav-links { display:flex; gap:30px; align-items:center; }
        .nav-links a { color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:500; transition:0.3s; position:relative; }
        .nav-links a:hover, .nav-links a.active { color:var(--text-dark); }
        .nav-links a.active::after { content:''; position:absolute; bottom:-5px; left:0; width:100%; height:2px; background-color:var(--accent-main); border-radius:2px; }
        .right-nav { display:flex; align-items:center; gap:20px; }
        .profile-btn { display:flex; align-items:center; gap:10px; background:var(--pastel-blue-light); padding:8px 15px; border-radius:30px; }
        .profile-btn img { width:32px; height:32px; border-radius:50%; }
        .profile-btn span { font-size:13px; font-weight:600; color:var(--pastel-blue); }
        .logout-btn { background:none; border:none; color:var(--text-muted); font-size:13px; font-weight:500; cursor:pointer; }
        .container { max-width:1200px; margin:40px auto; padding:0 20px; }
        .card { background-color:var(--bg-card); border-radius:20px; padding:25px; box-shadow:0 8px 24px rgba(0,0,0,0.04); margin-bottom:25px; border:1px solid rgba(255,255,255,0.4); }
        .section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px; }
        .section-title { font-size:20px; font-weight:600; }
        .btn-primary { background-color:var(--accent-main); color:var(--text-dark); padding:10px 20px; border-radius:10px; text-decoration:none; font-size:13px; font-weight:600; border:none; cursor:pointer; transition:all .3s ease; box-shadow:0 4px 10px rgba(255,184,0,0.2); display:inline-block; }
        .btn-primary:hover { background-color:var(--accent-hover); box-shadow:0 6px 15px rgba(255,184,0,0.3); transform:translateY(-2px); }
        .btn-soft { background:var(--pastel-blue-light); color:var(--pastel-blue); box-shadow:none; }
        .btn-sm { padding:6px 12px; border-radius:8px; }
        .filter-bar { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
        .filter-bar input, .filter-bar select, .pj-form select { padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:13px; font-family:inherit; }
        .table-responsive { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:700px; }
        th { text-align:left; padding:15px 10px; font-size:13px; color:var(--text-muted); border-bottom:1px solid #EDF2F7; }
        td { padding:15px 10px; font-size:14px; border-bottom:1px solid #EDF2F7; vertical-align:middle; }
        tbody tr:hover { background-color:#F8FAFC; }
        .badge { padding:5px 12px; border-radius:20px; font-size:11px; font-weight:600; display:inline-block; }
        .badge-green { background-color:var(--pastel-green-light); color:var(--pastel-green); }
        .badge-red { background-color:var(--pastel-red-light); color:var(--pastel-red); }
        .badge-yellow { background-color:var(--pastel-yellow-light); color:var(--pastel-yellow); }
        .badge-blue { background-color:var(--pastel-blue-light); color:var(--pastel-blue); }
        .muted { font-size:12px; color:var(--text-muted); }
        .action-links summary { list-style:none; cursor:pointer; font-size:13px; font-weight:600; color:var(--pastel-blue); }
        .action-links summary::-webkit-details-marker { display:none; }
        .action-links summary:hover { opacity:.7; }
        .pj-form { display:flex; gap:8px; align-items:center; margin-top:8px; }
        .pj-form select { padding:6px 8px; border-radius:8px; }
        .alert-sukses { background:var(--pastel-green-light); color:var(--pastel-green); padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
        .alert-error { background:var(--pastel-red-light); color:var(--pastel-red); padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
        .empty { text-align:center; padding:40px; color:var(--text-muted); font-size:14px; }
        .pagination { margin-top:20px; }
        @media (max-width:768px) {
            .navbar { flex-direction:column; padding:20px; gap:20px; }
            .nav-links { flex-wrap:wrap; justify-content:center; gap:15px; }
        }
        .tambah-barang {margin-bottom: 20px; }
        .tambah-barang > summary {list-style: none; width: fit-content; }
        .tambah-barang > summary::-webkit-details-marker {display: none; }
        .form-tambah-barang {
            margin-top: 16px;
            padding: 24px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
        }

        .form-tambah-barang h3 {
            margin-bottom: 6px;
            font-size: 18px;
        }

        .form-tambah-barang > .muted {
            margin-bottom: 18px;
        }

        .form-barang-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-tambah-barang label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .form-tambah-barang input,
        .form-tambah-barang select,
        .form-tambah-barang textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            background: white;
            color: var(--text-dark);
            font-size: 13px;
        }

        .form-tambah-barang textarea {
            resize: vertical;
        }

        .form-barang-lebar {
            grid-column: 1 / -1;
        }

        .form-barang-tombol {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .form-tambah-barang button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .daftar-error-barang {
            padding-left: 20px;
            margin-top: 8px;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
</head>
<body>

    @include('partials.navbar-internal')

    <div class="container">
        @if (session('sukses'))
            <div class="alert-sukses">{{ session('sukses') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="card">
            <div class="section-header">
                <h2 class="section-title">Master Data Inventaris</h2>
                <a href="{{ route('inventaris.index') }}" class="btn-primary">Muat Ulang Data</a>
            </div>

            @include('inventaris.form-tambah')

            <form method="GET" action="{{ route('inventaris.index') }}" class="filter-bar">
                <input type="text" name="cari" placeholder="Cari ID / nama barang" value="{{ request('cari') }}">
                <select name="ruangan" onchange="this.form.submit()">
                    <option value="">Semua Ruangan</option>
                    @foreach ($daftarRuangan as $r)
                        <option value="{{ $r->ruangan_id }}" @selected((string) request('ruangan') === (string) $r->ruangan_id)>{{ $r->nama_ruangan }}</option>
                    @endforeach
                </select>
                <select name="kondisi" onchange="this.form.submit()">
                    <option value="">Semua Kondisi</option>
                    @foreach ($daftarKondisi as $val => $label)
                        <option value="{{ $val }}" @selected(request('kondisi') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary btn-soft">Cari</button>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Barang</th>
                            <th>Nama &amp; Kategori</th>
                            <th>Lokasi (Ruangan)</th>
                            <th>Penanggung Jawab Ruangan</th>
                            <th>Kondisi Saat Ini</th>
                            <th>Status Penggunaan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($inventaris as $item)
                            @php
                                $badgeKondisi = match ($item->kondisi) {
                                    'baik' => 'badge-green',
                                    'rusak_ringan' => 'badge-yellow',
                                    'rusak_berat' => 'badge-red',
                                    default => 'badge-blue',
                                };

                                $labelPenggunaan = match ($item->status_penggunaan) {
                                    'tersedia' => 'Tersedia',
                                    'digunakan' => 'Digunakan',
                                    'tidak_digunakan' => 'Tidak Digunakan',
                                    default => 'Belum diketahui',
                                };

                                $badgePenggunaan = match ($item->status_penggunaan) {
                                    'tersedia' => 'badge-blue',
                                    'digunakan' => 'badge-green',
                                    default => 'badge-yellow',
                                };

                                $isLab = str_starts_with($item->jenis_ruangan, 'lab_');

                                $namaPenanggung = $isLab
                                    ? $item->pj_nama
                                    : $item->petugas_nama;
                            @endphp

                            <tr>
                                <td>{{ $item->inventaris_id }}</td>

                                <td>
                                    <strong>{{ $item->nama_barang }}</strong>
                                    <br>
                                    <span class="muted">{{ $item->kategori }}</span>
                                </td>

                                <td>{{ $item->nama_ruangan }}</td>

                                <td>
                                    @if ($namaPenanggung)
                                        {{ $namaPenanggung }}
                                        <br>
                                        <span class="muted">
                                            {{ $isLab ? 'PJ Lab' : 'Petugas Sarpras' }}
                                        </span>
                                    @else
                                        <span class="muted">Belum ditetapkan</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $badgeKondisi }}">
                                        {{ $daftarKondisi[$item->kondisi] ?? $item->kondisi }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $badgePenggunaan }}">
                                        {{ $labelPenggunaan }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">
                                    Belum ada inventaris yang sesuai dengan
                                    cakupan atau pencarian Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $inventaris->links() }}</div>
        </div>
    </div>
</body>
</html>
