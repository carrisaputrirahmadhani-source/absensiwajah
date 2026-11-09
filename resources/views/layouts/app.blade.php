<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presensi Wajah') | 12 RPL A</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
@php $nav = 'rounded-lg px-3 py-2 hover:bg-slate-100'; $aktif = 'bg-indigo-50 text-indigo-700'; @endphp
<header class="bg-white shadow-sm">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <a href="{{ route('beranda') }}" class="flex items-center gap-2 text-lg font-bold text-indigo-700">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-600 text-white">📷</span>
            Presensi Wajah
        </a>
        <nav class="flex flex-wrap items-center gap-1 text-sm font-medium">
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.siswa.index') }}" class="{{ $nav }} {{ request()->routeIs('admin.siswa.*') ? $aktif : '' }}">Data Siswa</a>
                    <a href="{{ route('admin.pengguna.index') }}" class="{{ $nav }} {{ request()->routeIs('admin.pengguna.*') ? $aktif : '' }}">Pengguna</a>
                @endif
                @if (auth()->user()->role === 'guru')
                    <a href="{{ route('guru.wajah.index') }}" class="{{ $nav }} {{ request()->routeIs('guru.*') ? $aktif : '' }}">Pendaftaran Wajah</a>
                @endif
                @if (in_array(auth()->user()->role, ['guru', 'sekretaris']))
                    <a href="{{ route('rekap.index') }}" class="{{ $nav }} {{ request()->routeIs('rekap.*') ? $aktif : '' }}">Rekap Absensi</a>
                @endif
                <span class="px-2 text-slate-600">{{ auth()->user()->nama_petugas }} <span class="text-slate-400">({{ ucfirst(auth()->user()->role) }})</span></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg bg-slate-800 px-3 py-2 text-white hover:bg-slate-700">Keluar</button>
                </form>
            @else
              
                <a href="{{ route('login') }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700">Login Petugas</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    @if (session('sukses'))
        <div class="mx-auto mt-4 max-w-6xl px-4">
            <div class="rounded-lg bg-emerald-100 px-4 py-3 text-emerald-800">{{ session('sukses') }}</div>
        </div>
    @endif
    @if ($errors->any())
        <div class="mx-auto mt-4 max-w-6xl px-4">
            <div class="rounded-lg bg-red-100 px-4 py-3 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>