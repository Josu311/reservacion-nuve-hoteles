<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Resend\Laravel\Facades\Resend;

class TestResendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-resend-email
                            {to : Email recipient for the test message}
                            {--from= : Override the sender address, for example "Nuve Hotel <no-reply@nuvehotel.com>"}
                            {--subject=Prueba de correo con Resend : Subject for the test message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email through Resend';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $to = (string) $this->argument('to');
        $subject = (string) $this->option('subject');
        $configuredFromAddress = (string) config('mail.from.address');
        $configuredFromName = (string) config('mail.from.name');
        $from = (string) ($this->option('from')
            ?: trim(sprintf('%s <%s>', $configuredFromName ?: 'Nuve Hotel', $configuredFromAddress)));
        $resendApiKey = (string) config('services.resend.key');
        $mailer = (string) config('mail.default');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('El destinatario no es un correo valido.');

            return self::FAILURE;
        }

        if ($resendApiKey === '') {
            $this->error('Falta configurar RESEND_API_KEY en el entorno.');

            return self::FAILURE;
        }

        if ($this->option('from') === null && $configuredFromAddress === 'hello@example.com') {
            $this->error('Falta configurar MAIL_FROM_ADDRESS en el entorno o pasar --from para esta prueba.');

            return self::FAILURE;
        }

        if (!preg_match('/<[^>]+>/', $from)) {
            $this->error('El remitente debe tener formato "Nombre <correo@dominio.com>".');

            return self::FAILURE;
        }

        $this->line("Mailer por defecto: {$mailer}");
        $this->line("Enviando prueba a: {$to}");
        $this->line("Remitente: {$from}");

        try {
            $response = Resend::emails()->send([
                'from' => $from,
                'to' => [$to],
                'subject' => $subject,
                'html' => $this->buildHtml(),
            ]);
        } catch (\Throwable $e) {
            $this->error('Resend devolvio un error al enviar la prueba.');
            $this->newLine();
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $emailId = data_get($response, 'id', 'sin id');

        $this->info("Correo enviado correctamente. Resend ID: {$emailId}");

        return self::SUCCESS;
    }

    private function buildHtml(): string
    {
        $sentAt = now()->format('Y-m-d H:i:s');

        return <<<HTML
<div style="font-family:Arial,sans-serif;line-height:1.6;color:#111827">
    <h1 style="margin-bottom:16px;">Prueba de correo con Resend</h1>
    <p>Este mensaje confirma que la aplicacion pudo conectarse con Resend.</p>
    <p><strong>Fecha:</strong> {$sentAt}</p>
    <p><strong>Proyecto:</strong> reservacion-nuve-hoteles</p>
</div>
HTML;
    }
}
