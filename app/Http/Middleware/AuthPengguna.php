<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthPengguna
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('pengguna')->check()) {
            return redirect()->guest(route('login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        abort_unless(in_array(Auth::guard('pengguna')->user()->role, ['pj_lab', 'petugas', 'koordinator']), 403);

        return $next($request);
    }
}
