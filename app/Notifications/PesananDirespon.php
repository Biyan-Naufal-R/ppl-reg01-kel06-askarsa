<?php

namespace App\Notifications;

use App\Models\Pesanan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PesananDirespon extends Notification
{
    use Queueable;

    public function __construct(
        public Pesanan $pesanan,
        public string $status,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $diterima = $this->status === Pesanan::STATUS_DIPROSES;

        return [
            'pesanan_id' => $this->pesanan->id,
            'judul' => $diterima ? 'Pesanan diterima' : 'Pesanan ditolak',
            'pesan' => $diterima
                ? 'Penyedia menerima pesanan Anda: '.$this->pesanan->judul.'.'
                : 'Penyedia menolak pesanan Anda: '.$this->pesanan->judul.'.',
            'status' => $this->status,
            'url' => route('pesanan.show', $this->pesanan),
        ];
    }
}
