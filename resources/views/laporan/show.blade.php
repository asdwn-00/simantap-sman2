<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail {{ $laporan->kode_laporan }} - SIMANTAP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main:#F4F7FE; --bg-card:#FFFFFF; --text-dark:#2B3674; --text-muted:#A3AED0;
            --accent-main:#FFB800; --accent-hover:#E5A600; --pastel-blue-light:#E9F2FF; --pastel-blue:#4A85D9;
            --pastel-red:#FF6B6B; --pastel-red-light:#FFE5E5; --pastel-green:#00B69B; --pastel-green-light:#D9F4EE;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color:var(--bg-main); color:var(--text-dark); min-height:100vh; }
        .navbar { background-color:var(--bg-card); padding:20px 60px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(0,0,0,0.02); position:sticky; top:0; z-index:100; }
        .logo { font-size:24px; font-weight:700; } .logo span { color:var(--accent-main); }
        .nav-links { display:flex; gap:30px; align-items:center; }
        .nav-links a { color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:500; }
        .nav-links a.active { color:var(--text-dark); }
        .container { max-width:850px; margin:40px auto; padding:0 20px; }
        .back-link { display:inline-block; margin-bottom:20px; color:var(--text-muted); text-decoration:none; font-size:13px; font-weight:600; }
        .header-card { background:var(--text-dark); color:white; border-radius:20px; padding:30px; margin-bottom:25px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:15px; }
        .header-card h1 { font-size:22px; margin-bottom:6px; }
        .header-card p { font-size:13px; color:#D1D5DB; }
        .badge-status { background:var(--accent-main); color:var(--text-dark); padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700; height:fit-content; }
        .section { background:var(--bg-card); border-radius:16px; padding:25px; margin-bottom:20px; box-shadow:0 5px 20px rgba(0,0,0,0.02); }
        .section h3 { font-size:15px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center; }
        .row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #F1F5F9; font-size:14px; }
        .row:last-child { border-bottom:none; }
        .row .label { color:var(--text-muted); }
        .empty-note { color:var(--text-muted); font-size:13px; font-style:italic; }
        .actions { display:flex; gap:10px; margin-top:15px; }
        .btn { padding:9px 16px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; border:none; cursor:pointer; }
        .btn-edit { background:var(--pastel-blue-light); color:var(--pastel-blue); }
        .btn-delete { background:var(--pastel-red-light); color:var(--pastel-red); }
        .alert-sukses { background:var(--pastel-green-light); color:var(--pastel-green); padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
        .form-progres { margin: 20px 0; padding-top: 20px; border-top: 1px solid #E5E7EB; }
        .form-progres label { display: block; margin: 14px 0 6px; font-size: 13px; font-weight: 600;}
        .form-progres select,
        .form-progres textarea { display: block; width: 100%; padding: 11px 13px; border: 1px solid #E5E7EB; border-radius: 10px; background: #F8FAFC; color: var(--text-dark); font-size: 13px;}
        .form-progres textarea { resize: vertical; }
        .progres-petunjuk { margin: 10px 0 14px; color: #64748B; font-size: 12px; line-height: 1.6; }
        .progres-error { margin: 15px 0; padding: 14px 18px; border-radius: 10px; background: #FFE5E5; color: #B91C1C; font-size: 13px; }
        .progres-error ul { padding-left: 18px;}
    </style>
    <link rel="stylesheet" href="{{ asset('css/navbar-internal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/status-simantap.css') }}">
</head>
<body>
    
    @include('partials.navbar-internal')

    <div class="container">
        <a href="{{ route('laporan.index') }}" class="back-link">&larr; Kembali ke Daftar Laporan</a>

        @if (session('sukses'))
            <div class="alert-sukses">{{ session('sukses') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="alert-sukses" style="background:#FFE5E5; color:#B91C1C;">{{ $errors->first() }}</div>
        @endif

        <div class="header-card">
            <div>
                <h1>{{ $laporan->kode_laporan }} | {{ $laporan->inventaris->nama_barang ?? '-' }}</h1>
                @if (
                    (int) $inventarisSaatIni->inventaris_id
                    !== (int) $laporan->inventaris_id
                )
                    <div class="row">
                        <span class="label">Unit pengganti terakhir</span>
                        <span>
                            ID: {{ $inventarisSaatIni->inventaris_id }}
                            | {{ $inventarisSaatIni->nama_barang }}
                        </span>
                    </div>
                @endif
                <p>{{ $laporan->ruangan->nama_ruangan ?? '-' }} • Dilaporkan oleh {{ $laporan->pelapor->nama ?? '-' }} • {{ $laporan->tanggal_laporan?->format('d M Y H:i') }}</p>
            </div>
            <span class="badge-status {{ config('simantap.warna_laporan.' . $laporan->status_laporan) }}">{{ ucfirst(str_replace('_', ' ', $laporan->status_laporan)) }}</span>
        </div>

        <div class="section">
            <h3>Informasi Awal</h3>
            <div class="row"><span class="label">Barang</span><span>{{ $laporan->inventaris->nama_barang ?? '-' }}</span></div>
            <div class="row"><span class="label">Ruangan saat dilaporkan</span><span>{{ $laporan->ruangan->nama_ruangan ?? '-' }}</span></div>
            <div class="row"><span class="label">Keterangan kerusakan</span><span>{{ $laporan->kerusakan }}</span></div>
            <div class="row"><span class="label">Prioritas</span><span>{{ $laporan->prioritas ? ucfirst($laporan->prioritas) : 'Belum ditetapkan koordinator' }}</span></div>

            @if ($bisaUbahHapus)
                <div class="actions">
                    <a href="{{ route('laporan.edit', $laporan) }}" class="btn btn-edit">Ubah Laporan</a>
                    <form action="{{ route('laporan.destroy', $laporan) }}" method="POST" onsubmit="return confirm('Yakin hapus laporan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete">Hapus Laporan</button>
                    </form>
                </div>
            @endif
        </div>

        @php
            $pAktif = \App\Services\AlurLaporan::pemeriksaan($laporan);
            $modal = null;
            $aksi = null;
            if ($pAktif && \App\Services\AlurLaporan::bolehIsi($laporan, $pAktif, $akun)) {
                $modal = 'modalPemeriksaan'; $aksi = 'Isi Pemeriksaan';
            } elseif ($akun->isKoordinator() && $pAktif && \App\Services\AlurLaporan::bolehTinjau($laporan, $pAktif)) {
                $modal = 'modalTinjau'; $aksi = 'Tinjau Rekomendasi';
            } elseif (\App\Services\AlurLaporan::bolehKonfirmasi($laporan, $akun)) {
                $modal = 'modalKonfirmasi'; $aksi = 'Konfirmasi Hasil';
            } elseif ($akun->isKoordinator() && \App\Services\AlurLaporan::bolehTutup($laporan)) {
                $modal = 'modalTutup'; $aksi = 'Tutup Laporan';
            }
        @endphp
        @if ($modal)
            <div class="section"><a class="btn btn-edit" href="{{ route('laporan.index', ['cari' => $laporan->kode_laporan, 'buka' => $modal.'-'.$laporan->laporan_id]) }}">{{ $aksi }}</a></div>
        @endif

        <div class="section">
            <h3>Pemeriksaan</h3>
            @forelse ($laporan->pemeriksaan as $p)
                <div class="row"><span class="label">Petugas</span><span>{{ $p->petugas->nama ?? '-' }}</span></div>
                <div class="row"><span class="label">Temuan</span><span>{{ $p->temuan ?? '-' }}</span></div>
                <div class="row"><span class="label">Rekomendasi</span><span>{{ $p->label_rekomendasi }}</span></div>
                @if ($p->alasan_penggantian)
                    <div class="row"><span class="label">Alasan penggantian</span><span>{{ $p->alasan_penggantian }}</span></div>
                @endif
                <div class="row"><span class="label">Keputusan rekomendasi</span><span>{{ ucfirst(str_replace('_', ' ', $p->status_persetujuan)) }}</span></div>
                @if ($p->catatan_koordinator)
                    <div class="row"><span class="label">Catatan koordinator</span><span>{{ $p->catatan_koordinator }}</span></div>
                @endif
                <div class="row"><span class="label">Status</span><span class="{{ config('simantap.warna_tugas.' . $p->status_pemeriksaan) }}">{{ ucfirst($p->status_pemeriksaan) }}</span></div>
            @empty
                <p class="empty-note">Belum ada pemeriksaan. Koordinator perlu menugaskan petugas dari halaman Penugasan.</p>
            @endforelse
        </div>

        <div class="section">
            <h3>Pengajuan Dana <a class="btn btn-edit" href="{{ route('dana.index') }}">Lihat Pengajuan Dana</a></h3>
            @forelse ($laporan->pengajuanDana as $pd)
                <div class="row"><span class="label">Pengajuan #{{ $pd->pengajuan_id }}</span><span>{{ ucfirst($pd->status_pengajuan) }}</span></div>
                <div class="row"><span class="label">Kebutuhan</span><span>{{ $pd->rincian_kebutuhan }}</span></div>
                @unless ($akun->isPjLab())
                <div class="row"><span class="label">Estimasi</span><span>Rp {{ number_format($pd->estimasi_biaya, 0, ',', '.') }}</span></div>
                <div class="row"><span class="label">Dibuat oleh</span><span>{{ $pd->pembuat->nama ?? '-' }}</span></div>
                @endunless
            @empty
                <p class="empty-note">Pengajuan dana belum dibuat.</p>
            @endforelse
        </div>

        <div class="section">
            <h3>Pelaksanaan</h3>
            @forelse ($laporan->penindaklanjutan as $pl)
                <div class="row"><span class="label">Petugas</span><span>{{ $pl->petugas->nama ?? '-' }}</span></div>
                <div class="row"><span class="label">Status</span><span class="{{ config('simantap.warna_tugas.' . $pl->status_tindakan) }}">{{ ucfirst($pl->status_tindakan) }}</span></div>
                <div class="row"><span class="label">Hasil</span><span>{{ $pl->hasil ?? '-' }}</span></div>
                @if ($pl->inventarisPengganti)
                    <div class="row">
                        <span class="label">Barang Pengganti</span>
                        <span>
                            ID: {{ $pl->inventarisPengganti->inventaris_id }}
                            | {{ $pl->inventarisPengganti->nama_barang }}
                        </span>
                    </div>
                @endif
                <div class="row"><span class="label">Tanggal Mulai</span><span>{{ $pl->tanggal_mulai?->format('d M Y H:i') ?? 'Belum dimulai' }}</span></div>
                @if (
                    \App\Services\AlurLaporan::bolehMulai($laporan, $akun)
                    && (int) \App\Services\AlurLaporan::tindakan($laporan)?->penugasan_id
                        === (int) $pl->penugasan_id
                )
                    <form action="{{ route('penugasan.mulai', $pl->penugasan_id) }}" method="POST" class="actions">
                        @csrf

                        <button type="submit" class="btn btn-edit">
                            Mulai Pekerjaan
                        </button>
                    </form>
                @endif

                <div class="row">
                    <span class="label">Catatan Pengerjaan</span>
                    <span style="max-width:65%; white-space:pre-wrap; overflow-wrap:anywhere;">{{ $pl->catatan_tindakan ?: '-' }}</span>
                </div>

                <div class="row">
                    <span class="label">Kendala</span>
                    <span style="max-width:65%; white-space:pre-wrap; overflow-wrap:anywhere;">{{ $pl->kendala ?: '-' }}</span>
                </div>

                @include('penugasan.form-progres')

                @if ($pl->konfirmasi)
                    <div class="row">
                        <span class="label">Konfirmasi</span>
                        <span>
                            {{ $pl->konfirmasi->hasil_konfirmasi === 'sesuai'
                                ? 'Sesuai'
                                : 'Masih Bermasalah' }}
                        </span>
                    </div>
                @elseif ($pl->status_tindakan === 'selesai')
                    <p class="empty-note">
                        Menunggu konfirmasi dari pelapor awal.
                    </p>
                @elseif ($pl->status_tindakan === 'dibatalkan')
                    <p class="empty-note">
                        Penugasan ini dibatalkan.
                    </p>
                @else
                    <p class="empty-note">
                        Konfirmasi dilakukan setelah pekerjaan selesai.
                    </p>
                @endif
            @empty
                <p class="empty-note">Belum ada penugasan pelaksanaan.</p>
            @endforelse
        </div>

        <div class="section">
            <h3>Penutupan</h3>
            @if ($laporan->status_laporan === 'dihentikan')
                <p><strong>Laporan dihentikan oleh koordinator.</strong></p>
                <p>{{ $laporan->alasan_penghentian }}</p>
                <p>Tanggal penghentian: {{ $laporan->tanggal_ditutup?->format('d M Y H:i') }}</p>
                <p class="empty-note">Proses berakhir tanpa konfirmasi keberhasilan penanganan.</p>
            @elseif ($laporan->status_laporan === 'selesai')
                <p>Laporan ini sudah ditutup oleh koordinator.</p>
            @else
                <p class="empty-note">Laporan belum ditutup.</p>
            @endif
        </div>
    </div>
</body>
</html>
