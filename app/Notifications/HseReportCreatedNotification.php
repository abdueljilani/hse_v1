<?php

namespace App\Notifications;

use App\Models\HseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HseReportCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public HseReport $report)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_number' => $this->report->report_number,
            'title' => $this->report->title,
            'created_by' => auth()->user()?->name,
        ];
    }
}
