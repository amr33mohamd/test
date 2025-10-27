# ProxyScrape Pricing Calculator - Interview Task

**Duration**: 45 minutes | **Difficulty**: Medium | **Language**: PHP

---

## 🚀 Quick Setup (GitHub Codespaces)

### Simple 3-Step Setup - Works with PHP 8.0+

1. **Open this repository in GitHub Codespaces**
   - Click the green "Code" button → "Codespaces" → "Create codespace on main"

2. **Install dependencies** (run these commands):
   ```bash
   composer install
   php artisan key:generate
   ```

3. **Run tests!**
   ```bash
   vendor/bin/phpunit
   ```

   You should see **13 failing tests** - that's perfect! Your job is to make them pass.

### That's It!

Simple manual setup that works with the default Codespace PHP 8.0.

### Troubleshooting

**Problem**: `composer install` fails
**Solution**: Make sure you're in the correct directory (`/workspaces/test/proxy-load-balancer`)

**Problem**: `vendor/bin/phpunit` command not found
**Solution**: Run `composer install` first

**Problem**: Tests pass immediately without coding anything
**Solution**: Check that [app/Services/PricingCalculator.php](app/Services/PricingCalculator.php) has TODO comments in the methods

---

## 🎯 The Challenge

ProxyScrape is a proxy service provider that charges customers based on their bandwidth usage. You need to build a **pricing calculator** that:

1. **Calculates costs** based on tiered pricing (different rates for different usage levels)
2. **Applies loyalty discounts** for repeat customers
3. **Applies volume discounts** for high usage
4. **Stacks discounts correctly** (sequential application, not additive)
5. **Recommends the best plan** by comparing all options

This is a real-world business problem - every SaaS company needs accurate billing calculations!

---

## 📋 Business Rules

### Pricing Structure: Three Plans

ProxyScrape offers three subscription plans with **tiered pricing** (price decreases as you use more):

| Plan | Tier 1 (Initial GB) | Tier 2 (Additional GB) |
|------|---------------------|------------------------|
| **Starter** | $10/GB for first 10 GB | $8/GB thereafter |
| **Pro** | $7/GB for first 50 GB | $5/GB thereafter |
| **Enterprise** | $4/GB for first 100 GB | $3/GB thereafter |

**How Tiered Pricing Works:**

If a Starter plan customer uses **15 GB**:
- First 10 GB charged at $10/GB = $100
- Next 5 GB charged at $8/GB = $40
- **Total base cost: $140**

If a Pro plan customer uses **75 GB**:
- First 50 GB charged at $7/GB = $350
- Next 25 GB charged at $5/GB = $125
- **Total base cost: $475**

---

### Discount System

#### 1. Loyalty Discount (Based on Previous Month)

Reward customers who used the service last month:

| Last Month Usage | Discount |
|-----------------|----------|
| 0-50 GB | 0% (no discount) |
| 51-100 GB | 5% off |
| 100+ GB | 10% off |

**Example:**
- Customer used 75 GB last month → Gets 5% off this month
- Customer used 150 GB last month → Gets 10% off this month

---

#### 2. Volume Discount (Based on Current Usage)

Encourage high usage with volume discounts:

| Current Usage | Discount |
|--------------|----------|
| 0-99 GB | 0% |
| 100-199 GB | 2% |
| 200-299 GB | 4% |
| 300-399 GB | 6% |
| 400-499 GB | 8% |
| 500+ GB | 10% (maximum) |

**Formula:** `floor(usage_gb / 100) * 2%` capped at 10%

**Examples:**
- 150 GB → 2% discount (1 × 100 GB = 2%)
- 350 GB → 6% discount (3 × 100 GB = 6%)
- 800 GB → 10% discount (8 × 100 GB = 16%, but capped at 10%)

---

#### 3. Discount Stacking (CRITICAL!)

Discounts apply **sequentially**, not additively. Each discount applies to the already-discounted price.

**Order of application:**
```
Base Cost → Loyalty Discount → Volume Discount
```

**Example with $1000 base, 10% loyalty, 2% volume:**

✅ **CORRECT (Sequential):**
```
Step 1: $1000 (base)
Step 2: $1000 × 0.90 = $900 (after 10% loyalty)
Step 3: $900 × 0.98 = $882 (after 2% volume)
Final: $882
```

❌ **WRONG (Additive):**
```
Total discount: 10% + 2% = 12%
Final: $1000 × 0.88 = $880 ❌
```

**Why it matters:** Sequential discounting is standard business practice and results in higher final costs (better for business). It's also mathematically compound, not additive.

