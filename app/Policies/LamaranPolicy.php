<?php

namespace App\Policies;

use App\Models\Lamaran;
use App\Models\User;

class LamaranPolicy
{
    /** Detail dan CV hanya untuk pelamar sendiri atau pemberi kerja terkait. */
    public function view(User $user, Lamaran $lamaran): bool
    {
        return $lamaran->dimilikiOleh($user) || $lamaran->lowongannyaMilik($user);
    }

    public function downloadCv(User $user, Lamaran $lamaran): bool
    {
        return $this->view($user, $lamaran);
    }

    /** Hanya pemberi kerja pemilik lowongan yang boleh memproses lamaran. */
    public function proses(User $user, Lamaran $lamaran): bool
    {
        return $lamaran->lowongannyaMilik($user);
    }

    public function terima(User $user, Lamaran $lamaran): bool
    {
        return $lamaran->lowongannyaMilik($user);
    }

    public function tolak(User $user, Lamaran $lamaran): bool
    {
        return $lamaran->lowongannyaMilik($user);
    }
}
