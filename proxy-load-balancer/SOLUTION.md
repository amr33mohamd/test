# Pricing Calculator - Complete Solution

> **⚠️ DO NOT share with candidates!**

---

## Complete Implementation

```php
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

    public function calculate(float $usageGb, string $plan, float $lastMonthUsageGb = 0): array
    {
        // Validate inputs
        if (!isset(self::PLANS[$plan])) {
            throw new Exception("Invalid plan: $plan");
        }

        if ($usageGb < 0) {
            throw new Exception("Usage cannot be negative");
        }

        // Calculate base cost
        $baseCost = $this->calculateBaseCost($usageGb, $plan);

        // Get discount percentages
        $loyaltyPercent = $this->getLoyaltyDiscount($lastMonthUsageGb);
        $volumePercent = $this->getVolumeDiscount($usageGb);

        // Apply loyalty discount
        $loyaltyAmount = $baseCost * ($loyaltyPercent / 100);
        $afterLoyalty = $baseCost - $loyaltyAmount;

        // Apply volume discount on discounted price
        $volumeAmount = $afterLoyalty * ($volumePercent / 100);
        $finalCost = $afterLoyalty - $volumeAmount;

        // Calculate total discount
        $totalDiscount = $loyaltyAmount + $volumeAmount;

        // Calculate effective rate
        $effectiveRate = $usageGb > 0 ? $finalCost / $usageGb : 0;

        return [
            'usage_gb' => $usageGb,
            'plan' => $plan,
            'base_cost' => round($baseCost, 2),
            'loyalty_discount_percent' => $loyaltyPercent,
            'loyalty_discount_amount' => round($loyaltyAmount, 2),
            'volume_discount_percent' => $volumePercent,
            'volume_discount_amount' => round($volumeAmount, 2),
            'total_discount_amount' => round($totalDiscount, 2),
            'final_cost' => round($finalCost, 2),
            'effective_rate_per_gb' => round($effectiveRate, 2)
        ];
    }

    private function calculateBaseCost(float $usageGb, string $plan): float
    {
        $tiers = self::PLANS[$plan]['tiers'];
        $cost = 0;
        $remaining = $usageGb;

        foreach ($tiers as $tier) {
            // Calculate how much usage falls in this tier
            $tierUsage = min($remaining, $tier['limit']);

            // Add cost for this tier
            $cost += $tierUsage * $tier['price'];

            // Subtract from remaining
            $remaining -= $tierUsage;

            // If no usage left, stop
            if ($remaining <= 0) {
                break;
            }
        }

        return $cost;
    }

    private function getLoyaltyDiscount(float $lastMonthUsageGb): float
    {
        if ($lastMonthUsageGb > 100) {
            return 10.0;
        } elseif ($lastMonthUsageGb > 50) {
            return 5.0;
        } else {
            return 0.0;
        }
    }

    private function getVolumeDiscount(float $usageGb): float
    {
        // 2% per 100 GB
        $discount = floor($usageGb / 100) * 2;

        // Cap at 10%
        return min($discount, 10);
    }

    public function recommendPlan(float $usageGb, float $lastMonthUsageGb = 0): array
    {
        $costs = [];

        // Calculate cost for each plan
        foreach (array_keys(self::PLANS) as $planName) {
            $result = $this->calculate($usageGb, $planName, $lastMonthUsageGb);
            $costs[$planName] = $result['final_cost'];
        }

        // Find minimum cost
        $minCost = min($costs);

        // Find recommended plan
        $recommendedPlan = array_search($minCost, $costs);

        // Build comparison
        $comparison = [];
        foreach ($costs as $planName => $cost) {
            $comparison[$planName] = [
                'cost' => round($cost, 2),
                'savings_vs_recommended' => round($cost - $minCost, 2)
            ];
        }

        return [
            'recommended_plan' => $recommendedPlan,
            'estimated_usage_gb' => $usageGb,
            'comparison' => $comparison
        ];
    }
}
```

---

## Test Results

### Test 1: Basic (5 GB, Starter)
```
Base: 5 × $10 = $50
No discounts
Final: $50.00 ✓
```

### Test 2: Tiered (15 GB, Starter)
```
Tier 1: 10 GB × $10 = $100
Tier 2: 5 GB × $8 = $40
Base: $140.00 ✓
```

### Test 3: Loyalty (60 GB Pro, 55 GB last month)
```
Base: 50×$7 + 10×$5 = $400
Loyalty (5%): $400 × 0.95 = $380
Final: $380.00 ✓
```

