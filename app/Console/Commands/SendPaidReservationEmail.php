<?php

namespace App\Console\Commands;

use App\Mail\ReservationConfirmedAdminMail;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPaidReservationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-paid-reservation-email
                            {reservation : Reservation ID or provider folio}
                            {to : Email recipient for the reservation details}
                            {--from= : Override the sender address, for example "Nuve Hotel <no-reply@nuvehotel.com>"}
                            {--subject= : Override the email subject}
                            {--force : Send even if the reservation status is not paid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an already paid reservation admin email to a specific recipient';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = trim((string) $this->argument('reservation'));
        $to = trim((string) $this->argument('to'));
        $configuredFromAddress = (string) config('mail.from.address');
        $configuredFromName = (string) config('mail.from.name');
        $from = (string) ($this->option('from')
            ?: trim(sprintf('%s <%s>', $configuredFromName ?: 'Nuve Hotel', $configuredFromAddress)));
        $resendApiKey = (string) config('services.resend.key');

        if ($identifier === '') {
            $this->error('Debes indicar un ID o folio de reservacion.');

            return self::FAILURE;
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('El destinatario no es un correo valido.');

            return self::FAILURE;
        }

        if ($resendApiKey === '') {
            $this->error('Falta configurar RESEND_API_KEY en el entorno.');

            return self::FAILURE;
        }

        if ($this->option('from') === null && $configuredFromAddress === 'hello@example.com') {
            $this->error('Falta configurar MAIL_FROM_ADDRESS en el entorno o pasar --from.');

            return self::FAILURE;
        }

        if (!preg_match('/<[^>]+>/', $from)) {
            $this->error('El remitente debe tener formato "Nombre <correo@dominio.com>".');

            return self::FAILURE;
        }

        $reservation = $this->findReservation($identifier);

        if (!$reservation) {
            $this->error("No se encontro una reservacion con identificador [{$identifier}].");

            return self::FAILURE;
        }

        if ($reservation->status !== 'paid' && !$this->option('force')) {
            $this->error("La reservacion #{$reservation->provider_folio} no esta en estado paid. Estado actual: {$reservation->status}.");
            $this->line('Si aun asi quieres enviarla, usa la opcion --force.');

            return self::FAILURE;
        }

        $subject = (string) ($this->option('subject')
            ?: 'Nueva reservacion pagada #' . $reservation->provider_folio);

        $this->line("Reservacion: #{$reservation->provider_folio} (ID {$reservation->id})");
        $this->line("Estado: {$reservation->status}");
        $this->line("Huesped: {$reservation->guest_name} <{$reservation->guest_email}>");
        $this->line("Enviando a: {$to}");

        try {
            $mailable = new ReservationConfirmedAdminMail($reservation);

            if ($this->option('subject')) {
                $mailable->subject($subject);
            }

            Mail::mailer('resend')
                ->to($to)
                ->send($mailable);
        } catch (\Throwable $e) {
            $this->error('Resend devolvio un error al enviar la reservacion.');
            $this->newLine();
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $meta = $reservation->meta ?? [];
        $manualResends = $meta['manual_email_resends'] ?? [];
        $manualResends[] = [
            'to' => $to,
            'sent_at' => now()->toISOString(),
            'subject' => $subject,
        ];

        $reservation->meta = array_merge($meta, [
            'manual_email_resends' => $manualResends,
        ]);
        $reservation->save();

        $this->info('Correo enviado correctamente.');

        return self::SUCCESS;
    }

    private function findReservation(string $identifier): ?Reservation
    {
        $query = Reservation::query()->with('user');

        if (ctype_digit($identifier)) {
            $reservation = (clone $query)->find((int) $identifier);

            if ($reservation) {
                return $reservation;
            }
        }

        return (clone $query)
            ->where('provider_folio', $identifier)
            ->first();
    }
}
