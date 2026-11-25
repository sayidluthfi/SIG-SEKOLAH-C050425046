@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Pengguna
        </h1>

        <a href="{{ route('admin.users.create') }}"
           class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg">
            + Tambah Pengguna
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form method="GET" action="{{ route('admin.users.index') }}"
          class="flex gap-2 mb-4">

        <select name="role"
                class="border rounded-lg px-3 py-2">
            <option value="">Semua role</option>
            <option value="admin" @selected(request('role') === 'admin')>
                Admin
            </option>
            <option value="dinas" @selected(request('role') === 'dinas')>
                Dinas Pendidikan
            </option>
            <option value="sekolah" @selected(request('role') === 'sekolah')>
                Sekolah
            </option>
        </select>

        <button type="submit"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg">
            Filter
        </button>
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto">

        <table class="w-full text-sm text-left text-gray-700">

            <thead class="bg-gray-200 text-gray-800">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Sekolah</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)
                    <tr class="border-t">

                        <td class="px-4 py-3">
                            {{ $user->name }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $user->email }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $user->roleLabel() }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $user->sekolah?->nama ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="text-green-600 font-semibold">
                                    Aktif
                                </span>
                            @else
                                <span class="text-red-600 font-semibold">
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap space-x-2">

                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-blue-600 hover:underline">
                                Ubah
                            </a>

                            @if (!$user->is(auth()->user()))
                                <form
                                    action="{{ route('admin.users.toggle-active', $user) }}"
                                    method="POST"
                                    class="inline"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="{{ $user->is_active ? 'text-red-600' : 'text-green-600' }} hover:underline"
                                        onclick="return confirm('Yakin ingin mengubah status akun ini?')"
                                    >
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <form
                                    action="{{ route('admin.users.destroy', $user) }}"
                                    method="POST"
                                    class="inline"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-red-600 hover:underline"
                                        onclick="return confirm('Yakin ingin menghapus pengguna ini?')"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center px-4 py-6">
                            Belum ada pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

</div>
@endsection
