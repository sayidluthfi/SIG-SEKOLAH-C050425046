<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use App\Support\Geometri;

class PetaController extends Controller
{
   public function index()
    {
    return view('peta.index');
    }

    public function cari()
    {   
    return view('peta.cari');
    }

    public function klaster()
    {
        return view('peta.klaster');
    }

    public function panas()
    {
        return view('peta.panas');
    }


public function wilayah()
{
    return view('peta.wilayah');
}

public function rute()
{
    return view('peta.rute');
}

public function alamat()
{
    return view('peta.alamat');
}

public function gabungan()
{
    return view('peta.gabungan');
} 

public function geojsonWilayah(Request $request)
{
    $features = Sekolah::with('profil')
        ->when($request->jenjang, fn ($q, $j) => $q->where('jenjang', $j))
        ->get()
        ->groupBy('kecamatan')
        ->map(function ($kelompok, $kecamatan) {
            $titik = $kelompok
                ->map(fn ($s) => [$s->longitude, $s->latitude])
                ->all();

            $hull = Geometri::convexHull($titik);

            // Poligon memerlukan minimal 3 titik yang tidak segaris
            if (count($hull) < 3) {
                return null;
            }

            $hull[] = $hull[0];

            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [$hull],
                ],
                'properties' => [
                    'kecamatan' => $kecamatan,
                    'jumlah_sekolah' => $kelompok->count(),
                    'jumlah_siswa' => (int) $kelompok->sum(
                        fn ($s) => $s->profil?->jumlah_siswa ?? 0
                    ),
                ],
            ];
        })
        ->filter()
        ->values();

    return response()->json([
        'type' => 'FeatureCollection',
        'features' => $features,
    ]);
}


   
public function geojson(Request $request)
{
    $query = Sekolah::with('profil')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->when($request->jenjang, function ($q, $jenjang) {
            $q->where('jenjang', $jenjang);
        })
        ->when($request->status, function ($q, $status) {
            $q->where('status', $status);
        });

    // Membatasi data berdasarkan bounding box peta.
    if ($request->filled('bbox')) {
        $koordinat = explode(',', $request->bbox);

        if (count($koordinat) !== 4) {
            return response()->json([
                'message' => 'Format bounding box tidak valid.'
            ], 422);
        }

        [$barat, $selatan, $timur, $utara] = $koordinat;

        foreach ($koordinat as $nilai) {
            if (!is_numeric($nilai)) {
                return response()->json([
                    'message' => 'Koordinat bounding box harus berupa angka.'
                ], 422);
            }
        }

        $barat = (float) $barat;
        $selatan = (float) $selatan;
        $timur = (float) $timur;
        $utara = (float) $utara;

        if (
            $barat < -180 || $barat > 180 ||
            $timur < -180 || $timur > 180 ||
            $selatan < -90 || $selatan > 90 ||
            $utara < -90 || $utara > 90 ||
            $selatan > $utara ||
            $barat > $timur
        ) {
            return response()->json([
                'message' => 'Batas koordinat bounding box tidak valid.'
            ], 422);
        }

        $query->whereBetween('longitude', [$barat, $timur])
              ->whereBetween('latitude', [$selatan, $utara]);
    }

    $features = $query->get()->map(fn ($s) => [
        'type' => 'Feature',
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [
                (float) $s->longitude,
                (float) $s->latitude
            ],
        ],
        'properties' => [
            'npsn' => $s->npsn,
            'nama' => $s->nama,
            'jenjang' => $s->jenjang,
            'status' => $s->status,
            'alamat' => $s->alamat,
            'kecamatan' => $s->kecamatan,
            'telepon' => $s->telepon,
            'kepala_sekolah' => $s->profil?->kepala_sekolah,
            'akreditasi' => $s->profil?->akreditasi,
            'jumlah_siswa' => $s->profil?->jumlah_siswa,
            'jumlah_guru' => $s->profil?->jumlah_guru,
        ],
    ]);

    return response()->json([
        'type' => 'FeatureCollection',
        'features' => $features,
    ]);
}
}