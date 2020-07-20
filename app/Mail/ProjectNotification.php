<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class ProjectNotification extends Mailable
{
    use Queueable, SerializesModels;

    public static function formatPlural($count)
    {
        if ($count == 1) {
            return ' projekt';
        }

        if ($count >= 2 and $count <= 4) {
            return ' projekty';
        }

        if ($count >= 5) {
            return ' projektov';
        }
    }


    /**
     * Create a new message instance.
     *
     * @param Collection $projects
     */
    public function __construct(Collection $projects)
    {
        $this->projects = $projects;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->projects->count() === 1
            ? 'EIA: ' . $this->projects->first()->name
            : 'EIA: ' . $this->projects->count() . ' ' . self::formatPlural($this->projects->count());
        return $this->view('email.notification')->subject($subject)->with('projects', $this->projects);
    }
}
