
@extends('layouts.app')

@section('title', 'Ubah Kata Sandi')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Ubah Kata Sandi
        </h1>
        <p class="text-gray-600 mt-1">
            Perbarui kata sandi akun SIG Sekolah kamu.
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-5">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-5">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block font-medium text-gray-700 mb-2">
                    Kata Sandi Saat Ini
                </label>
                <input
                    type="password"
                    name="current_password"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            <div class="mb-5">
                <label class="block font-medium text-gray-700 mb-2">
                    Kata Sandi Baru
                </label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            <div class="mb-6">
                <label class="block font-medium text-gray-700 mb-2">
                    Konfirmasi Kata Sandi Baru
                </label>
                <input
                    type="password"
                    name="password_confirmation"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg font-medium"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route('dashboard') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2.5 rounded-lg"
                >
                    Kembali
                </a>
            </div>
        </form>

    </div>
</div>
@endsection
