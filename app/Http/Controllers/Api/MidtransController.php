<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashedKey !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;
        $transaction = Transaction::where('transaction_code', $orderId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        //setingan untuk pesan whatsapp dengan twilio
        $sid    = env('TWILIO_ACCOUNT_SID');
        $token  = env('TWILIO_ACCOUNT_TOKEN');
        $twilio = new Client($sid, $token);

        $methode = $transaction->payment_method;

        if ($methode == 'full_payment') {
            $methodePembayaran = 'Full Payment 100%';
        } else if ($methode == 'down_payment') {
            $methodePembayaran = 'Down Payment 30%';
        }

        $messages =
            "Halo, " . "*" . $transaction->name . "*" . "." . PHP_EOL  . PHP_EOL .
            "Kami telah menerima pembayaran anda pada kode pemesanan : " . "*" . $transaction->transaction_code . "*" . PHP_EOL .
            "-------------------------------------------------" . PHP_EOL .
            "Total Pembayaran : Rp. " . number_format($transaction->total_amount, 0, ',', '.') . PHP_EOL .
            "Tempat : " . "*" . $transaction->boardingHouse->category->name . " " . $transaction->boardingHouse->name . "*" . PHP_EOL .
            "Alamat : " . $transaction->boardingHouse->address . PHP_EOL .
            "Kamar : " . $transaction->room->name . PHP_EOL .
            "Durasi : " . $transaction->duration . " Bulan" . PHP_EOL .
            "Tanggal Check In : " . date('d-m-Y', strtotime($transaction->start_date)) . PHP_EOL .
            "Pembayaran : " . $methodePembayaran . PHP_EOL .
            "-------------------------------------------------" . PHP_EOL .
            "Terima kasih telah menggunakan layanan kami.";

        switch ($transactionStatus) {
            case 'capture':
                if ($request->payment_type == 'credit_card') {
                    if ($request->fraud_status == 'challenge') {
                        $transaction->update(['payment_status' => 'pending']);
                    } else {
                        $transaction->update(['payment_status' => 'success']);
                    }
                }
                break;

                //Setingan dengan twillo
                // case 'settlement':
                //     $transaction->update(['payment_status' => 'success']);

                //     $twilio->messages
                //         ->create(
                //             "whatsapp:+" . $transaction->phone_number, // to
                //             array(
                //                 "from" => "whatsapp:+14155238886",
                //                 "body" => $messages
                //             )
                //         );

                //     break;

                //Setingan dengan fonnte
            case 'settlement':
                $transaction->update(['payment_status' => 'success']);

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.fonnte.com/send',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array(
                        'target' => $transaction->phone_number,
                        'message' => $messages,
                        'schedule' => 0,
                        'typing' => false,
                        'delay' => '2',
                        'countryCode' => '62',
                        'followup' => 0,
                    ),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: gk5K6VtsJNx3LUDBXhmU'
                    ),
                ));

                $response = curl_exec($curl);
                if (curl_errno($curl)) {
                    $error_msg = curl_error($curl);
                }
                curl_close($curl);

                if (isset($error_msg)) {
                    echo $error_msg;
                }
                echo $response;

                break;

            case 'pending':
                $transaction->update(['payment_status' => 'pending']);
                break;

            case 'deny':
                $transaction->update(['payment_status' => 'failed']);
                break;

            case 'expire':
                $transaction->update(['payment_status' => 'expired']);
                break;

            case 'cancel':
                $transaction->update(['payment_status' => 'canceled']);
                break;

            default:
                $transaction->update(['payment_status' => 'unknown']);
                break;
        }

        return response()->json(['message' => 'Callback received successfully'], 200);
    }
}
