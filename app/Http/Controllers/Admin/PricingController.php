<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Pricing\CreatePricingAction;
use App\Actions\Admin\Pricing\CreatePricingTierAction;
use App\Actions\Admin\Pricing\DeletePricingAction;
use App\Actions\Admin\Pricing\DeletePricingTierAction;
use App\Actions\Admin\Pricing\UpdatePricingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatePricingRequest;
use App\Http\Requests\Admin\CreatePricingTierRequest;
use App\Http\Requests\Admin\UpdatePricingRequest;
use App\Models\Pricing;
use App\Models\PricingTier;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    /**
     * Create new pricing.
     */
    public function store(CreatePricingRequest $request, CreatePricingAction $action): JsonResponse
    {
        $pricing = $action->execute($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pricing created successfully.',
            'data' => $pricing,
        ], 201);
    }

    /**
     * Update pricing.
     */
    public function update(UpdatePricingRequest $request, string $id, UpdatePricingAction $action): JsonResponse
    {
        $pricing = Pricing::findOrFail($id);
        $updatedPricing = $action->execute($pricing, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pricing updated successfully.',
            'data' => $updatedPricing,
        ]);
    }

    /**
     * Delete pricing.
     */
    public function destroy(string $id, DeletePricingAction $action): JsonResponse
    {
        try {
            $pricing = Pricing::findOrFail($id);
            $action->execute($pricing);

            return response()->json([
                'success' => true,
                'message' => 'Pricing deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Manage pricing tiers.
     */
    public function storeTier(CreatePricingTierRequest $request, CreatePricingTierAction $action): JsonResponse
    {
        $tier = $action->execute($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pricing tier saved successfully.',
            'data' => $tier,
        ]);
    }

    /**
     * Delete pricing tier.
     */
    public function destroyTier(string $id, DeletePricingTierAction $action): JsonResponse
    {
        $tier = PricingTier::findOrFail($id);
        $action->execute($tier);

        return response()->json([
            'success' => true,
            'message' => 'Pricing tier deleted successfully.',
        ]);
    }
}
