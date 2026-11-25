
@extends('layouts.public')
@section('title', 'Peta Panas')

@section('content')
<div class="toolbar">
    <label>Bobot
        <select id="mode">
            <option value="sekolah">Jumlah sekolah</option>
            <option value="siswa">Jumlah siswa</option>
        </select>
    </label>

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

    <label>Radius
        <input type="range" id="radius" min="10" max="60" value="30">
    </label>

    <span id="jumlah">Memuat data...</span>
</div>

<div id="peta" class="peta"></div>

<div class="panel" style="left:12px;bottom:24px;width:180px;">
    <div style="height:10px;border-radius:5px;background:linear-gradient(to right,#2563eb,#06b6d4,#84cc16,#facc15,#dc2626);"></div>
    <div style="display:flex;justify-content:space-between;font-size:12px;margin-top:4px;">
        <span>Rendah</span>
        <span>Tinggi</span>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = Peta.buat('peta');

    let heat = null;
    let dataSekolah = [];

    function gambar() {
        const mode = document.getElementById('mode').value;
        const radius = Number(document.getElementById('radius').value);

        const siswaMaks = Math.max(
            1,
            ...dataSekolah.map(f =>
                Number(f.properties.jumlah_siswa) || 0
            )
        );

        const titik = dataSekolah.map(function (f) {
            const siswa = Number(f.properties.jumlah_siswa) || 0;

            const bobot = mode === 'siswa'
                ? Math.max(siswa / siswaMaks, 0.1)
                : 0.35;

            return [...Peta.latlng(f), bobot];
        });

        if (heat) {
            map.removeLayer(heat);
        }

        heat = L.heatLayer(titik, {
            radius: radius,
            blur: 25,
            maxZoom: 17,
            max: 1,
            gradient: {
                0.2: '#2563eb',
                0.4: '#06b6d4',
                0.6: '#84cc16',
                0.8: '#facc15',
                1.0: '#dc2626'
            }
        }).addTo(map);

        document.getElementById('jumlah').textContent =
            dataSekolah.length + ' sekolah';
    }

    async function muat() {
        const jumlah = document.getElementById('jumlah');

        try {
            jumlah.textContent = 'Memuat data...';

            const parameter = new URLSearchParams({
                jenjang: document.getElementById('jenjang').value,
                status: document.getElementById('status').value
            });

            const response = await fetch(
                `${window.PETA_URL.data}?${parameter}`
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const hasil = await response.json();

            dataSekolah = (hasil.features || []).filter(function (f) {
                return f.geometry?.type === 'Point';
            });

            gambar();

            if (dataSekolah.length > 0) {
                map.fitBounds(
                    L.latLngBounds(dataSekolah.map(Peta.latlng)).pad(0.1)
                );
            }

        } catch (error) {
            console.error('Heatmap:', error);
            jumlah.textContent = 'Gagal memuat data sekolah';
        }
    }

    document.getElementById('jenjang')
        .addEventListener('change', muat);

    document.getElementById('status')
        .addEventListener('change', muat);

    document.getElementById('mode')
        .addEventListener('change', gambar);

    document.getElementById('radius')
        .addEventListener('input', gambar);

    muat();
});
</script>
@endpush
