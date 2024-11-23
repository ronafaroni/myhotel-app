<?php

namespace App\Interfaces;

interface TransactionRepositoryInterface
{
    public function getTransactionDataFormSession();

    public function saveTransactionDataToSession($data);

    public function saveTransaction($data);

    public function getTransactionByCode($transaction_code);

    public function getTransactionByCodeEmailPhone($transaction_code, $email, $phone_number);
}
