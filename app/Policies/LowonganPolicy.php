<?php

namespace App\Policies;

use App\Models\Lowongan;
use App\Models\User;

class LowonganPolicy
{
    /** Hanya pemberi kerja pemilik lowongan yang boleh mengubahnya. */
    public function update(User $user, Lowongan $lowongan): bool
    {
        return (int) $lowongan->user_id === (int) $user->id;
    }

    public function toggleStatus(User $user, Lowongan $lowongan): bool
    {
        return (int) $lowongan->user_id === (int) $user->id;
    }

    public function tutup(User $user, Lowongan $lowongan): bool
    {
        return (int) $lowongan->user_id === (int) $user->id;
    }
}
