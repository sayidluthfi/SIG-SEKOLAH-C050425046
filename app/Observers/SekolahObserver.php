<?php

namespace App\Observers;

use App\Models\Sekolah;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SekolahObserver
{
    public function updated(Sekolah $sekolah): void
    {
        $perubahan = $sekolah->getChanges();

        unset($perubahan['updated_at']);

        if (empty($perubahan)) {
            return;
        }

        $dataLama = [];

        foreach ($perubahan as $kolom => $nilaiBaru) {
            $dataLama[$kolom] = $sekolah->getRawOriginal($kolom);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'sekolah_id' => $sekolah->id,
            'action' => 'Mengubah data sekolah',
            'changes' => [
                'data_lama' => $dataLama,
                'data_baru' => $perubahan,
            ],
        ]);
    }
}
