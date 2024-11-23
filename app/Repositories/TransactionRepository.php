<?php

namespace App\Repositories;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Room;
use App\Models\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getTransactionDataFormSession()
    {
        return session()->get('transaction');
    }

    public function saveTransactionDataToSession($data)
    {
        $transactionData = session()->get('transaction', []);

        foreach ($data as $key => $value) {
            $transactionData[$key] = $value;
        }
        session()->put('transaction', $transactionData);
    }

    public function saveTransaction($data)
    {
        //Mencari harga room
        $room = Room::find($data['room_id']);
        //
        $data = $this->prepareTransactionData($data, $room);

        $transaction = Transaction::create($data);

        session()->forget('transaction');

        return $transaction;
    }

    public function getTransactionByCode($transaction_code)
    {
        return Transaction::where('transaction_code', $transaction_code)->first();
    }

    private function prepareTransactionData($data, $room)
    {
        $data['transaction_code'] = $this->generateTransactionCode();
        $data['payment_status'] = 'pending';
        $data['transaction_date'] = now();

        $total = $this->calculateTotalAmount($room->price_per_month, $data['duration']);
        $data['total_amount'] = $this->calculatePaymentAmount($total, $data['payment_method']);

        return $data;
    }

    private function generateTransactionCode()
    {
        return 'NGKSDG' . rand(1000, 9999);
    }

    private function calculateTotalAmount($priceMonth, $duration)
    {
        $sub_total = $priceMonth * $duration;
        $tax = $sub_total * 0.11;
        $insurance = $sub_total * 0.01;
        return $sub_total + $tax + $insurance;
    }

    private function calculatePaymentAmount($total, $paymentMethod)
    {
        return $paymentMethod == 'full_payment' ? $total : $total * 0.3;
    }

    public function getTransactionByCodeEmailPhone($transaction_code, $email, $phone_number)
    {
        return Transaction::where('transaction_code', $transaction_code)->where('email', $email)->where('phone_number', $phone_number)->first();
    }
}