---

## 🛠️ Your Task: Implement 5 Methods

Edit the file: **`app/Services/PricingCalculator.php`**

All methods have detailed documentation in the file. Here's what each does:

---

### Method 1: `calculateBaseCost(float $usageGb, string $plan): float`

**Purpose:** Calculate the base cost before any discounts using tiered pricing.

**Input:**
- `$usageGb`: Bandwidth used in GB (e.g., 75.5)
- `$plan`: Plan name ('starter', 'pro', or 'enterprise')

**Output:** Base cost in dollars

**Algorithm:**
1. Get the tier structure for the plan from `self::PLANS` constant
2. Loop through each tier
3. Calculate how much usage falls in this tier using `min(remaining, tier_limit)`
4. Multiply tier usage by tier price
5. Subtract used amount from remaining
6. Continue to next tier if usage remains
7. Return total cost

**Example Implementation Flow:**

Pro plan, 75 GB:
```
Tier 1: First 50 GB at $7/GB
  - Usage in tier: min(75, 50) = 50 GB
  - Cost: 50 × $7 = $350
  - Remaining: 75 - 50 = 25 GB

Tier 2: Remaining at $5/GB
  - Usage in tier: min(25, INF) = 25 GB
  - Cost: 25 × $5 = $125
  - Remaining: 25 - 25 = 0 GB

Total: $350 + $125 = $475
```

**Hint:**
```php
$tiers = self::PLANS[$plan]['tiers'];
$cost = 0;
$remaining = $usageGb;

foreach ($tiers as $tier) {
    $tierUsage = min($remaining, $tier['limit']);
    $cost += $tierUsage * $tier['price'];
    $remaining -= $tierUsage;
    if ($remaining <= 0) break;
}
return $cost;
```

---

### Method 2: `getLoyaltyDiscount(float $lastMonthUsageGb): float`

**Purpose:** Determine loyalty discount percentage based on last month's usage.

**Input:** Last month's usage in GB

**Output:** Discount percentage (0.0, 5.0, or 10.0)

**Logic:**
```php
if ($lastMonthUsageGb > 100) return 10.0;  // Heavy user
if ($lastMonthUsageGb > 50) return 5.0;    // Regular user
return 0.0;                                 // New or light user
```

**Test Cases:**
- 25 GB last month → 0%
- 75 GB last month → 5%
- 150 GB last month → 10%

---

### Method 3: `getVolumeDiscount(float $usageGb): float`

**Purpose:** Calculate volume discount based on current usage.

**Input:** Current usage in GB

**Output:** Discount percentage (0 to 10)

**Formula:**
```php
$discount = floor($usageGb / 100) * 2;
return min($discount, 10);
```

**Examples:**
- 50 GB → floor(50/100) × 2 = 0%
- 150 GB → floor(150/100) × 2 = 2%
- 350 GB → floor(350/100) × 2 = 6%
- 700 GB → floor(700/100) × 2 = 14% → capped at 10%

---

### Method 4: `calculate(float $usageGb, string $plan, float $lastMonthUsageGb = 0): array`

**Purpose:** Main calculation method that brings everything together.

**Inputs:**
- `$usageGb`: Current month usage
- `$plan`: Plan name
- `$lastMonthUsageGb`: Previous month usage (default 0)

**Output:** Complete breakdown array

**Required Return Structure:**
```php
[
    'usage_gb' => 150.0,
    'plan' => 'enterprise',
    'base_cost' => 550.00,
    'loyalty_discount_percent' => 10.0,
    'loyalty_discount_amount' => 55.00,
    'volume_discount_percent' => 2.0,
    'volume_discount_amount' => 9.90,
    'total_discount_amount' => 64.90,
    'final_cost' => 485.10,
    'effective_rate_per_gb' => 3.23
]
```

**Algorithm Steps:**

```php
// 1. Validate inputs
if (!isset(self::PLANS[$plan])) {
    throw new Exception("Invalid plan: $plan");
}
if ($usageGb < 0) {
    throw new Exception("Usage cannot be negative");
}

// 2. Calculate base cost
$baseCost = $this->calculateBaseCost($usageGb, $plan);

// 3. Get discount percentages
$loyaltyPercent = $this->getLoyaltyDiscount($lastMonthUsageGb);
$volumePercent = $this->getVolumeDiscount($usageGb);

// 4. Apply loyalty discount
$loyaltyAmount = $baseCost * ($loyaltyPercent / 100);
$afterLoyalty = $baseCost - $loyaltyAmount;

// 5. Apply volume discount on discounted price
$volumeAmount = $afterLoyalty * ($volumePercent / 100);
$finalCost = $afterLoyalty - $volumeAmount;

// 6. Calculate additional fields
$totalDiscount = $loyaltyAmount + $volumeAmount;
$effectiveRate = $usageGb > 0 ? $finalCost / $usageGb : 0;

// 7. Build return array with ALL fields
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
```

