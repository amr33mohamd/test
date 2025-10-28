# ProxyScrape Pricing Calculator - Interview Task

**Duration**: 45 minutes | **Difficulty**: Medium | **Language**: PHP

---

## 🚀 Quick Setup (GitHub Codespaces)



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

****


---

### Method 2: `getLoyaltyDiscount(float $lastMonthUsageGb): float`

**Purpose:** Determine loyalty discount percentage based on last month's usage.

**Input:** Last month's usage in GB

**Output:** Discount percentage (0.0, 5.0, or 10.0)


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


---

**Good luck! 🚀**

Work methodically. Test frequently. Debug systematically. You've got this!
