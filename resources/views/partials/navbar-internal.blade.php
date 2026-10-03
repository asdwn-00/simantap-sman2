@auth('pengguna')
    @php
        $akunNavbar = auth('pengguna')->user();

        $namaRoleNavbar = [
            'pj_lab' => 'PJ Lab',
            'petugas' => 'Petugas Sarpras',
            'koordinator' => 'Koordinator Sarpras',
        ];

        $roleNavbar = $namaRoleNavbar[$akunNavbar->role]
            ?? $akunNavbar->role;
    @endphp

    <nav class="simantap-navbar" aria-label="Navigasi utama">
        <div class="simantap-navbar-inner">
            <div class="simantap-logo">
                SIMAN<span>TAP.</span>
            </div>

            <div class="simantap-menu">
                <a
                    href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('laporan.index') }}"
                    class="{{ request()->routeIs('laporan.*') ? 'is-active' : '' }}"
                >
                    Pengajuan Laporan
                </a>

                <a
                    href="{{ route('penugasan.index') }}"
                    class="{{ request()->routeIs('penugasan.*') ? 'is-active' : '' }}"
                >
                    Penugasan
                </a>

                <a
                    href="{{ route('inventaris.index') }}"
                    class="{{ request()->routeIs('inventaris.*') ? 'is-active' : '' }}"
                >
                    Inventaris Barang
                </a>

                <a
                    href="{{ route('dana.index') }}"
                    class="{{ request()->routeIs('dana.*') ? 'is-active' : '' }}"
                >
                    Pengajuan Dana
                </a>
            </div>

            <div class="simantap-akun">
                <div class="simantap-avatar" aria-hidden="true">
                    {{ \Illuminate\Support\Str::upper(
                        \Illuminate\Support\Str::substr($akunNavbar->nama, 0, 1)
                    ) }}
                </div>

                <span class="simantap-nama">
                    {{ $akunNavbar->nama }} ({{ $roleNavbar }})
                </span>

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="simantap-logout-form"
                >
                    @csrf

                    <button type="submit" class="simantap-logout">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
@endauth