**Detailed Example:**

Input: 150 GB Enterprise, 120 GB last month

```
1. Base Cost:
   calculateBaseCost(150, 'enterprise')
   → 100 GB × $4 = $400
   → 50 GB × $3 = $150
   → $550

2. Loyalty:
   getLoyaltyDiscount(120) → 10%
   Amount: $550 × 0.10 = $55
   After loyalty: $550 - $55 = $495

3. Volume:
   getVolumeDiscount(150) → 2%
   Amount: $495 × 0.02 = $9.90
   Final: $495 - $9.90 = $485.10

4. Totals:
   Total discount: $55 + $9.90 = $64.90
   Effective rate: $485.10 / 150 = $3.23/GB

5. Return:
   [
     'usage_gb' => 150.0,
     'plan' => 'enterprise',
     'base_cost' => 550.00,
     'loyalty_discount_percent' => 10.0,
     'loyalty_discount_amount' => 55.00,
     'volume_discount_percent' => 2.0,
     'volume_discount_amount' => 9.90,
     'total_discount_amount' => 64.90,
     'final_cost' => 485.10,
     'effective_rate_per_gb' => 3.23
   ]
```

---

### Method 5: `recommendPlan(float $usageGb, float $lastMonthUsageGb = 0): array`

**Purpose:** Compare all three plans and recommend the cheapest option.

**Inputs:**
- `$usageGb`: Expected usage for this month
- `$lastMonthUsageGb`: Last month usage (affects all plans equally)

**Output:** Recommendation with full comparison

**Algorithm:**

```php
// 1. Calculate cost for each plan
$costs = [];
foreach (['starter', 'pro', 'enterprise'] as $planName) {
    $result = $this->calculate($usageGb, $planName, $lastMonthUsageGb);
    $costs[$planName] = $result['final_cost'];
}

// 2. Find minimum cost
$minCost = min($costs);

// 3. Find which plan has minimum cost
$recommendedPlan = array_search($minCost, $costs);

// 4. Build comparison with savings
$comparison = [];
foreach ($costs as $planName => $cost) {
    $comparison[$planName] = [
        'cost' => round($cost, 2),
        'savings_vs_recommended' => round($cost - $minCost, 2)
    ];
}

// 5. Return recommendation
return [
    'recommended_plan' => $recommendedPlan,
    'estimated_usage_gb' => $usageGb,
    'comparison' => $comparison
];
```

**Example:**

Input: 100 GB, 0 GB last month

```
Calculate for each plan:
- Starter: (10×$10) + (90×$8) = $820
  With 2% volume: $820 × 0.98 = $803.60

- Pro: (50×$7) + (50×$5) = $600
  With 2% volume: $600 × 0.98 = $588.00

- Enterprise: 100×$4 = $400
  With 2% volume: $400 × 0.98 = $392.00 ← Cheapest!

Return:
{
  "recommended_plan": "enterprise",
  "estimated_usage_gb": 100,
  "comparison": {
    "starter": {
      "cost": 803.60,
      "savings_vs_recommended": 411.60
    },
    "pro": {
      "cost": 588.00,
      "savings_vs_recommended": 196.00
    },
    "enterprise": {
      "cost": 392.00,
      "savings_vs_recommended": 0.00
    }
  }
}
```

---

## 🚀 Getting Started

### 1. Setup

```bash
# Install dependencies
composer install

# Setup Laravel
cp .env.example .env
php artisan key:generate
```

### 2. Implement

Open `app/Services/PricingCalculator.php` and implement the 5 methods marked with `TODO`.

### 3. Test

```bash
# Run all tests
vendor/bin/phpunit

# Expected output:
# OK (13 tests, 42 assertions) ✅
```

---

## 🧪 Testing & Debugging

### Run Tests

```bash
# All tests
vendor/bin/phpunit

# Detailed readable output
vendor/bin/phpunit --testdox

# Specific test
vendor/bin/phpunit --filter test_basic_starter

# Verbose output (shows var_dump)
vendor/bin/phpunit --verbose
```

### Debug Your Code

