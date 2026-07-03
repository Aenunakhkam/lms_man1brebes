<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAnnouncementNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $announcement;

    /**
     * Create a new notification instance.
     */
    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Define target link based on user role
        $role = $notifiable->role ? $notifiable->role->name : 'siswa';
        $link = '/' . $role . '/announcements';

        return [
            'title' => 'Pengumuman Baru',
            'message' => $this->announcement->title,
            'icon' => 'mdi-bullhorn-outline',
            'link' => $link
        ];
    }
}
