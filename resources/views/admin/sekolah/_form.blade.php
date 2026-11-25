@php
    $teks = [
        'npsn' => 'NPSN (8 digit)',
        'nama' => 'Nama Sekolah',
        'kecamatan' => 'Kecamatan',
        'telepon' => 'Telepon',
    ];

    $pilihan = [
        'jenjang' => ['SD', 'SMP', 'SMA', 'SMK'],
        'status' => ['Negeri', 'Swasta'],
    ];

    $cls = 'w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 rounded-xl bg-white p-6 shadow">

    {{-- Kolom informasi sekolah --}}
    <div class="space-y-4">

        @foreach ($teks as $name => $label)
            <div>
                <label for="{{ $name }}" class="mb-1 block font-medium text-gray-700">
                    {{ $label }}
                </label>

                <input
                    type="text"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    value="{{ old($name, $sekolah->$name) }}"
                    class="{{ $cls }}"
                    @readonly($name === 'npsn' && auth()->user()->isSekolah())
                    @if ($name === 'npsn')
                        maxlength="8"
                        inputmode="numeric"
                    @endif
                    @if (in_array($name, ['npsn', 'nama', 'kecamatan'], true))
                        required
                    @endif
                >

                @error($name)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        @foreach ($pilihan as $name => $opsi)
            <div>
                <label for="{{ $name }}" class="mb-1 block font-medium text-gray-700">
                    {{ ucfirst($name) }}
                </label>

                <select
                    id="{{ $name }}"
                    name="{{ $name }}"
                    class="{{ $cls }}"
                    required
                >
                    <option value="">-- Pilih {{ ucfirst($name) }} --</option>

                    @foreach ($opsi as $o)
                        <option
                            value="{{ $o }}"
                            @selected(old($name, $sekolah->$name) === $o)
                        >
                            {{ $o }}
                        </option>
                    @endforeach
                </select>

                @error($name)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        {{-- SS 1: Input alamat dan tombol Isi alamat dari peta --}}
        <div>
            <label for="alamat" class="mb-1 block font-medium text-gray-700">
                Alamat
            </label>

            <textarea
                id="alamat"
                name="alamat"
                rows="3"
                class="{{ $cls }}"
                required
            >{{ old('alamat', $sekolah->alamat) }}</textarea>

            @error('alamat')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button
                type="button"
                id="isi-alamat-peta"
                class="mt-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Isi alamat dari peta
            </button>

            <p
                id="status-alamat"
                class="mt-2 text-sm text-gray-500"
                aria-live="polite"
            ></p>
        </div>

    </div>

    {{-- Kolom peta dan koordinat --}}
    <div class="space-y-4">

        <div>
            <p class="font-medium text-gray-700">Lokasi Sekolah</p>

            <p class="mt-1 text-sm text-gray-500">
                Klik peta atau geser marker untuk menentukan lokasi sekolah.
            </p>

            {{-- Tombol Gunakan Lokasi Saya --}}
            <button
                type="button"
                id="gunakan-lokasi"
                style="display: inline-block; background-color: #16a34a; color: white; padding: 10px 16px; border-radius: 8px; margin-top: 12px; cursor: pointer;"
            >
                Gunakan Lokasi Saya
            </button>

            <p
                id="status-lokasi"
                class="mt-2 text-sm text-gray-500"
                aria-live="polite"
            ></p>
        </div>

        {{-- SS 2: Peta Leaflet --}}
        <div
            id="map-picker"
            style="height: 400px; width: 100%;"
            class="rounded-lg border border-gray-300"
        ></div>

        {{-- SS 3: Input koordinat --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
                <label for="latitude" class="mb-1 block font-medium text-gray-700">
                    Latitude
                </label>

                <input
                    type="number"
                    step="any"
                    min="-90"
                    max="90"
                    id="latitude"
                    name="latitude"
                    value="{{ old('latitude', $sekolah->latitude) }}"
                    class="{{ $cls }}"
                    required
                >

                @error('latitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="longitude" class="mb-1 block font-medium text-gray-700">
                    Longitude
                </label>

                <input
                    type="number"
                    step="any"
                    min="-180"
                    max="180"
                    id="longitude"
                    name="longitude"
                    value="{{ old('longitude', $sekolah->longitude) }}"
                    class="{{ $cls }}"
                    required
                >

                @error('longitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Tombol Simpan dan Batal --}}
        <div class="flex flex-wrap gap-3 pt-2">

            <button
                type="submit"
                class="rounded-lg bg-blue-700 px-5 py-2.5 font-medium text-white transition hover:bg-blue-800"
            >
                Simpan
            </button>

            <a
                href="{{ auth()->user()->isDinas()
                    ? route('dinas.sekolah.index')
                    : route('dashboard') }}"
                class="rounded-lg bg-gray-200 px-5 py-2.5 font-medium text-gray-700 transition hover:bg-gray-300"
            >
                Batal
            </a>

        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const mapElement = document.getElementById('map-picker');

    const tombolLokasi = document.getElementById('gunakan-lokasi');
    const statusLokasi = document.getElementById('status-lokasi');

    const tombolAlamat = document.getElementById('isi-alamat-peta');
    const statusAlamat = document.getElementById('status-alamat');
    const alamatInput = document.getElementById('alamat');

    if (
        !latInput || !lngInput || !mapElement ||
        !tombolLokasi || !statusLokasi ||
        !tombolAlamat || !statusAlamat || !alamatInput ||
        typeof L === 'undefined'
    ) {
        console.error('Elemen peta atau Leaflet tidak ditemukan.');
        return;
    }

    // Memeriksa koordinat awal.
    const latAwal = parseFloat(latInput.value);
    const lngAwal = parseFloat(lngInput.value);

    const koordinatValid =
        latInput.value.trim() !== '' &&
        lngInput.value.trim() !== '' &&
        Number.isFinite(latAwal) &&
        Number.isFinite(lngAwal) &&
        latAwal >= -90 && latAwal <= 90 &&
        lngAwal >= -180 && lngAwal <= 180;

    // Pusat Kota Banjarmasin jika koordinat belum tersedia.
    const pusat = koordinatValid
        ? [latAwal, lngAwal]
        : [-3.3194, 114.5908];

    const map = L.map(mapElement).setView(
        pusat,
        koordinatValid ? 16 : 13
    );

    // Menampilkan OpenStreetMap.
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Membuat marker yang dapat digeser.
    const marker = L.marker(pusat, {
        draggable: true
    }).addTo(map);

    // Mengisi koordinat ke input.
    function isiKoordinat(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
    }

    // SS 4: Fungsi reverse geocoding dari koordinat marker.
    async function ambilAlamatDariPeta() {

        const posisi = marker.getLatLng();

        tombolAlamat.disabled = true;
        statusAlamat.textContent = 'Sedang mengambil alamat...';

        statusAlamat.classList.remove('text-red-600', 'text-green-600');

        try {
            const url = new URL(
                'https://nominatim.openstreetmap.org/reverse'
            );

            url.search = new URLSearchParams({
                format: 'jsonv2',
                lat: posisi.lat,
                lon: posisi.lng,
                zoom: 18,
                addressdetails: 1,
                'accept-language': 'id'
            });

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Gagal menghubungi layanan peta.');
            }

            const data = await response.json();

            if (!data.display_name) {
                throw new Error('Alamat tidak ditemukan pada lokasi tersebut.');
            }

            alamatInput.value = data.display_name;

            statusAlamat.textContent = 'Alamat berhasil diisi dari peta.';
            statusAlamat.classList.add('text-green-600');

        } catch (error) {

            statusAlamat.textContent =
                error.message || 'Terjadi kesalahan saat mengambil alamat.';

            statusAlamat.classList.add('text-red-600');

        } finally {
            tombolAlamat.disabled = false;
        }
    }

    // Menjalankan reverse geocoding ketika tombol diklik.
    tombolAlamat.addEventListener('click', ambilAlamatDariPeta);

    // Memilih lokasi dengan mengeklik peta.
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        isiKoordinat(e.latlng.lat, e.latlng.lng);
    });

    // Memperbarui koordinat ketika marker digeser.
    marker.on('dragend', function () {
        const posisi = marker.getLatLng();
        isiKoordinat(posisi.lat, posisi.lng);
    });

    // Memindahkan marker ketika koordinat diubah manual.
    function perbaruiMarker() {

        const latText = latInput.value.trim();
        const lngText = lngInput.value.trim();

        if (latText === '' || lngText === '') return;

        const lat = Number(latText);
        const lng = Number(lngText);

        const valid =
            Number.isFinite(lat) &&
            Number.isFinite(lng) &&
            lat >= -90 && lat <= 90 &&
            lng >= -180 && lng <= 180;

        if (!valid) return;

        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 16);
    }

    latInput.addEventListener('change', perbaruiMarker);
    lngInput.addEventListener('change', perbaruiMarker);

    // Mengambil lokasi pengguna.
    tombolLokasi.addEventListener('click', function () {

        if (!navigator.geolocation) {
            statusLokasi.textContent =
                'Browser tidak mendukung fitur lokasi.';
            return;
        }

        statusLokasi.textContent = 'Sedang mengambil lokasi...';
        tombolLokasi.disabled = true;

        navigator.geolocation.getCurrentPosition(

            function (posisi) {

                const lat = posisi.coords.latitude;
                const lng = posisi.coords.longitude;

                // Mengisi latitude dan longitude.
                isiKoordinat(lat, lng);

                // Memindahkan marker dan peta.
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 17);

                statusLokasi.textContent =
                    'Lokasi berhasil ditemukan!';

                tombolLokasi.disabled = false;
            },

            function (error) {

                let pesan = 'Gagal mendapatkan lokasi.';

                if (error.code === 1) {
                    pesan = 'Izin lokasi ditolak. Izinkan akses lokasi di browser.';
                } else if (error.code === 2) {
                    pesan = 'Informasi lokasi tidak tersedia.';
                } else if (error.code === 3) {
                    pesan = 'Waktu pengambilan lokasi habis.';
                }

                statusLokasi.textContent = pesan;
                tombolLokasi.disabled = false;
            },

            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    });

    // SS 5: Menyesuaikan ukuran peta setelah halaman ditampilkan.
    requestAnimationFrame(function () {
        map.invalidateSize();
    });

});
</script>
@endpush