```bash
# Add debugging in your method
var_dump($baseCost, $loyaltyPercent, $finalCost);

# Check syntax
php -l app/Services/PricingCalculator.php

# Run single test to focus
vendor/bin/phpunit --filter test_basic
```

### Test Cases Explained

The test suite includes 13 comprehensive tests:

1. **test_basic_starter_plan_5gb** - Simple calculation, no discounts
2. **test_tiered_pricing_15gb** - Tests tier boundary crossing
3. **test_pro_plan_with_loyalty_discount** - 5% loyalty discount
4. **test_enterprise_with_all_discounts** - Both discounts stacking
5. **test_max_volume_discount** - Volume discount cap at 10%
6. **test_plan_recommendation** - Recommends cheapest plan
7. **test_zero_usage** - Edge case: $0 for everything
8. **test_invalid_plan_throws_exception** - Error handling
9. **test_discount_stacking_order_matters** - Sequential vs additive
10. **test_loyalty_discount_thresholds** - All three loyalty levels
11. **test_volume_discount_calculation** - Multiple volume tiers
12. **test_effective_rate_calculation** - Per-GB rate accuracy
13. **Additional edge cases** - Negative numbers, validation

---

## 📊 Detailed Example Walkthrough

Let's walk through a complete calculation step-by-step:

**Scenario:** 150 GB on Enterprise plan, customer used 120 GB last month

### Step 1: Calculate Base Cost

```php
$baseCost = $this->calculateBaseCost(150, 'enterprise');
```

Enterprise tiers:
- Tier 1: 0-100 GB at $4/GB
- Tier 2: 100+ GB at $3/GB

Calculation:
```
First tier: min(150, 100) = 100 GB
  Cost: 100 × $4 = $400
  Remaining: 150 - 100 = 50 GB

Second tier: min(50, INF) = 50 GB
  Cost: 50 × $3 = $150
  Remaining: 50 - 50 = 0 GB

Base Cost: $400 + $150 = $550
```

### Step 2: Calculate Loyalty Discount

```php
$loyaltyPercent = $this->getLoyaltyDiscount(120);
// 120 > 100, so return 10.0
```

Apply discount:
```
Loyalty amount: $550 × 0.10 = $55.00
After loyalty: $550 - $55 = $495.00
```

### Step 3: Calculate Volume Discount

```php
$volumePercent = $this->getVolumeDiscount(150);
// floor(150 / 100) × 2 = 1 × 2 = 2.0
```

Apply discount:
```
Volume amount: $495 × 0.02 = $9.90
Final cost: $495 - $9.90 = $485.10
```

### Step 4: Calculate Additional Fields

```
Total discount: $55.00 + $9.90 = $64.90
Effective rate: $485.10 / 150 GB = $3.234/GB ≈ $3.23/GB
```

### Step 5: Return Complete Array

```php
return [
    'usage_gb' => 150.0,
    'plan' => 'enterprise',
    'base_cost' => 550.00,
    'loyalty_discount_percent' => 10.0,
    'loyalty_discount_amount' => 55.00,
    'volume_discount_percent' => 2.0,
    'volume_discount_amount' => 9.90,
    'total_discount_amount' => 64.90,
    'final_cost' => 485.10,
    'effective_rate_per_gb' => 3.23
];
```

---

## ⚠️ Common Mistakes & How to Avoid Them

### Mistake 1: Hardcoding Prices

❌ **Wrong:**
```php
if ($plan == 'starter') {
    if ($usageGb <= 10) {
        return $usageGb * 10;
    } else {
        return (10 * 10) + (($usageGb - 10) * 8);
    }
}
```

✅ **Correct:**
```php
$tiers = self::PLANS[$plan]['tiers'];
// Loop through tiers dynamically
```

**Why:** Hardcoding makes code unmaintainable. Use the PLANS constant.

---

### Mistake 2: Wrong Tier Logic

❌ **Wrong:**
```php
foreach ($tiers as $tier) {
    $cost += $usageGb * $tier['price']; // Uses ALL usage for each tier!
}
```

✅ **Correct:**
```php
$tierUsage = min($remaining, $tier['limit']);
$cost += $tierUsage * $tier['price'];
$remaining -= $tierUsage;
```

**Why:** Must track remaining usage and apply tier limits properly.

---

### Mistake 3: Additive Discounts

❌ **Wrong:**
```php
$totalDiscount = $loyaltyPercent + $volumePercent;
$final = $base * (1 - $totalDiscount/100);
```

✅ **Correct:**
```php
$afterLoyalty = $base * (1 - $loyaltyPercent/100);
$final = $afterLoyalty * (1 - $volumePercent/100);
```

