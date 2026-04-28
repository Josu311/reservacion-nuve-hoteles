<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\SendDataToEnzo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendMonthlyReservationsDaily extends Command
{
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
        $currentMonthlyReservationsPaid = Reservation::query()
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->where('status', 'paid')
                                        ->count();

        $currentMonthlyReservationsPaidMount = Reservation::query()
                                            ->whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)
                                            ->where('status', 'paid')
                                            ->sum('amount_cents');

        $currentMonthlyReservationsPending = Reservation::query()
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->where('status', 'booking_in_reception')
                                        ->count();

        $currentMonthlyReservationsPendingMount = Reservation::query()
                                            ->whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)
                                            ->where('status', 'booking_in_reception')
                                            ->sum('amount_cents');

        $currentDate = now()->format('Y-m-d');

        $amountMx = number_format($currentMonthlyReservationsPaidMount / 100, 2, '.', '');

        $pendingAmountMx = number_format($currentMonthlyReservationsPendingMount / 100, 2, '.', '');

        $payload = [
            "api_key" => "enzo_master_key_zB3JnXRw2Z}ci}~.+,N>",
            "post_id" => 1350,
            "date" => $currentDate,
            "metric_count" => $currentMonthlyReservationsPaid,
            "metric_revenue" => $amountMx,
            "pending_reservation_count" => $currentMonthlyReservationsPending,
            "pending_reservation_revenue" => $pendingAmountMx,
            "source" => "Sistema Reservas Nuve Hotel"
        ];

        $response = Http::timeout(20)
                    ->retry(30)
                    ->post('https://control.enzomarketing.mx/wp-json/enzo-api/v1/update-client-metrics', $payload);

        $saveSendDataToEnzo = SendDataToEnzo::create([
            'reservations_count' => $currentMonthlyReservationsPaid,
            'reservations_mount' => $currentMonthlyReservationsPaidMount,
            'pending_reservations_count' => $currentMonthlyReservationsPending,
            'pending_reservations_mount' => $currentMonthlyReservationsPendingMount,
            'payload'            => json_encode($payload),
            'post_id'            => 1350
        ]);

        if(!$response->successful()) {
            $saveSendDataToEnzo->response = $response->json();
            $saveSendDataToEnzo->status = $response->status();
            $saveSendDataToEnzo->save();

            return self::FAILURE;
        }

        $saveSendDataToEnzo->response = $response->json();
        $saveSendDataToEnzo->status = $response->status();
        $saveSendDataToEnzo->save();

        return self::SUCCESS;

    }
}
