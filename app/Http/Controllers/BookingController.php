<?php

namespace App\Http\Controllers;

use App\Interfaces\BoardingHouseRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerInformationStoreRequest;
use App\Http\Requests\BookingShowRequest;

class BookingController extends Controller
{

    private BoardingHouseRepositoryInterface $boardingHouseRepository;
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        BoardingHouseRepositoryInterface $boardingHouseRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->boardingHouseRepository = $boardingHouseRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function booking(Request $request, $slug)
    {
        $this->transactionRepository->saveTransactionDataToSession($request->all());

        return redirect()->route('booking.information', $slug);
    }

    public function information($slug)
    {
        $transaction = $this->transactionRepository->getTransactionDataFormSession($slug);
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        $room = $this->boardingHouseRepository->getBoardingHouseByRoomId($transaction['room_id']);

        return view('pages.booking.information', compact('boardingHouse', 'transaction', 'room'));
    }

    public function saveInformation(CustomerInformationStoreRequest $request, $slug)
    {
        $data = $request->validated();

        $this->transactionRepository->saveTransactionDataToSession($data);

        return redirect()->route('booking.checkout', $slug);
    }

    public function checkout($slug)
    {
        $transaction = $this->transactionRepository->getTransactionDataFormSession($slug);
        $boardingHouse = $this->boardingHouseRepository->getBoardingHouseBySlug($slug);
        $room = $this->boardingHouseRepository->getBoardingHouseByRoomId($transaction['room_id']);

        return view('pages.booking.checkout', compact('boardingHouse', 'transaction', 'room'));
    }

    public function payment(Request $request)
    {
        $this->transactionRepository->saveTransactionDataToSession($request->all());

        $transaction = $this->transactionRepository->saveTransaction($this->transactionRepository->getTransactionDataFormSession());

        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        if (empty($transaction->transaction_code) || empty($transaction->total_amount) || !is_numeric($transaction->total_amount)) {
            return back()->withErrors('Transaction details are incomplete or invalid.');
        }

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->transaction_code,
                'gross_amount' => $transaction->total_amount,
            ],
            'customer_details' => [
                'first_name' => $transaction->name,
                'email' => $transaction->email,
                'phone' => $transaction->phone_number,
            ]
        ];

        $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

        return redirect($paymentUrl);
    }

    public function success(Request $request)
    {
        $transaction = $this->transactionRepository->getTransactionByCode($request->order_id);

        if (!$transaction) {
            return redirect()->route('home');
        }

        return view('pages.booking.success', compact('transaction'));
    }

    public function checkBooking()
    {
        return view('pages.booking.check-booking');
    }

    public function show(BookingShowRequest $request)
    {
        $transaction = $this->transactionRepository->getTransactionByCodeEmailPhone($request->transaction_code, $request->email, $request->phone_number);

        if (!$transaction) {
            return redirect()->back()->with('error', 'Data Transaksi Tidak Ditemukan.');
        }

        return view('pages.booking.detail', compact('transaction'));
    }
}
