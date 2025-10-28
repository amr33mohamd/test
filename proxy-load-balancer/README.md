# ProxyScrape Pricing Calculator - Interview Task

**Duration**: 45 minutes | **Difficulty**: Medium | **Language**: PHP

---

## Setup

1. Open in GitHub Codespaces
2. Run:
   ```bash
   composer install
   php artisan key:generate
   vendor/bin/phpunit
   ```
3. You should see **13 failing tests** - implement the 5 methods to make them pass!

---

## The Challenge

Build a pricing calculator for a proxy service with:
1. **Tiered pricing** (different rates per usage level)
2. **Loyalty discounts** (5-10% for repeat customers)
3. **Volume discounts** (2% per 100GB, max 10%)
4. **Sequential discount stacking** (NOT additive)
5. **Plan recommendation** (find cheapest option)

**Edit**: `app/Services/PricingCalculator.php`

---

## Business Rules

### Pricing Plans

| Plan | Tier 1 | Tier 2 |
|------|--------|--------|
| **Starter** | $10/GB (first 10 GB) | $8/GB (rest) |
| **Pro** | $7/GB (first 50 GB) | $5/GB (rest) |
| **Enterprise** | $4/GB (first 100 GB) | $3/GB (rest) |

**Example**: Pro plan, 75 GB usage
- 50 GB × $7 = $350
- 25 GB × $5 = $125
- **Base cost: $475**

### Discounts

**Loyalty** (based on last month usage):
- 0-50 GB → 0%
- 51-100 GB → 5%
- 100+ GB → 10%

**Volume** (based on current usage):
- Formula: `floor(usage / 100) × 2%` (max 10%)
- 150 GB → 2% discount
- 500 GB → 10% discount

### Discount Stacking (CRITICAL!)

Discounts apply **sequentially**, not additively:

```
Base Cost → Apply Loyalty → Apply Volume
```

**Example**: $1000 base, 10% loyalty, 2% volume
- ✅ Correct: $1000 → $900 (loyalty) → $882 (volume) = **$882**
- ❌ Wrong: $1000 × (1 - 0.10 - 0.02) = $880

---

## What to Implement

Implement these 5 methods in `app/Services/PricingCalculator.php`:

### 1. `calculateBaseCost(float $usageGb, string $plan): float`
Calculate cost using tiered pricing.

### 2. `getLoyaltyDiscount(float $lastMonthUsageGb): float`
Return loyalty discount percentage (0, 5, or 10).

### 3. `getVolumeDiscount(float $usageGb): float`
Return volume discount percentage (0-10).

### 4. `calculate(float $usageGb, string $plan, float $lastMonthUsageGb = 0): array`
Main calculation with full breakdown. Return format:
```php
[
    'usage_gb' => 150.0,
    'plan' => 'pro',
    'base_cost' => 475.00,
    'loyalty_discount_percent' => 10.0,
    'loyalty_discount_amount' => 47.50,
    'volume_discount_percent' => 2.0,
    'volume_discount_amount' => 8.55,
    'total_discount_amount' => 56.05,
    'final_cost' => 418.95,
    'effective_rate_per_gb' => 2.79
]
```

### 5. `recommendPlan(float $usageGb, float $lastMonthUsageGb = 0): array`
Compare all plans and recommend cheapest. Return format:
```php
[
    'recommended_plan' => 'enterprise',
    'estimated_usage_gb' => 150.0,
    'comparison' => [
        'starter' => ['cost' => 1260.00, 'savings_vs_recommended' => 777.00],
        'pro' => ['cost' => 588.00, 'savings_vs_recommended' => 105.00],
        'enterprise' => ['cost' => 483.00, 'savings_vs_recommended' => 0]
    ]
]
```

---

## Testing

Run tests:
```bash
vendor/bin/phpunit
```

Run specific test:
```bash
vendor/bin/phpunit --filter test_basic_starter_plan
```

Verbose output:
```bash
vendor/bin/phpunit --testdox
```

---

## Tips

1. **Start simple**: Implement `calculateBaseCost()` first, then add discounts
2. **Test incrementally**: Run tests after each method
3. **Watch discount order**: Loyalty first, then volume
4. **Handle edge cases**: Zero usage, invalid plans
5. **Use `var_dump()`** to debug: `var_dump($result); die();`

---

## Time Management (45 min)

- **5 min**: Read requirements
- **30 min**: Implement methods (6 min each)
- **10 min**: Test and debug

Good luck! 🚀
