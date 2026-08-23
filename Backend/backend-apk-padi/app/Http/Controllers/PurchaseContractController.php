<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseContractResource;
use App\Models\PurchaseContract;
use Illuminate\Http\Request;

class PurchaseContractController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = PurchaseContract::query()
            ->with([
                'listing',
                'farmer',
                'partner',
                'offer',
            ]);

        if ($user->role === 'farmer') {
            $query->where(
                'farmer_id',
                $user->id
            );
        } else {
            $query->where(
                'partner_id',
                $user->id
            );
        }

        return PurchaseContractResource::collection(
            $query->latest()->get()
        );
    }

    public function show(
        Request $request,
        PurchaseContract $purchaseContract
    ) {
        $user = $request->user();

        if (
            $purchaseContract->farmer_id !== $user->id &&
            $purchaseContract->partner_id !== $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kontrak ini.',
            ], 403);
        }

        $purchaseContract->load([
            'listing',
            'farmer',
            'partner',
            'offer',
        ]);

        return response()->json([
            'success' => true,
            'data' => new PurchaseContractResource(
                $purchaseContract
            ),
        ]);
    }
}
