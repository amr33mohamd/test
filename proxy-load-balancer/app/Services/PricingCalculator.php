<?php

namespace App\Services;

use Exception;
class PricingCalculator
{
    const PLANS = [
        'starter' => [
            'tiers' => [
                ['limit' => 10, 'price' => 10.00],
                ['limit' => INF, 'price' => 8.00]
            ]
        ],
        'pro' => [
            'tiers' => [
                ['limit' => 50, 'price' => 7.00],
                ['limit' => INF, 'price' => 5.00]
            ]
        ],
        'enterprise' => [
            'tiers' => [
                ['limit' => 100, 'price' => 4.00],
                ['limit' => INF, 'price' => 3.00]
            ]
        ]
    ];

    /**
     * Calculate the total cost for bandwidth usage
     *
     * @param float $usageGb Bandwidth used in GB
     * @param string $plan Plan name: 'starter', 'pro', or 'enterprise'
     * @param float $lastMonthUsageGb Last month's usage for loyalty discount
     * @return array Result breakdown
     *
     * Return format:
     * [
     *   'usage_gb' => 150.0,
     *   'plan' => 'pro',
     *   'base_cost' => 850.00,
     *   'loyalty_discount_percent' => 10.0,
     *   'loyalty_discount_amount' => 85.00,
     *   'volume_discount_percent' => 2.0,
     *   'volume_discount_amount' => 15.30,
     *   'total_discount_amount' => 100.30,
     *   'final_cost' => 749.70,
     *   'effective_rate_per_gb' => 4.99
     * ]
     */
    public function calculate(float $usageGb, string $plan, float $lastMonthUsageGb = 0): array
    {
        // TODO: Implement
        //
        // Steps:
        // 1. Validate inputs (plan exists, usage >= 0)
        // 2. Calculate base cost using tiered pricing
        // 3. Apply loyalty discount (5% if >50GB, 10% if >100GB)
        // 4. Apply volume discount (2% per 100GB, max 10%)
        // 5. Return detailed breakdown with all fields
        //
        // Discount stacking example:
        // Base: $1000
        // After loyalty (10%): $1000 * 0.9 = $900
        // After volume (2%): $900 * 0.98 = $882
    }

    /**
     * Calculate base cost using tiered pricing
     *
     * Example for Pro plan (15 GB):
     * - First 50 GB costs $7/GB
     * - Since we only use 15 GB, cost = 15 * $7 = $105
     *
     * Example for Pro plan (60 GB):
     * - First 50 GB: 50 * $7 = $350
     * - Next 10 GB: 10 * $5 = $50
     * - Total: $400
     *
     * @param float $usageGb
     * @param string $plan
     * @return float
     */
    private function calculateBaseCost(float $usageGb, string $plan): float
    {
        // TODO: Implement tiered pricing logic
        //
        // Algorithm:
        // 1. Get tiers for the plan
        // 2. Loop through each tier
        // 3. Calculate how much usage falls in this tier
        // 4. Multiply by tier price
        // 5. Subtract used amount from remaining usage
        // 6. Stop when no usage left
    }

    /**
     * Calculate loyalty discount percentage based on last month's usage
     *
     * Rules:
     * - 0-50 GB last month: 0% discount
     * - 51-100 GB last month: 5% discount
     * - 100+ GB last month: 10% discount
     *
     * @param float $lastMonthUsageGb
     * @return float Discount percentage (0, 5, or 10)
     */
    private function getLoyaltyDiscount(float $lastMonthUsageGb): float
    {
        // TODO: Implement
        // Hint: Use if/elseif/else or match/switch
    }

    /**
     * Calculate volume discount percentage based on current usage
     *
     * Rules:
     * - 2% discount per 100 GB used
     * - Maximum 10% discount
     *
     * Examples:
     * - 0-99 GB: 0% discount
     * - 100-199 GB: 2% discount
     * - 200-299 GB: 4% discount
     * - 500+ GB: 10% discount (capped)
     *
     * @param float $usageGb Current usage
     * @return float Discount percentage (0-10)
     */
    private function getVolumeDiscount(float $usageGb): float
    {
        // TODO: Implement
        // Hint: Use floor($usageGb / 100) * 2, then cap at 10
    }

    /**
     * Compare plans and recommend the best one
     *
     * Calculates cost for all three plans and recommends the cheapest.
     *
     * @param float $usageGb Expected usage
     * @param float $lastMonthUsageGb Last month usage
     * @return array Best plan recommendation with cost comparison
     *
     * Return format:
     * [
     *   'recommended_plan' => 'pro',
     *   'estimated_usage_gb' => 100.0,
     *   'comparison' => [
     *     'starter' => [
     *       'cost' => 820.00,
     *       'savings_vs_recommended' => 232.00
     *     ],
     *     'pro' => [
     *       'cost' => 588.00,
     *       'savings_vs_recommended' => 196.00
     *     ],
     *     'enterprise' => [
     *       'cost' => 392.00,
     *       'savings_vs_recommended' => 0
     *     ]
     *   ]
     * ]
     */
    public function recommendPlan(float $usageGb, float $lastMonthUsageGb = 0): array
    {
        // TODO: Implement
        //
        // Algorithm:
        // 1. Calculate cost for each plan (starter, pro, enterprise)
        // 2. Find the cheapest plan
        // 3. Calculate savings vs recommended for each plan
        // 4. Return recommendation with full comparison
    }
}
