<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_user_can_create_a_transaction(): void
    {
        $user = User::factory()->create();

        Transaction::create([
            'user_id' => $user->id,
            'amount' => 500.00,
            'type' => 'income',
            'transaction_date' => now(),
            'description' => 'Bonus',
            'category' => 'Work', 
        ]);

        $this->assertDatabaseHas('transactions', [
            'amount' => 500.00,
            'description' => 'Bonus',
        ]);
    }

    #[Test]
    public function balance_calculation_is_correct(): void
    {
        $user = User::factory()->create();

        Transaction::create([
            'user_id' => $user->id, 
            'amount' => 1000, 
            'type' => 'income', 
            'description' => 'Salary', 
            'category' => 'Job', 
            'transaction_date' => now()
        ]);

        Transaction::create([
            'user_id' => $user->id, 
            'amount' => -400, 
            'type' => 'expense', 
            'description' => 'Rent', 
            'category' => 'Home', 
            'transaction_date' => now()
        ]);

        $this->assertEquals(600, Transaction::sum('amount'));
    }
}