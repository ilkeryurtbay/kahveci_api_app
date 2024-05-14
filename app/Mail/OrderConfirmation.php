<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $totalAmount;

    public function __construct($user, $totalAmount)
    {
        $this->user = $user;
        $this->totalAmount = $totalAmount;
    }

    public function build()
    {
        return $this->subject('Sipariş Onayı')
            ->view('emails.order_confirmation');
    }
}