**Why:** Discounts compound sequentially, not additively.

---

### Mistake 4: No Validation

❌ **Wrong:**
```php
public function calculate($usageGb, $plan, $lastMonth = 0) {
    $base = $this->calculateBaseCost($usageGb, $plan);
    // What if plan doesn't exist? What if usage is negative?
}
```

✅ **Correct:**
```php
if (!isset(self::PLANS[$plan])) {
    throw new Exception("Invalid plan: $plan");
}
if ($usageGb < 0) {
    throw new Exception("Usage cannot be negative");
}
```

**Why:** Always validate inputs to prevent errors and provide clear messages.

---

### Mistake 5: Missing Return Fields

❌ **Wrong:**
```php
return [
    'final_cost' => $finalCost
]; // Missing required fields!
```

✅ **Correct:**
```php
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
```

**Why:** Tests expect exact structure. Missing fields = failed tests.

---

### Mistake 6: Floating Point Rounding

❌ **Wrong:**
```php
return [
    'final_cost' => 485.1034567 // Too many decimals
];
```

✅ **Correct:**
```php
return [
    'final_cost' => round(485.1034567, 2) // $485.10
];
```

**Why:** Money should always be rounded to 2 decimal places.

---

## ⏱️ Time Management

```
Total: 45 minutes

3 min  → Read README thoroughly
         Understand business rules and discount stacking

8 min  → Implement calculateBaseCost()
         Get tiered pricing working correctly

3 min  → Implement getLoyaltyDiscount()
         Simple if/else logic

3 min  → Implement getVolumeDiscount()
         Simple formula with min()

12 min → Implement calculate()
         Bring it all together
         Build complete return array

10 min → Implement recommendPlan()
         Loop through plans, find minimum

6 min  → Test & debug
         Run vendor/bin/phpunit
         Fix any failing tests

------
45 min total
```

**Pro Tips:**
- Test after each method with `vendor/bin/phpunit --filter method_name`
- Use `var_dump()` liberally to debug
- Read test error messages carefully - they tell you what's wrong
- Start simple, then add validation and edge cases

---

## ✅ Success Criteria

When you run:
```bash
vendor/bin/phpunit
```

You should see:
```
PHPUnit 10.5.0 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.0
Configuration: /path/to/phpunit.xml

...............                                                  13 / 13 (100%)

Time: 00:00.234, Memory: 24.00 MB

OK (13 tests, 42 assertions)
```

All 13 tests passing = Complete! ✅

---

## 📊 Evaluation Rubric

| Category | Points | Details |
|----------|--------|---------|
| **Tiered Pricing** | 20 | Uses tiers correctly, handles boundaries |
| **Loyalty Discount** | 10 | Correct thresholds (50, 100) |
| **Volume Discount** | 10 | Correct formula, 10% cap |
| **Discount Stacking** | 15 | Sequential, not additive |
| **Return Structure** | 10 | All fields present and correct |
| **Plan Comparison** | 5 | Finds cheapest correctly |
| **Code Quality** | 20 | Clean, readable, uses constants |
| **Tests Pass** | 10 | All 13 tests pass |
| **Total** | **100** | |

**Grading:**
- **90-100 points:** Exceptional (Senior level) - Strong hire
- **75-89 points:** Strong (Hire) - Solid implementation
- **60-74 points:** Pass - Basic functionality works
- **< 60 points:** Needs improvement - Major issues

---

## 📚 Reference

**Your Implementation:**
- `app/Services/PricingCalculator.php`

**Test Suite:**
- `tests/Feature/PricingCalculatorTest.php`

**Key Commands:**
- `vendor/bin/phpunit` - Run all tests
- `vendor/bin/phpunit --testdox` - Readable test names
- `vendor/bin/phpunit --filter test_name` - Run specific test
- `vendor/bin/phpunit --verbose` - Show var_dump() output
- `php -l file.php` - Check syntax

---

## 🎯 Final Tips

1. **Read carefully** - All requirements are documented above
2. **Test frequently** - Run tests after each method
3. **Use constants** - Don't hardcode prices
4. **Validate inputs** - Check for invalid plan names, negative numbers
5. **Debug systematically** - Use var_dump(), run single tests
6. **Think about edge cases** - Zero usage, large numbers, missing data
7. **Check return structure** - Tests expect exact field names
8. **Round money values** - Always 2 decimal places
9. **Sequential discounts** - Not additive!
10. **Ask if unclear** - Better to clarify than guess

---

**Good luck! 🚀**

Work methodically. Test frequently. Debug systematically. You've got this!