### Test 4: All Discounts (150 GB Enterprise, 120 GB last)
```
Base: 100×$4 + 50×$3 = $550
Loyalty (10%): $550 × 0.9 = $495
Volume (2%): $495 × 0.98 = $485.10 ✓
```

### Test 5: Max Volume (600 GB Enterprise)
```
Base: 100×$4 + 500×$3 = $1900
Volume (10% max): $1900 × 0.9 = $1710.00 ✓
```

### Test 6: Recommendation (100 GB)
```
Starter: $820 - 2% = $803.60
Pro: $600 - 2% = $588.00
Enterprise: $400 - 2% = $392.00 ← Recommended ✓
```

### Test 7: Zero Usage
```
All costs: $0.00 ✓
```

### Test 8: Invalid Plan
```
Exception thrown ✓
```

---

## Key Implementation Details

### 1. Discount Stacking

**CORRECT (Sequential):**
```php
$afterLoyalty = $baseCost * (1 - $loyaltyPercent/100);
$final = $afterLoyalty * (1 - $volumePercent/100);
```

**WRONG (Additive):**
```php
$final = $baseCost * (1 - ($loyaltyPercent + $volumePercent)/100);
```

### 2. Tiered Pricing

Key: Use `min()` to handle tier limits:
```php
$tierUsage = min($remaining, $tier['limit']);
```

This ensures you never exceed the tier limit.

### 3. Volume Discount

```php
floor($usageGb / 100) * 2  // 2% per 100GB
min($discount, 10)          // Cap at 10%
```

---

## Common Mistakes

### ❌ Wrong Discount Stacking
```php
// WRONG - applies both to original
$total = $base - ($base * 0.10) - ($base * 0.02);

// Results in $880 instead of $882
```

### ❌ Not Handling Tier Limits
```php
// WRONG - doesn't subtract from remaining
foreach ($tiers as $tier) {
    $cost += $usageGb * $tier['price'];
}
```

### ❌ Hardcoding Prices
```php
// WRONG
if ($plan == 'starter') {
    if ($usageGb <= 10) {
        return $usageGb * 10;
    }
}

// CORRECT - uses PLANS constant
$tiers = self::PLANS[$plan]['tiers'];
```

### ❌ Wrong Loyalty Thresholds
```php
// WRONG - uses >= instead of >
if ($lastMonth >= 100) {
    return 10.0;
}

// CORRECT
if ($lastMonth > 100) {
    return 10.0;
}
```

---

## Scoring Guide

### 90-100: Exceptional
- All methods perfect
- All tests pass
- Clean code
- Edge cases handled
- Proper validation
- Good naming

### 75-89: Strong
- Core logic correct
- Most tests pass
- Decent code quality
- Some edge cases

### 60-74: Pass
- Basic functionality works
- Math mostly correct
- Some tests pass

### <60: Fail
- Many tests fail
- Wrong math
- Poor code quality

---

## Interview Red Flags

❌ Can't explain discount stacking
❌ Hardcodes all prices instead of using constant
❌ Doesn't handle edge cases (zero, negative, invalid)
❌ Math is consistently wrong
❌ Can't debug when tests fail
❌ Copy-pastes without understanding

---

## Interview Green Flags

✅ Tests frequently during development
✅ Handles edge cases proactively
✅ Clean, readable code
✅ Uses constants properly
✅ Can explain the math
✅ Asks clarifying questions
✅ Debugs systematically

---

## Time Complexity

All methods are **O(1)** or **O(k)** where k = number of tiers (max 2):

- `calculateBaseCost()`: O(k) - loops through tiers
- `getLoyaltyDiscount()`: O(1) - simple conditionals
- `getVolumeDiscount()`: O(1) - one calculation
- `calculate()`: O(k) - calls calculateBaseCost
- `recommendPlan()`: O(n×k) where n = 3 plans

Total: Very efficient, no performance concerns.

---

## Extension Ideas

If candidate finishes early, ask about:

1. **Promo Codes**
   - How to add percentage or fixed-amount codes?
   - Should they stack with other discounts?

2. **Annual Plans**
   - 12-month commitment with discount
   - How to modify pricing structure?

3. **Overage Charges**
   - What if user goes over plan limit?
   - Different pricing for overages?

4. **Currency Support**
   - Multiple currencies
   - Exchange rates
   - Rounding rules per currency

5. **Tax Calculation**
   - VAT/GST support
   - Region-based tax rates
   - Tax-inclusive vs tax-exclusive

---

This solution is production-ready and demonstrates strong PHP skills, business logic understanding, and clean code practices.
