<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        $totalMx = number_format($this->reservation->amount_cents / 100, 2);

        return $this->subject('Tu reserva #' . $this->reservation->provider_folio . ' está confirmada')
            ->markdown('emails.reservations.confirmed', [
                'reservation' => $this->reservation,
                'totalMx'     => $totalMx,
            ]);
    }
}
