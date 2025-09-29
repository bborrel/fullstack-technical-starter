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
        // ARRANGE 1
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

        // ACT 1
        $response = $this->actingAs($user)->postJson('/api/donations', $payloadCreate);

        // ASSERT 1
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

        // ARRANGE 2
        // update a donation status
        $payloadUpdate = [
            'status' => DonationStatus::COMPLETED->value,
        ];

        // ACT 2
        $response = $this->actingAs($user)->patchJson("/api/donations/2", $payloadUpdate);

        // ASSERT 2
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

    public function test_donation_history_by_user(): void
    {
        // ARRANGE
        /** @var User $user */
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $otherUser = User::factory()->create();

        // Completed donations for $user
        $completedDonations = Donation::factory()
            ->count(2)
            ->create([
                'user_id' => $user->id,
                'status' => DonationStatus::COMPLETED->value,
            ]);

        // Pending donation for $user
        Donation::factory()->create([
            'user_id' => $user->id,
            'status' => DonationStatus::PENDING->value,
        ]);

        // Completed donation for another user
        Donation::factory()->create([
            'user_id' => $otherUser->id,
            'status' => DonationStatus::COMPLETED->value,
        ]);

        // ACT
        $response = $this->actingAs($user)->getJson("/api/donations/history/{$user->id}");
        print_r($response->json());

        // ASSERT
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'donations')
            ->assertJsonFragment([
                'user_id' => $user->id,
                'status' => DonationStatus::COMPLETED->value,
            ]);

        // Ensure only completed donations for $user are returned
        foreach ($response->json('donations') as $donation) {
            $this->assertEquals($user->id, $donation['user_id']);
            $this->assertEquals(DonationStatus::COMPLETED->value, $donation['status']);
        }
    }
}
