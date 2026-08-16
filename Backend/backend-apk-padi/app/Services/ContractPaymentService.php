<?php

namespace App\Services;

use App\Models\ContractPayment;
use Illuminate\Database\Eloquent\Collection;

class ContractPaymentService
{
    public function getPayments(): Collection
    {
        return ContractPayment::with('contract')->get();
    }
}