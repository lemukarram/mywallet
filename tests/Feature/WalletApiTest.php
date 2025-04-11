<?php
// tests/Feature/WalletApiTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->user->wallet()->create(['balance' => 1000]);
        
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('access_token');
    }

    public function test_authenticated_user_can_view_their_details()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'email' => 'test@example.com',
                    'wallet' => [
                        'balance' => 1000,
                    ],
                ],
            ]);
    }

    public function test_user_cannot_view_other_users_details()
    {
        $otherUser = User::factory()->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403);
    }

    public function test_deposit_funds()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/wallet/deposit', [
            'amount' => 200,
            'description' => 'Salary deposit',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Deposit successful',
                'wallet' => [
                    'balance' => 1200,
                ],
            ]);
    }

    // Add more authenticated tests...
}