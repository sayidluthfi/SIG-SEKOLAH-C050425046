<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SekolahController extends Controller
{
    public function index(Request $request)
    {
       $sekolahs = Sekolah::query()
    ->when($request->q, function ($query, $q) {
        $query->where(function ($w) use ($q) {
            $w->whereLike('nama', "%{$q}%")
              ->orWhereLike('npsn', "%{$q}%")
              ->orWhereLike('kecamatan', "%{$q}%");
        });
    })
    ->orderBy('nama')
    ->paginate(10)
    ->withQueryString();


        return view('admin.sekolah.index', compact('sekolahs'));
    }

    public function create()
    {
        return view('admin.sekolah.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        Sekolah::create($data);

        return redirect()->route('dinas.sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);

        return view('admin.sekolah.edit', compact('sekolah'));
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);

        $data = $request->validate($this->rules($sekolah->id));

        // Pembatasan khusus pengguna Sekolah.
        if ($request->user()->isSekolah()) {

            // NPSN tidak boleh diubah.
            unset($data['npsn']);

            // Hitung jarak dari koordinat awal ke koordinat baru.
            $jarak = $this->hitungJarak(
                $sekolah->latitude,
                $sekolah->longitude,
                $data['latitude'],
                $data['longitude']
            );

            // Jika lebih dari 500 meter, koordinat tetap menggunakan titik awal.
            if ($jarak > 500) {
                $data['latitude'] = $sekolah->latitude;
                $data['longitude'] = $sekolah->longitude;
            }
        }

        // Simpan perubahan data sekolah.
        $sekolah->update($data);

        return $this->kembali($request)
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();

        return redirect()->route('dinas.sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus.');
    }

    // Menghitung jarak koordinat menggunakan rumus Haversine.
    private function hitungJarak($lat1, $lon1, $lat2, $lon2): float
    {
        $radiusBumi = 6371000;

        $lat1 = deg2rad((float) $lat1);
        $lon1 = deg2rad((float) $lon1);
        $lat2 = deg2rad((float) $lat2);
        $lon2 = deg2rad((float) $lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radiusBumi * $c;
    }

    private function kembali(Request $request)
    {
        return $request->user()->isDinas()
            ? redirect()->route('dinas.sekolah.index')
            : redirect()->route('dashboard');
    }

    private function rules(?int $id = null): array
    {
        return [
            'npsn' => [
                'required',
                'digits:8',
                Rule::unique('sekolahs', 'npsn')->ignore($id),
            ],

            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'jenjang' => [
                'required',
                Rule::in(['SD', 'SMP', 'SMA', 'SMK']),
            ],

            'status' => [
                'required',
                Rule::in(['Negeri', 'Swasta']),
            ],

            'alamat' => [
                'required',
                'string',
            ],

            'kecamatan' => [
                'required',
                'string',
                'max:100',
            ],

            'telepon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        ];
    }
}
