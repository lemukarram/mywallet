<?php
// app/Services/WalletService.php
namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    public function createWallet(User $user, float $initialBalance = 0): Wallet
    {
        return DB::transaction(function () use ($user, $initialBalance) {
            $wallet = $user->wallet()->create(['balance' => $initialBalance]);

            if ($initialBalance > 0) {
                $this->recordTransaction(
                    $user,
                    $wallet,
                    'deposit',
                    $initialBalance,
                    'Initial deposit'
                );
            }

            return $wallet;
        });
    }

    public function deposit(Wallet $wallet, float $amount, string $description = null): Wallet
    {
        return DB::transaction(function () use ($wallet, $amount, $description) {
            $wallet->increment('balance', $amount);

            $this->recordTransaction(
                $wallet->user,
                $wallet,
                'deposit',
                $amount,
                $description ?? 'Deposit to wallet'
            );

            return $wallet->fresh();
        });
    }

    public function withdraw(Wallet $wallet, float $amount, string $description = null): Wallet
    {
        return DB::transaction(function () use ($wallet, $amount, $description) {
            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient funds');
            }

            $wallet->decrement('balance', $amount);

            $this->recordTransaction(
                $wallet->user,
                $wallet,
                'withdrawal',
                $amount,
                $description ?? 'Withdrawal from wallet'
            );

            return $wallet->fresh();
        });
    }

    public function transfer(Wallet $fromWallet, Wallet $toWallet, float $amount, string $description = null): void
    {
        DB::transaction(function () use ($fromWallet, $toWallet, $amount, $description) {
            if ($fromWallet->balance < $amount) {
                throw new \Exception('Insufficient funds for transfer');
            }

            $fromWallet->decrement('balance', $amount);
            $toWallet->increment('balance', $amount);

            // Generate unique references for each transaction
            $senderReference = Str::uuid()->toString();
            $receiverReference = Str::uuid()->toString();

            // Record transaction for sender
            $this->recordTransaction(
                $fromWallet->user,
                $fromWallet,
                'transfer',
                $amount,
                $description ?? sprintf('Transfer to user %s', $toWallet->user->id),
                $toWallet->user_id,
                $senderReference
            );

            // Record transaction for recipient
            $this->recordTransaction(
                $toWallet->user,
                $toWallet,
                'transfer',
                $amount,
                $description ?? sprintf('Transfer from user %s', $fromWallet->user->id),
                $fromWallet->user_id,
                $receiverReference
            );
        });
    }

    protected function recordTransaction(
        User $user,
        Wallet $wallet,
        string $type,
        float $amount,
        ?string $description = null,
        ?int $relatedUserId = null,
        ?string $reference = null
    ): Transaction {
        return Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => $type,
            'amount' => $amount,
            'reference' => $reference ?? Str::uuid()->toString(),
            'description' => $description,
            'related_user_id' => $relatedUserId,
        ]);
    }
}