<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penugasan - SIMANTAP</title>
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
        .navbar { background-color:rgba(255,255,255,0.85); backdrop-filter:blur(12px); padding:20px 60px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 30px rgba(0,0,0,0.04); position:sticky; top:0; z-index:100; }
        .logo { font-size:24px; font-weight:700; } .logo span { color:var(--accent-main); }
        .nav-links { display:flex; gap:30px; align-items:center; }
        .nav-links a { color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:500; }
        .nav-links a.active { color:var(--text-dark); }
        .profile-btn { display:flex; align-items:center; gap:10px; background:var(--pastel-blue-light); padding:8px 15px; border-radius:30px; }
        .profile-btn img { width:32px; height:32px; border-radius:50%; }
        .profile-btn span { font-size:13px; font-weight:600; color:var(--pastel-blue); }
        .container { max-width:1200px; margin:40px auto; padding:0 20px; }
        .card { background-color:var(--bg-card); border-radius:20px; padding:25px; box-shadow:0 8px 24px rgba(0,0,0,0.04); margin-bottom:25px; }
        .section-title { font-size:20px; font-weight:600; margin-bottom:20px; }
        .alert-error { background:var(--pastel-red-light); color:var(--pastel-red); padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
        .right-nav { display:flex; align-items:center; gap:20px; }
        .logout-btn { background:none; border:none; color:var(--text-muted); font-size:13px; font-weight:500; cursor:pointer; }
        .alert-sukses { background:var(--pastel-green-light); color:var(--pastel-green); padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
        .grid-petugas { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:30px; }
        .petugas-card { border:1px solid rgba(0,0,0,0.03); text-align:center; }
        .petugas-card img { width:60px; height:60px; border-radius:50%; margin-bottom:12px; }
        .petugas-card h4 { font-size:15px; margin-bottom:4px; }
        .petugas-card p { font-size:12px; color:var(--text-muted); margin-bottom:10px; }
        .table-responsive { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:650px; }
        th { text-align:left; padding:12px 10px; font-size:13px; color:var(--text-muted); border-bottom:1px solid #EDF2F7; }
        td { padding:12px 10px; font-size:14px; border-bottom:1px solid #EDF2F7; vertical-align:middle; }
        .badge { padding:5px 12px; border-radius:20px; font-size:11px; font-weight:600; display:inline-block; }
        .badge-yellow { background-color:var(--pastel-yellow-light); color:var(--pastel-yellow); }
        .badge-blue { background-color:var(--pastel-blue-light); color:var(--pastel-blue); }
        .badge-green { background-color:var(--pastel-green-light); color:var(--pastel-green); }
        .badge-red { background-color:var(--pastel-red-light); color:var(--pastel-red); }
        .assign-form { display:flex; gap:8px; align-items:center; }
        select { padding:6px 8px; border-radius:8px; border:1px solid #E2E8F0; font-family:inherit; font-size:13px; }
        .btn-primary { background-color:var(--accent-main); color:var(--text-dark); padding:7px 14px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:600; border:none; cursor:pointer; }
        .btn-primary:hover { background-color:var(--accent-hover); }
        .text-blue { color:var(--pastel-blue); text-decoration:none; font-size:13px; font-weight:600; }
        .empty-note { color:var(--text-muted); font-size:13px; font-style:italic; padding:10px 0; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/status-simantap.css') }}">
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

        <h2 class="section-title">Daftar Petugas Lapangan</h2>
        <div class="grid-petugas">
            @forelse ($petugasList as $petugas)
                <div class="petugas-card card">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($petugas->nama) }}&background=E9F2FF&color=4A85D9" alt="Petugas">
                    <h4>{{ $petugas->nama }}</h4>
                    <p>{{ $petugas->email }}</p>
                    @if ($petugas->jumlah_tugas_aktif === 0)
                        <span class="badge badge-green">Tersedia (0 tugas)</span>
                    @else
                        <span class="badge badge-yellow">Sibuk ({{ $petugas->jumlah_tugas_aktif }} tugas)</span>
                    @endif
                </div>
            @empty
                <p class="empty-note">Belum ada akun dengan role petugas.</p>
            @endforelse
        </div>

        @if ($akun->isKoordinator())
        <div class="card">
            <h2 class="section-title">Menunggu Penugasan Pemeriksa</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Barang / Ruangan</th>
                            <th>Keterangan Kerusakan</th>
                            <th>Tugaskan Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($perluPemeriksaan as $laporan)
                            <tr>
                                <td><strong>LAP-{{ str_pad($laporan->laporan_id, 3, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ $laporan->nama_barang }}<br>
                                    <span style="font-size:12px;color:var(--text-muted);">{{ $laporan->nama_ruangan }}</span>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($laporan->kerusakan, 60) }}</td>
                                <td>
                                    <form class="assign-form" method="POST" action="{{ route('penugasan.tugaskan-pemeriksaan', $laporan->laporan_id) }}">
                                        @csrf
                                        <select name="petugas_id" required>
                                            <option value="">Pilih petugas</option>
                                            @foreach ($petugasList as $petugas)
                                                <option value="{{ $petugas->pengguna_id }}">{{ $petugas->nama }} ({{ $petugas->jumlah_tugas_aktif }} tugas)</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-primary">Tugaskan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><p class="empty-note">Tidak ada laporan yang menunggu penugasan pemeriksaan.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2 class="section-title">Menunggu Penugasan Pelaksana</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Barang / Ruangan</th>
                            <th>Rekomendasi</th>
                            <th>Tugaskan Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($perluPelaksanaan as $item)
                            <tr>
                                <td><strong>LAP-{{ str_pad($item->laporan_id, 3, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ $item->nama_barang }}<br>
                                    <span style="font-size:12px;color:var(--text-muted);">{{ $item->nama_ruangan }}</span>
                                </td>
                                <td>
                                    {{ $item->label_rekomendasi }}
                                    @if ($item->sumber_pengganti)
                                        <br><span style="font-size:12px;color:var(--text-muted);">{{ ucfirst(str_replace('_', ' ', $item->sumber_pengganti)) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <form class="assign-form" method="POST" action="{{ route('penugasan.tugaskan-pelaksanaan', $item->pemeriksaan_id) }}">
                                        @csrf
                                        <select name="petugas_id" required>
                                            <option value="">Pilih petugas</option>
                                            @foreach ($petugasList as $petugas)
                                                <option value="{{ $petugas->pengguna_id }}">{{ $petugas->nama }} ({{ $petugas->jumlah_tugas_aktif }} tugas)</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-primary">Tugaskan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><p class="empty-note">Tidak ada pekerjaan yang menunggu penugasan pelaksanaan.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @endif
        <div class="card">
            <h2 class="section-title">Daftar Penugasan</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Petugas</th>
                            <th>Kode Laporan</th>
                            <th>Jenis Tugas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tugasPemeriksaanBerjalan as $tugas)
                            <tr>
                                <td><strong>{{ $tugas->petugas_nama }}</strong><br><span style="font-size:12px;color:var(--text-muted);">{{ $tugas->petugas_email }}</span></td>
                                <td>LAP-{{ str_pad($tugas->laporan_id, 3, '0', STR_PAD_LEFT) }}<br><span style="font-size:12px;color:var(--text-muted);">{{ $tugas->nama_ruangan }}</span></td>
                                <td>Pemeriksaan</td>
                                <td><span class="badge {{ config('simantap.warna_tugas.' . $tugas->status) }}">{{ ucfirst(str_replace('_', ' ', $tugas->status)) }}</span></td>
                                <td><a href="{{ route('laporan.show', $tugas->laporan_id) }}" class="text-blue">Lihat Laporan</a></td>
                            </tr>
                        @endforeach

                        @foreach ($tugasPelaksanaanBerjalan as $tugas)
                            <tr>
                                <td><strong>{{ $tugas->petugas_nama }}</strong><br><span style="font-size:12px;color:var(--text-muted);">{{ $tugas->petugas_email }}</span></td>
                                <td>LAP-{{ str_pad($tugas->laporan_id, 3, '0', STR_PAD_LEFT) }}<br><span style="font-size:12px;color:var(--text-muted);">{{ $tugas->nama_ruangan }}</span></td>
                                <td>Pelaksanaan</td>
                                <td><span class="badge {{ config('simantap.warna_tugas.' . $tugas->status) }}">{{ ucfirst(str_replace('_', ' ', $tugas->status)) }}</span></td>
                                <td><a href="{{ route('laporan.show', $tugas->laporan_id) }}" class="text-blue">Lihat Laporan</a></td>
                            </tr>
                        @endforeach

                        @if ($tugasPemeriksaanBerjalan->isEmpty() && $tugasPelaksanaanBerjalan->isEmpty())
                            <tr><td colspan="5"><p class="empty-note">Tidak ada tugas yang sedang berjalan saat ini.</p></td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
