<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'SIG Sekolah') - SIG Sekolah</title>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">

    <nav class="bg-blue-800 text-white">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-6">

            <span class="font-bold">SIG Sekolah</span>

            <a href="{{ url('/') }}" class="hover:text-blue-200">
                Peta Publik
            </a>

            {{-- Menu berdasarkan role pengguna --}}
            @auth

                {{-- Menu Admin --}}
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('dashboard') }}"
                       class="hover:text-blue-200">
                        Dashboard
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="hover:text-blue-200">
                        Pengguna
                    </a>

                {{-- Menu Dinas --}}
                @elseif (auth()->user()->isDinas())
                    <a href="{{ route('dinas.sekolah.index') }}"
                       class="hover:text-blue-200">
                        Data Sekolah
                    </a>

                {{-- Menu Sekolah --}}
                @elseif (auth()->user()->isSekolah())
                    <a href="{{ route('dashboard') }}"
                       class="hover:text-blue-200">
                        Dashboard
                    </a>

                    <a href="{{ route('sekolah.edit', auth()->user()->sekolah_id) }}"
                       class="hover:text-blue-200">
                        Sekolah Saya
                    </a>
                @endif

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                    @csrf
                    <button type="submit" class="hover:text-blue-200 underline">
                        Keluar ({{ auth()->user()->name }})
                    </button>
                </form>

            @else
                <a href="{{ route('login') }}"
                   class="ml-auto hover:text-blue-200">
                    Masuk
                </a>
            @endauth

        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6">

        {{-- Notifikasi berhasil --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Notifikasi error --}}
        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- Error validasi --}}
        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')

</body>
</html>
