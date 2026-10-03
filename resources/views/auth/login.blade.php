<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMANTAP - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #2B4885; border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen relative flex items-center justify-center p-4 md:p-8">

    <div class="absolute inset-0 z-0">
        <img src="{{ asset('images/smada-pic.png') }}" alt="School Background" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 z-0 bg-gradient-to-r from-[#2B4885]/90 via-[#2B4885]/70 to-[#F5C518]/40 backdrop-blur-[4px]"></div>

    <div class="relative z-10 w-full max-w-5xl bg-white/10 backdrop-blur-md border border-white/30 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] rounded-3xl flex flex-col md:flex-row items-center p-8 md:p-14 gap-10 md:gap-16">

        <div class="w-full md:w-1/2 flex flex-col text-white">
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-5 drop-shadow-lg">
                SIMAN<span class="text-[#F5C518]">TAP.</span>
            </h1>
            <h2 class="text-xl md:text-2xl font-semibold leading-snug mb-4 drop-shadow-md">
                Sistem Informasi<br>
                Manajemen Aset dan<br>
                Inventaris Terpadu
            </h2>
            <div class="w-16 h-1.5 bg-[#F5C518] mb-5 rounded-full shadow-md"></div>
            <p class="text-white/95 text-sm md:text-base leading-relaxed max-w-md drop-shadow-md font-medium">
                Kelola laporan kerusakan, pengajuan dana, dan penugasan perbaikan SMAN 2 Surabaya dengan cepat dan terintegrasi
            </p>

            <a href="{{ route('transparansi.index') }}" class="mt-8 inline-flex items-center text-sm font-semibold text-white/90 hover:text-[#F5C518] transition-colors drop-shadow-md w-fit">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke halaman Transparansi
            </a>
        </div>

        <div class="w-full md:w-1/2">
            <h3 class="text-2xl font-bold text-white mb-1.5 drop-shadow-md">Selamat Datang.</h3>
            <p class="text-white font-medium text-sm mb-6 drop-shadow-md">Silakan masukkan kredensial akun Anda.</p>

            @if ($errors->any())
                <div class="mb-5 bg-red-500/20 border border-red-300/50 text-white text-sm font-semibold rounded-xl px-4 py-3 backdrop-blur-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 bg-emerald-500/20 border border-emerald-300/50 text-white text-sm font-semibold rounded-xl px-4 py-3 backdrop-blur-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-white mb-2 drop-shadow-md">Username / Email</label>
                    <div class="relative">
                        <input type="text" id="email" name="email" value="{{ old('email') }}" autofocus
                            class="w-full bg-black/30 border border-white/40 rounded-xl px-4 py-3.5 text-sm text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-[#F5C518] focus:border-transparent transition-all shadow-inner backdrop-blur-sm"
                            placeholder="Contoh: koordinator@sman2sby.sch.id">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-white mb-2 drop-shadow-md">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="w-full bg-black/30 border border-white/40 rounded-xl px-4 py-3.5 text-sm text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-[#F5C518] focus:border-transparent transition-all shadow-inner backdrop-blur-sm"
                            placeholder="Masukkan password Anda">

                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" onclick="togglePassword()">
                            <svg id="eye-icon" class="h-5 w-5 text-white/80 hover:text-white transition-colors drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full flex justify-center py-3.5 px-4 mt-4 border border-transparent rounded-xl shadow-lg text-base font-bold text-[#2B4885] bg-[#F5C518] hover:bg-[#e3b615] hover:shadow-xl transform hover:-translate-y-0.5 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F5C518] focus:ring-offset-[#1a2c5b]">
                    Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>
