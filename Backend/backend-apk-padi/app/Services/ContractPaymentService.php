<?php

namespace App\Services;

use App\Models\ContractPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ContractPaymentService
{
    public function getPayments(?User $user = null): Collection
    {
        $query = ContractPayment::with('contract');

        if (! $user) {
            return $query->get();
        }

        $isAdmin = ($user->role === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin')));

        if ($isAdmin) {
            return $query->get();
        }

        return $query->whereHas('contract', function ($q) use ($user): void {
            $q->where('farmer_id', $user->id)
                ->orWhere('partner_id', $user->id);
        })->get();
    }
}