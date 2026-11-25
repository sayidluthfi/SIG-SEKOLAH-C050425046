@extends('layouts.public')
@section('title', 'Cek Lokasi')

@section('content')
<div class="toolbar">
    <button type="button" id="lokasi-saya">
        Lokasi Saya
    </button>

    <span>
        Klik di mana saja pada peta untuk melihat alamat,
        sekolah terdekat, dan jangkauan radius 1 km.
    </span>
</div>

<div id="peta" class="peta"></div>

<div id="hasil" class="panel"
     style="top: 64px; right: 12px; width: 320px; max-height: 70%;
            overflow: auto; display: none;"></div>
@endsection

@push('scripts')
<script>
    const map = Peta.buat('peta');
    const lapisan = L.layerGroup().addTo(map);
    const panel = document.getElementById('hasil');

    let semua = [];
    let penandaLokasi = null;
    let penandaSekolah = L.layerGroup().addTo(map);
    let lingkaranJangkauan = null;
    let terakhir = 0;

    // Mengambil seluruh data sekolah
    Peta.ambilSekolah()
        .then((fitur) => {
            semua = fitur;
        })
        .catch((error) => {
            console.error('Gagal memuat sekolah:', error);
        });

    // Mencari sekolah terdekat dari suatu titik
    function terdekat(titik, n = 3) {
        return semua
            .map((f) => ({
                fitur: f,
                jarak: Peta.jarakKm(titik, Peta.latlng(f))
            }))
            .sort((a, b) => a.jarak - b.jarak)
            .slice(0, n);
    }

    // Mengambil alamat menggunakan Nominatim
    async function ambilAlamat(lat, lng) {
        const url = 'https://nominatim.openstreetmap.org/reverse'
            + '?format=jsonv2&addressdetails=1&accept-language=id'
            + `&lat=${lat}&lon=${lng}`;

        const respon = await fetch(url);

        if (!respon.ok) {
            throw new Error('HTTP ' + respon.status);
        }

        return respon.json();
    }

    // Menampilkan alamat, sekolah terdekat, dan jangkauan
    async function tampilkanLokasi(lat, lng) {
        const titik = [lat, lng];

        lapisan.clearLayers();
        penandaSekolah.clearLayers();

        // Penanda lokasi yang dipilih
        const penanda = L.circleMarker(titik, {
            radius: 9,
            color: '#fff',
            weight: 3,
            fillColor: '#ef4444',
            fillOpacity: 1
        }).addTo(lapisan);

        // Memperbarui penanda lokasi pengguna
        if (penandaLokasi) {
            penandaLokasi.setLatLng(titik);
            penandaLokasi.setStyle({
                fillColor: '#2563eb'
            });
        }

        // Menghapus lingkaran sebelumnya
        if (lingkaranJangkauan) {
            map.removeLayer(lingkaranJangkauan);
        }

        // Membuat lingkaran radius 1 km
        lingkaranJangkauan = L.circle(titik, {
            radius: 1000,
            color: '#16a34a',
            weight: 2,
            fillColor: '#22c55e',
            fillOpacity: 0.15
        }).addTo(map);

        // Menghitung sekolah di dalam radius 1 km
        const jumlahDalamRadius = semua.filter((f) => {
            const jarak = Peta.jarakKm(titik, Peta.latlng(f));
            return jarak <= 1;
        }).length;

        // Menghitung tiga sekolah terdekat
        const dekat = terdekat(titik);

        // Garis menuju sekolah terdekat
        dekat.forEach((d) => {
            const posisiSekolah = Peta.latlng(d.fitur);

            L.polyline([titik, posisiSekolah], {
                color: '#2563eb',
                weight: 2,
                dashArray: '6 6'
            }).addTo(lapisan);

            L.marker(posisiSekolah)
                .bindPopup(Peta.popup(d.fitur))
                .addTo(penandaSekolah);
        });

        panel.style.display = 'block';
        panel.textContent = 'Mencari alamat...';

        // Mengambil informasi alamat
        let bagianAlamat = '<em>Alamat tidak dapat dimuat.</em>';

        try {
            const data = await ambilAlamat(lat, lng);
            const a = data.address || {};

            const baris = [
                ['Jalan', a.road],
                ['Kelurahan/Desa',
                    a.suburb || a.neighbourhood || a.village],
                ['Kota/Kabupaten',
                    a.city || a.town || a.municipality || a.county],
                ['Provinsi', a.state],
                ['Kode pos', a.postcode]
            ].map(([k, v]) => `
                <tr>
                    <td style="padding: 2px 8px 2px 0; color: #6b7280;">
                        ${k}
                    </td>
                    <td>${Peta.esc(v) || '-'}</td>
                </tr>
            `).join('');

            bagianAlamat = `
                <p style="margin: 6px 0;">
                    ${Peta.esc(data.display_name || '-')}
                </p>
                <table style="font-size: 13px;">
                    ${baris}
                </table>
            `;
        } catch (err) {
            console.error('Gagal mengambil alamat:', err);
        }

        // Daftar sekolah terdekat
        const daftar = dekat.map((d) => `
            <li data-npsn="${Peta.esc(d.fitur.properties.npsn)}"
                style="padding: 6px 0; border-top: 1px solid #e5e7eb;
                       cursor: pointer;">
                <strong>
                    ${Peta.esc(d.fitur.properties.nama)}
                </strong>
                <br>
                <small>
                    ${d.jarak.toFixed(2)} km (garis lurus)
                </small>
            </li>
        `).join('');

        // Menampilkan seluruh hasil
        panel.innerHTML = `
            <strong>Koordinat</strong>

            <div style="font-family: monospace;">
                ${lat.toFixed(6)}, ${lng.toFixed(6)}
            </div>

            <strong style="display: block; margin-top: 8px;">
                Alamat
            </strong>

            ${bagianAlamat}

            <strong style="display: block; margin-top: 8px;">
                Analisis Jangkauan (Radius 1 km)
            </strong>

            <p style="margin: 5px 0;">
                Jumlah sekolah dalam radius 1 km:
                <strong>${jumlahDalamRadius} sekolah</strong>
            </p>

            <strong style="display: block; margin-top: 8px;">
                Tiga Sekolah Terdekat
            </strong>

            <ul style="list-style: none; margin: 4px 0 0; padding: 0;">
                ${daftar || '<li>Tidak ada data sekolah.</li>'}
            </ul>
        `;

        penanda.bindPopup('Lokasi yang dipilih').openPopup();
    }

    // Klik langsung pada peta
    map.on('click', (e) => {
        const sekarang = Date.now();

        // Membatasi permintaan Nominatim
        if (sekarang - terakhir < 1200) return;

        terakhir = sekarang;

        tampilkanLokasi(e.latlng.lat, e.latlng.lng);
    });

    // Tombol Lokasi Saya
    document.getElementById('lokasi-saya')
        .addEventListener('click', () => {

            if (!navigator.geolocation) {
                alert('Browser tidak mendukung fitur lokasi.');
                return;
            }

            if (semua.length === 0) {
                alert('Data sekolah masih dimuat. Coba beberapa saat lagi.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (posisi) => {
                    const lat = posisi.coords.latitude;
                    const lng = posisi.coords.longitude;

                    const titik = [lat, lng];

                    if (penandaLokasi) {
                        lapisan.removeLayer(penandaLokasi);
                    }

                    penandaLokasi = L.circleMarker(titik, {
                        radius: 10,
                        color: '#fff',
                        weight: 3,
                        fillColor: '#2563eb',
                        fillOpacity: 1
                    }).addTo(lapisan);

                    penandaLokasi.bindPopup('Lokasi Saya');

                    map.flyTo(titik, 15);

                    tampilkanLokasi(lat, lng);
                },
                (error) => {
                    let pesan = 'Gagal mendapatkan lokasi.';

                    if (error.code === 1) {
                        pesan = 'Izin lokasi ditolak. Aktifkan izin lokasi browser.';
                    } else if (error.code === 2) {
                        pesan = 'Lokasi tidak tersedia.';
                    } else if (error.code === 3) {
                        pesan = 'Waktu permintaan lokasi habis. Coba lagi.';
                    }

                    alert(pesan);
                    console.error(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });

    // Klik nama sekolah untuk menuju lokasi sekolah
    panel.addEventListener('click', (e) => {
        const li = e.target.closest('[data-npsn]');

        if (!li) return;

        const f = semua.find(
            (x) => String(x.properties.npsn) === li.dataset.npsn
        );

        if (f) {
            L.popup()
                .setLatLng(Peta.latlng(f))
                .setContent(Peta.popup(f))
                .openOn(map);

            map.flyTo(Peta.latlng(f), 17);
        }
    });
</script>
@endpush
