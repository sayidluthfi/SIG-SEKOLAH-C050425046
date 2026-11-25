<?php

namespace App\Policies;

use App\Models\Sekolah;
use App\Models\User;

class SekolahPolicy
{
    public function update(User $user, Sekolah $sekolah): bool
    {
        // Admin dapat mengubah semua data sekolah
        if ($user->isAdmin()) {
            return true;
        }

        // Dinas dapat mengubah semua data sekolah
        if ($user->isDinas()) {
            return true;
        }

        // Operator hanya dapat mengubah sekolah miliknya
        return $user->isSekolah()
            && $user->sekolah_id !== null
            && (int) $user->sekolah_id === $sekolah->id;
    }
}
