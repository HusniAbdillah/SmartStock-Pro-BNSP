<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $filename,
        private readonly int    $fileSizeKb,
        private readonly bool   $failed = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->failed) {
            return [
                'title'    => 'Pembuatan Laporan Gagal',
                'message'  => "File \"{$this->filename}\" gagal dibuat. Silakan coba lagi atau hubungi administrator.",
                'severity' => 'critical',
                'filename' => $this->filename,
                'type'     => 'report_failed',
            ];
        }

        return [
            'title'    => 'Laporan Inventaris Siap Diunduh',
            'message'  => "File \"{$this->filename}\" ({$this->fileSizeKb} KB) telah selesai dibuat dan siap diunduh.",
            'severity' => 'info',
            'filename' => $this->filename,
            'type'     => 'report_ready',
        ];
    }
}
