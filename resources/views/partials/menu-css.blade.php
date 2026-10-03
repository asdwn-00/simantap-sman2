<a href="{{ route('transparansi.index') }}" class="{{ request()->routeIs('transparansi.*') ? 'active' : '' }}">Transparansi</a>
<a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
<a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">Pengajuan Laporan</a>
<a href="{{ route('penugasan.index') }}" class="{{ request()->routeIs('penugasan.*') ? 'active' : '' }}">Penugasan</a>
<a href="{{ route('inventaris.index') }}" class="{{ request()->routeIs('inventaris.*') ? 'active' : '' }}">Inventaris Barang</a>
<a href="{{ route('dana.index') }}" class="{{ request()->routeIs('dana.*') ? 'active' : '' }}">Pengajuan Dana</a>