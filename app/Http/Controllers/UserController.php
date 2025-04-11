<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->middleware('auth:sanctum')->except(['store']);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(10)), // Random password for API-created users
        ]);

        $initialBalance = $request->input('initial_balance', 0);
        $this->walletService->createWallet($user, $initialBalance);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('wallet'),
        ], 201);
    }

    public function showCurrentUser(): JsonResponse
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user->load('wallet'),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return response()->json([
            'user' => $user->load('wallet'),
        ]);
    }
}