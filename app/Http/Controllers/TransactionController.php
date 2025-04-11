<?php
namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->middleware('auth:sanctum');
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $fromUser = Auth::user();
        $fromWallet = $fromUser->wallet;
        $toUser = User::findOrFail($request->input('to_user_id'));
        $toWallet = $toUser->wallet;
        $amount = $request->input('amount');
        $description = $request->input('description');

        $this->walletService->transfer($fromWallet, $toWallet, $amount, $description);

        return response()->json([
            'message' => 'Transfer successful',
            'from_wallet' => $fromWallet->fresh(),
            'to_wallet' => $toWallet->fresh(),
        ]);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $transactions = $user->transactions()
            ->with('relatedUser')
            ->latest()
            ->get();

        return response()->json([
            'transactions' => $transactions,
        ]);
    }
}