<?php
namespace App\Http\Controllers;

use App\Http\Requests\WalletOperationRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->middleware('auth:sanctum');
    }

    public function deposit(WalletOperationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $amount = $request->input('amount');
        $description = $request->input('description');

        $this->walletService->deposit($wallet, $amount, $description);

        return response()->json([
            'message' => 'Deposit successful',
            'wallet' => $wallet->fresh(),
        ]);
    }

    public function withdraw(WalletOperationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $amount = $request->input('amount');
        $description = $request->input('description');

        $this->walletService->withdraw($wallet, $amount, $description);

        return response()->json([
            'message' => 'Withdrawal successful',
            'wallet' => $wallet->fresh(),
        ]);
    }
}