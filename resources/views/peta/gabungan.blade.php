@extends('layouts.public')
@section('title', 'Peta Gabungan')

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
<link rel="stylesheet"
      href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
@endpush

@section('content')
<div class="toolbar">
    <label>Jenjang
        <select id="jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </label>

    <label>Status
        <select id="status">
            <option value="">Semua status</option>
            <option value="Negeri">Negeri</option>
            <option value="Swasta">Swasta</option>
        </select>
    </label>

    <label>Bobot panas
        <select id="mode">
            <option value="sekolah">Jumlah sekolah</option>
            <option value="siswa">Jumlah siswa</option>
        </select>
    </label>

    <span id="jumlah"></span>
</div>

<div id="peta" class="peta"></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<script>
    const map = Peta.buat('peta');

    // Layer marker biasa
    const markerLayer = L.layerGroup();

    // Layer klaster
    const clusterLayer = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 60,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 17,
        chunkedLoading: true,
    });

    // Layer peta panas
    let heatLayer = L.heatLayer([], {
        radius: 30,
        blur: 25,
        maxZoom: 17,
        max: 1.0,
        gradient: {
            0.2: '#2563eb',
            0.4: '#06b6d4',
            0.6: '#84cc16',
            0.8: '#facc15',
            1.0: '#dc2626'
        }
    });

    // Kontrol untuk mengaktifkan dan menonaktifkan layer
   const kontrolLayer = L.control.layers(null, {
    'Marker': markerLayer,
    'Klaster': clusterLayer,
    'Peta Panas': heatLayer
}, {
    collapsed: false,
    position: 'topright'
}).addTo(map);
    async function muat() {
        try {
            const jenjang = document.getElementById('jenjang').value;
            const status = document.getElementById('status').value;
            const mode = document.getElementById('mode').value;

            const fitur = await Peta.ambilSekolah(jenjang, status);

            markerLayer.clearLayers();
            clusterLayer.clearLayers();

            const maks = Math.max(
                1,
                ...fitur.map(f => Number(f.properties.jumlah_siswa ?? 0))
            );

            const titikPanas = [];

            fitur.forEach(f => {
                const posisi = Peta.latlng(f);

                L.marker(posisi)
                    .bindPopup(Peta.popup(f))
                    .addTo(markerLayer);

                clusterLayer.addLayer(
                    L.marker(posisi).bindPopup(Peta.popup(f))
                );

                const siswa = Number(f.properties.jumlah_siswa ?? 0);
                const bobot = mode === 'siswa'
                    ? Math.max(siswa / maks, 0.1)
                    : 0.35;

                titikPanas.push([...posisi, bobot]);
            });

            heatLayer.setLatLngs(titikPanas);

            document.getElementById('jumlah').textContent =
                fitur.length + ' sekolah';

            if (fitur.length > 0) {
                map.fitBounds(
                    L.latLngBounds(fitur.map(Peta.latlng)).pad(0.1)
                );
            }
        } catch (e) {
            console.error(e);
            document.getElementById('jumlah').textContent =
                'Gagal memuat data';
        }
    }

    document.getElementById('jenjang')
        .addEventListener('change', muat);

    document.getElementById('status')
        .addEventListener('change', muat);

    document.getElementById('mode')
        .addEventListener('change', muat);

    muat();
</script>
@endpush
