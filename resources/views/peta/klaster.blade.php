
@extends('layouts.public')
@section('title', 'Klaster Sekolah')

@push('styles')
<link rel="stylesheet"
    href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">

<link rel="stylesheet"
    href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
@endpush

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

    <span style="color:#6b7280;">
        Klik angka klaster untuk memperbesar peta.
    </span>
</div>

<div id="peta" class="peta"></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = Peta.buat('peta');

    const klaster = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 60,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 17,
        chunkedLoading: true
    });

    map.addLayer(klaster);

    async function muat() {
        const jumlah = document.getElementById('jumlah');

        try {
            jumlah.textContent = 'Memuat data...';

            const parameter = new URLSearchParams({
                jenjang: document.getElementById('filter-jenjang').value,
                status: document.getElementById('filter-status').value
            });

            const response = await fetch(
                `${window.PETA_URL.data}?${parameter}`
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            klaster.clearLayers();

            const fitur = (data.features || []).filter(function (f) {
                return f.geometry?.type === 'Point';
            });

            const markers = fitur.map(function (f) {
                return L.marker(Peta.latlng(f))
                    .bindPopup(Peta.popup(f));
            });

            klaster.addLayers(markers);

            jumlah.textContent = markers.length + ' sekolah';

            if (markers.length > 0) {
                map.fitBounds(klaster.getBounds().pad(0.1));
            }

        } catch (error) {
            console.error('Klaster:', error);
            jumlah.textContent = 'Gagal memuat data sekolah';
        }
    }

    document.getElementById('filter-jenjang')
        .addEventListener('change', muat);

    document.getElementById('filter-status')
        .addEventListener('change', muat);

    muat();
});
</script>
@endpush
