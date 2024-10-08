<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Markdown;
use Illuminate\Mail\Mailables\Enveloper;

class SendMessageToSubscriber extends Mailable
{
    use Queueable, SerializesModels;

    public $user = null;
    public $tenant=null;


    /**
     * Create a new message instance.
     */
    public function __construct($user,$tenant)
    {
        $this->user = $user;
        $this->tenant = $tenant;
 
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
        return $this->subject('Welcome to Jezdan')
                    ->markdown('emails.reply_to_subscriber');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
