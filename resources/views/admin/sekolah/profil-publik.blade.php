
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil {{ $sekolah->nama }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #1e3a8a;
            color: white;
            padding: 18px 30px;
        }

        .container {
            max-width: 900px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px #00000012;
        }

        .identitas {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
        }

        .detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .item {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
        }

        .item strong {
            display: block;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .deskripsi {
            margin-top: 25px;
            line-height: 1.7;
        }

        .tombol {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 18px;
            background: #1e40af;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        @media (max-width: 600px) {
            .detail {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    <h2>Profil Publik Sekolah</h2>
</header>

<div class="container">
    <div class="card">

        <div class="identitas">
            @if ($sekolah->profil?->logo)
                <img
                    src="{{ asset('storage/' . $sekolah->profil->logo) }}"
                    alt="Logo Sekolah"
                    class="logo"
                >
            @endif

            <h1>{{ $sekolah->nama }}</h1>
            <p>NPSN: {{ $sekolah->npsn }}</p>
        </div>

        <div class="detail">
            <div class="item">
                <strong>Jenjang</strong>
                {{ $sekolah->jenjang }}
            </div>

            <div class="item">
                <strong>Status</strong>
                {{ $sekolah->status }}
            </div>

            <div class="item">
                <strong>Alamat</strong>
                {{ $sekolah->alamat }}
            </div>

            <div class="item">
                <strong>Kecamatan</strong>
                {{ $sekolah->kecamatan }}
            </div>

            <div class="item">
                <strong>Telepon</strong>
                {{ $sekolah->telepon ?: '-' }}
            </div>

            <div class="item">
                <strong>Kepala Sekolah</strong>
                {{ $sekolah->profil?->kepala_sekolah ?: '-' }}
            </div>

            <div class="item">
                <strong>Akreditasi</strong>
                {{ $sekolah->profil?->akreditasi ?: '-' }}
            </div>

            <div class="item">
                <strong>Tahun Berdiri</strong>
                {{ $sekolah->profil?->tahun_berdiri ?: '-' }}
            </div>

            <div class="item">
                <strong>Jumlah Siswa</strong>
                {{ $sekolah->profil?->jumlah_siswa ?? '-' }}
            </div>

            <div class="item">
                <strong>Jumlah Guru</strong>
                {{ $sekolah->profil?->jumlah_guru ?? '-' }}
            </div>

            <div class="item">
                <strong>Email</strong>
                {{ $sekolah->profil?->email ?: '-' }}
            </div>

            <div class="item">
                <strong>Website</strong>
                @if ($sekolah->profil?->website)
                    <a href="{{ $sekolah->profil->website }}" target="_blank">
                        {{ $sekolah->profil->website }}
                    </a>
                @else
                    -
                @endif
            </div>
        </div>

        <div class="deskripsi">
            <h3>Tentang Sekolah</h3>
            <p>{{ $sekolah->profil?->deskripsi ?: 'Belum ada deskripsi sekolah.' }}</p>
        </div>

        <a href="{{ route('peta.index') }}" class="tombol">
            Kembali ke Peta
        </a>

    </div>
</div>

</body>
</html>
