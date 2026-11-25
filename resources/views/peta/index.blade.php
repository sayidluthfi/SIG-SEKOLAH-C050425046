
@extends('layouts.public')
@section('title', 'Peta Sekolah')

@section('content')
<div class="toolbar">
    <label>Jenjang
        <select id="filter-jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </label>

    <label>Status
        <select id="filter-status">
            <option value="">Semua status</option>
            <option value="Negeri">Negeri</option>
            <option value="Swasta">Swasta</option>
        </select>
    </label>

    <span id="jumlah">Memuat data...</span>

    <button type="button" id="unduh-geojson"
        style="background:#16a34a;color:white;padding:9px 15px;border-radius:6px;cursor:pointer;">
        Unduh GeoJSON
    </button>
</div>

<div id="peta" class="peta"></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = Peta.buat('peta');
    const lapisan = L.layerGroup().addTo(map);

    let controller = null;
    let timer = null;

    async function muat() {
        if (controller) controller.abort();
        controller = new AbortController();

        const jumlah = document.getElementById('jumlah');

        try {
            jumlah.textContent = 'Memuat data...';

            const parameter = new URLSearchParams({
                jenjang: document.getElementById('filter-jenjang').value,
                status: document.getElementById('filter-status').value,
                bbox: map.getBounds().toBBoxString()
            });

            const response = await fetch(
                `${window.PETA_URL.data}?${parameter}`,
                { signal: controller.signal }
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (!Array.isArray(data.features)) {
                throw new Error('Format GeoJSON tidak valid.');
            }

            lapisan.clearLayers();

            data.features.forEach(function (fitur) {
                if (fitur.geometry?.type !== 'Point') return;

                const marker = L.marker(Peta.latlng(fitur))
                    .bindPopup(Peta.popup(fitur));

                marker.addTo(lapisan);
            });

            jumlah.textContent =
                data.features.length + ' sekolah dalam area peta';

        } catch (error) {
            if (error.name === 'AbortError') return;

            console.error('Marker:', error);
            jumlah.textContent = 'Gagal memuat data sekolah';
        }
    }

    function terapkanFilter() {
        muat();
    }

    document.getElementById('filter-jenjang')
        .addEventListener('change', terapkanFilter);

    document.getElementById('filter-status')
        .addEventListener('change', terapkanFilter);

    map.on('moveend', function () {
        clearTimeout(timer);
        timer = setTimeout(muat, 300);
    });

    document.getElementById('unduh-geojson')
        .addEventListener('click', async function () {
            const tombol = this;
            tombol.disabled = true;
            tombol.textContent = 'Mengunduh...';

            try {
                const parameter = new URLSearchParams({
                    jenjang: document.getElementById('filter-jenjang').value,
                    status: document.getElementById('filter-status').value
                });

                const response = await fetch(
                    `${window.PETA_URL.data}?${parameter}`
                );

                if (!response.ok) {
                    throw new Error('Gagal mengunduh GeoJSON.');
                }

                const data = await response.json();

                const blob = new Blob(
                    [JSON.stringify(data, null, 2)],
                    { type: 'application/geo+json' }
                );

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = url;
                link.download = 'data-sekolah.geojson';

                document.body.appendChild(link);
                link.click();
                link.remove();

                URL.revokeObjectURL(url);

            } catch (error) {
                console.error(error);
                alert(error.message);

            } finally {
                tombol.disabled = false;
                tombol.textContent = 'Unduh GeoJSON';
            }
        });

    muat();
});
</script>
@endpush
