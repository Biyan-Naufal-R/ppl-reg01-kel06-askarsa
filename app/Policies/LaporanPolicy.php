<?php

namespace App\Policies;

use App\Models\Laporan;
use App\Models\User;

class LaporanPolicy
{
    /** Bukti laporan hanya boleh dibuka oleh pelapor yang mengirimnya. */
    public function unduh(User $user, Laporan $laporan): bool
    {
        return (int) $laporan->pelapor_id === (int) $user->id;
    }
}
