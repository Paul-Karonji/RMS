<?php

namespace Tests\Unit;

use App\Services\ProRatedRentCalculator;
use Tests\TestCase;

class ProRatedRentCalculatorTest extends TestCase
{
    protected ProRatedRentCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ProRatedRentCalculator();
    }

    /** @test */
    public function move_in_on_day_1_returns_full_rent()
    {
        $result = $this->calculator->calculate('2025-01-01', 50000);

        $this->assertEquals(50000, $result['amount']);
        $this->assertFalse($result['is_prorated']);
        $this->assertEquals('Full month rent - moved in before 15th', $result['note']);
    }

    /** @test */
    public function move_in_on_day_15_returns_full_rent()
    {
        $result = $this->calculator->calculate('2025-01-15', 50000);

        $this->assertEquals(50000, $result['amount']);
        $this->assertFalse($result['is_prorated']);
    }

    /** @test */
    public function move_in_on_day_16_returns_half_rent()
    {
        $result = $this->calculator->calculate('2025-01-16', 50000);

        $this->assertEquals(25000, $result['amount']);
        $this->assertTrue($result['is_prorated']);
        $this->assertStringContainsString('Half month rent', $result['note']);
    }

    /** @test */
    public function move_in_on_last_day_returns_half_rent()
    {
        $result = $this->calculator->calculate('2025-01-31', 50000);

        $this->assertEquals(25000, $result['amount']);
        $this->assertTrue($result['is_prorated']);
        $this->assertEquals(1, $result['prorated_days']);
    }

    /** @test */
    public function handles_leap_year_february()
    {
        $result = $this->calculator->calculate('2024-02-20', 40000);

        $this->assertEquals(20000, $result['amount']);
        $this->assertTrue($result['is_prorated']);
        $this->assertEquals(10, $result['prorated_days']); // Feb 20-29 = 10 days
    }

    /** @test */
    public function first_payment_includes_deposit()
    {
        $result = $this->calculator->calculateFirstPayment('2025-01-20', 50000, 50000);

        $this->assertEquals(75000, $result['total_amount']); // 25000 rent + 50000 deposit
        $this->assertEquals(25000, $result['breakdown']['rent']);
        $this->assertEquals(50000, $result['breakdown']['deposit']);
        $this->assertTrue($result['is_prorated']);
    }
}
