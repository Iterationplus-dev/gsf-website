<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Acknowledgement extends Mailable implements ShouldQueue
{
    use Queueable,SerializesModels;

    public function __construct(public string $heading, public string $messageText, public ?string $actionUrl = null, public string $actionLabel = 'View details')
    {
        $this->afterCommit();
    }

    public function build(): static
    {
        return $this->subject($this->heading)->view('emails.acknowledgement');
    }
}
