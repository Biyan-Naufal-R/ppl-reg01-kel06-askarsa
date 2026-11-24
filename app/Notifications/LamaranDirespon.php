<?php

namespace App\Notifications;

use App\Models\Lamaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LamaranDirespon extends Notification
{
    use Queueable;

    public function __construct(public Lamaran $lamaran) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $label = Lamaran::labelStatus()[$this->lamaran->status] ?? $this->lamaran->status;
        $judul = $this->lamaran->lowongan?->judul ?? 'Lowongan';

        return [
            'lamaran_id' => $this->lamaran->id,
            'judul' => 'Status lamaran diperbarui',
            'pesan' => 'Lamaran Anda untuk "'.$judul.'" sekarang berstatus '.$label.'.',
            'status' => $this->lamaran->status,
            'url' => route('dashboard.applications'),
        ];
    }
}
