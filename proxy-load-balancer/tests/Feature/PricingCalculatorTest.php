<?php

namespace Tests\Feature;

use App\Services\PricingCalculator;
use Exception;
use Tests\TestCase;

class PricingCalculatorTest extends TestCase
{
    private PricingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PricingCalculator();
    }

    public function test_basic_starter_plan_5gb()
    {
        $result = $this->calculator->calculate(5, 'starter');

        $this->assertEquals(50.00, $result['base_cost']);
        $this->assertEquals(50.00, $result['final_cost']);
        $this->assertEquals(0, $result['loyalty_discount_percent']);
        $this->assertEquals(0, $result['volume_discount_percent']);
    }

    public function test_tiered_pricing_15gb()
    {
        $result = $this->calculator->calculate(15, 'starter');

        // 10 GB × $10 + 5 GB × $8 = $140
        $this->assertEquals(140.00, $result['base_cost']);
        $this->assertEquals(140.00, $result['final_cost']);
    }

    public function test_pro_plan_with_loyalty_discount()
    {
        $result = $this->calculator->calculate(60, 'pro', 55);

        // Base: 50×$7 + 10×$5 = $400
        $this->assertEquals(400.00, $result['base_cost']);

        // Loyalty: 5% (55GB last month)
        $this->assertEquals(5.0, $result['loyalty_discount_percent']);
        $this->assertEquals(20.00, $result['loyalty_discount_amount']);

        // Final: $380
        $this->assertEquals(380.00, $result['final_cost']);
    }

    public function test_enterprise_with_all_discounts()
    {
        $result = $this->calculator->calculate(150, 'enterprise', 120);

        // Base: 100×$4 + 50×$3 = $550
        $this->assertEquals(550.00, $result['base_cost']);

        // Loyalty: 10% (120GB last month)
        $this->assertEquals(10.0, $result['loyalty_discount_percent']);
        $this->assertEquals(55.00, $result['loyalty_discount_amount']);

        // Volume: 2% (150GB)
        $this->assertEquals(2.0, $result['volume_discount_percent']);
        $this->assertEquals(9.90, $result['volume_discount_amount']);

        // Final: $485.10
        $this->assertEquals(485.10, $result['final_cost']);
    }

    public function test_max_volume_discount()
    {
        $result = $this->calculator->calculate(600, 'enterprise');

        // Base: 100×$4 + 500×$3 = $1900
        $this->assertEquals(1900.00, $result['base_cost']);

        // Volume: 10% max (600GB = 6×100GB but capped)
        $this->assertEquals(10.0, $result['volume_discount_percent']);
        $this->assertEquals(190.00, $result['volume_discount_amount']);

        // Final: $1710
        $this->assertEquals(1710.00, $result['final_cost']);
    }

    public function test_plan_recommendation()
    {
        $result = $this->calculator->recommendPlan(100);

        // Enterprise should be cheapest
        $this->assertEquals('enterprise', $result['recommended_plan']);

        // Enterprise cost should be lowest
        $enterpriseCost = $result['comparison']['enterprise']['cost'];
        $starterCost = $result['comparison']['starter']['cost'];
        $proCost = $result['comparison']['pro']['cost'];

        $this->assertLessThan($starterCost, $enterpriseCost);
        $this->assertLessThan($proCost, $enterpriseCost);

        // Savings for recommended should be 0
        $this->assertEquals(0, $result['comparison']['enterprise']['savings_vs_recommended']);
    }

    public function test_zero_usage()
    {
        $result = $this->calculator->calculate(0, 'pro');

        $this->assertEquals(0, $result['base_cost']);
        $this->assertEquals(0, $result['final_cost']);
        $this->assertEquals(0, $result['effective_rate_per_gb']);
    }

    public function test_invalid_plan_throws_exception()
    {
        $this->expectException(Exception::class);
        $this->calculator->calculate(10, 'invalid_plan');
    }

    public function test_discount_stacking_order_matters()
    {
        // Test that discounts are applied sequentially, not additively
        $result = $this->calculator->calculate(150, 'enterprise', 120);

        // Base: $550
        // If discounts were additive (12% total): $550 × 0.88 = $484
        // If discounts are sequential: $550 × 0.9 × 0.98 = $485.10

        $this->assertEquals(485.10, $result['final_cost']);
        // Proves sequential application
    }

    public function test_loyalty_discount_thresholds()
    {
        // 50GB or less = 0%
        $result1 = $this->calculator->calculate(10, 'starter', 50);
        $this->assertEquals(0, $result1['loyalty_discount_percent']);

        // 51-100GB = 5%
        $result2 = $this->calculator->calculate(10, 'starter', 75);
        $this->assertEquals(5.0, $result2['loyalty_discount_percent']);

        // Over 100GB = 10%
        $result3 = $this->calculator->calculate(10, 'starter', 150);
        $this->assertEquals(10.0, $result3['loyalty_discount_percent']);
    }

    public function test_volume_discount_calculation()
    {
        // 0-99GB = 0%
        $result1 = $this->calculator->calculate(99, 'enterprise');
        $this->assertEquals(0, $result1['volume_discount_percent']);

        // 100-199GB = 2%
        $result2 = $this->calculator->calculate(150, 'enterprise');
        $this->assertEquals(2.0, $result2['volume_discount_percent']);

        // 200-299GB = 4%
        $result3 = $this->calculator->calculate(250, 'enterprise');
        $this->assertEquals(4.0, $result3['volume_discount_percent']);

        // 500+GB = 10% (capped)
        $result4 = $this->calculator->calculate(700, 'enterprise');
        $this->assertEquals(10.0, $result4['volume_discount_percent']);
    }

    public function test_effective_rate_calculation()
    {
        $result = $this->calculator->calculate(150, 'enterprise', 120);

        // $485.10 / 150 GB = $3.23/GB
        $this->assertEquals(3.23, $result['effective_rate_per_gb']);
    }
}
