<?php

namespace App\Policies;

use App\Models\Pesanan;
use App\Models\User;

class PesananPolicy
{
    /** Detail pesanan hanya untuk pelanggan atau penyedia yang terlibat. */
    public function view(User $user, Pesanan $pesanan): bool
    {
        return $pesanan->dimilikiOleh($user);
    }

    /**
     * Policy hanya menentukan siapa yang boleh melakukan aksi.
     * Aturan status (menunggu/diproses/selesai) divalidasi di controller
     * supaya pengguna mendapat pesan error yang jelas, bukan 403.
     */
    public function terima(User $user, Pesanan $pesanan): bool
    {
        return (int) $pesanan->penyedia_id === (int) $user->id;
    }

    public function tolak(User $user, Pesanan $pesanan): bool
    {
        return (int) $pesanan->penyedia_id === (int) $user->id;
    }

    public function batalkan(User $user, Pesanan $pesanan): bool
    {
        return (int) $pesanan->pelanggan_id === (int) $user->id;
    }

    public function selesaikan(User $user, Pesanan $pesanan): bool
    {
        return (int) $pesanan->penyedia_id === (int) $user->id;
    }
}
