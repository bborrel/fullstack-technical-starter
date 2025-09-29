<?php

namespace App\Http\Controllers;

use App\DonationStatus;
use App\Http\Requests\StoreDonationRequest;
use App\Http\Requests\UpdateDonationRequest;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DonationController extends Controller
{
    public function store(StoreDonationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $donation = Donation::create([
            'user_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'currency' => strtoupper($validated['currency']),
            'donated_at' => now(),
        ])->refresh();

        return response()->json([
            'message' => 'Donation created successfully',
            'donation' => $donation,
        ], Response::HTTP_CREATED);
    }

    public function update(Donation $donation, UpdateDonationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $donation->update($validated);

        return response()->json([
            'message' => 'Donation updated successfully',
            'donation' => $donation,
        ], Response::HTTP_OK);
    }

    public function historyByUser(string $user_id): JsonResponse
    {
        $donations = Donation::where('user_id', $user_id)
            ->orderBy('donated_at', 'desc')
            ->where('status', DonationStatus::COMPLETED->value)
            ->get();

        return response()->json([
            'donations' => DonationResource::collection($donations),
        ], Response::HTTP_OK);
    }
}
