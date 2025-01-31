<?php 
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;  // Variabel untuk menyimpan OTP

    // Constructor untuk menerima OTP
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    // Menentukan bagaimana email akan dikirimkan
    public function build()
    {
        return $this->subject('Your OTP Code')
                    ->view('emails.otp');  // Nama view email yang akan digunakan
    }
}
