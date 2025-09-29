<?php

namespace Tests\Feature;

use App\DonationStatus;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_edited(): void
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create();

        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseCount('donations', 1);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'user_id' => $donation->user_id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'status' => DonationStatus::PENDING,
        ]);

        $donation->update([
            'status' => DonationStatus::COMPLETED,
        ]);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => DonationStatus::COMPLETED,
        ]);
    }
}
