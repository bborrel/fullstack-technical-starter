<?php

namespace Tests\Feature;

use App\DonationStatus;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_edition_via_model(): void
    {
        $donation = Donation::factory()->create();

        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseCount('donations', 1);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'user_id' => $donation->user_id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'status' => DonationStatus::PENDING->value,
        ]);

        $donation->update([
            'status' => DonationStatus::COMPLETED->value,
        ]);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => DonationStatus::COMPLETED->value,
        ]);
    }

    public function test_donation_edition_via_api(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // create a donation
        $payloadCreate = [
            'amount' => '100.00',
            'currency' => 'CAD',
        ];

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('donations', 0);

        $response = $this->actingAs($user)->postJson('/api/donations', $payloadCreate);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'message' => 'Donation created successfully',
                'donation' => [
                    'id' => 2,
                    'user_id' => $user->id,
                    'status' => DonationStatus::PENDING->value,
                ] + $payloadCreate,
            ]);

        $this->assertDatabaseCount('donations', 1);
        $this->assertDatabaseHas('donations', [
            'user_id' => $user->id,
            'status' => DonationStatus::PENDING->value,
        ] + $payloadCreate);

        // update a donation status
        $payloadUpdate = [
            'status' => DonationStatus::COMPLETED->value,
        ];

        $response = $this->actingAs($user)->patchJson("/api/donations/2", $payloadUpdate);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Donation updated successfully',
                'donation' => [
                    'id' => 2,
                    'user_id' => $user->id,
                    'status' => DonationStatus::COMPLETED->value,
                ] + $payloadCreate + $payloadUpdate
            ]);

        $this->assertDatabaseCount('donations', 1);
        $this->assertDatabaseHas('donations', [
            'user_id' => $user->id,
            'status' => DonationStatus::COMPLETED->value,
        ] + $payloadCreate + $payloadUpdate);
    }
}
