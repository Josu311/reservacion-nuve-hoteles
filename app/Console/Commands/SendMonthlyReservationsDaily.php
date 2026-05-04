<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\SendDataToEnzo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendMonthlyReservationsDaily extends Command
{
    private const HOTEL_CODES = ['torreon', 'gomez'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-monthly-reservations-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = now()->format('Y-m-d');
        $enzoApiKey = config('services.enzo.api_key');
        $enzoMetricsUrl = config('services.enzo.metrics_url');
        $enzoPostIds = config('services.enzo.post_ids', []);
        $hasFailures = false;

        foreach (self::HOTEL_CODES as $hotelCode) {
            $postId = $enzoPostIds[$hotelCode] ?? null;

            if (!$postId) {
                $this->error("No hay post_id configurado para {$hotelCode}.");
                $hasFailures = true;

                continue;
            }

            $baseQuery = Reservation::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('hotel_code', $hotelCode);

            $currentMonthlyReservationsPaid = (clone $baseQuery)
                ->where('status', 'paid')
                ->count();

            $currentMonthlyReservationsPaidMount = (clone $baseQuery)
                ->where('status', 'paid')
                ->sum('amount_cents');

            $currentMonthlyReservationsPending = (clone $baseQuery)
                ->where('status', 'booking_in_reception')
                ->count();

            $currentMonthlyReservationsPendingMount = (clone $baseQuery)
                ->where('status', 'booking_in_reception')
                ->sum('amount_cents');

            $amountMx = number_format($currentMonthlyReservationsPaidMount / 100, 2, '.', '');
            $pendingAmountMx = number_format($currentMonthlyReservationsPendingMount / 100, 2, '.', '');

            $payload = [
                'api_key' => $enzoApiKey,
                'post_id' => $postId,
                'date' => $currentDate,
                'metric_count' => $currentMonthlyReservationsPaid,
                'metric_revenue' => $amountMx,
                'pending_reservation_count' => $currentMonthlyReservationsPending,
                'pending_reservation_revenue' => $pendingAmountMx,
                'source' => 'Sistema Reservas Nuve Hotel',
            ];

            $response = Http::timeout(20)
                ->retry(30)
                ->post($enzoMetricsUrl, $payload);

            $saveSendDataToEnzo = SendDataToEnzo::create([
                'reservations_count' => $currentMonthlyReservationsPaid,
                'reservations_mount' => $currentMonthlyReservationsPaidMount,
                'pending_reservations_count' => $currentMonthlyReservationsPending,
                'pending_reservations_mount' => $currentMonthlyReservationsPendingMount,
                'payload' => json_encode($payload),
                'post_id' => $postId,
            ]);

            $saveSendDataToEnzo->response = $response->json();
            $saveSendDataToEnzo->status = $response->status();
            $saveSendDataToEnzo->save();

            if (!$response->successful()) {
                $this->error("Error al enviar métricas de {$hotelCode} a Enzo.");
                $hasFailures = true;
            }
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }
}
