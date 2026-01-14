<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run comprehensive seeder
        $this->artisan('db:seed', ['--class' => 'Week13ComprehensiveSeeder']);
    }

    /**
     * Test that leases endpoint responds within 500ms
     *
     * @test
     */
    public function leases_endpoint_responds_within_500ms()
    {
        $user = User::where('email', 'admin@primepropertieskenya.com')->first();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/leases');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $duration, "Leases API took {$duration}ms, expected < 500ms");
        
        // Output performance metric
        echo "\n✅ Leases API Response Time: " . round($duration, 2) . "ms\n";
    }

    /**
     * Test that payments endpoint responds within 500ms
     *
     * @test
     */
    public function payments_endpoint_responds_within_500ms()
    {
        $user = User::where('email', 'admin@primepropertieskenya.com')->first();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/payments');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $duration, "Payments API took {$duration}ms, expected < 500ms");
        
        // Output performance metric
        echo "\n✅ Payments API Response Time: " . round($duration, 2) . "ms\n";
    }

    /**
     * Test that leases endpoint returns correct data structure
     *
     * @test
     */
    public function leases_endpoint_returns_correct_data_structure()
    {
        $user = User::where('email', 'admin@primepropertieskenya.com')->first();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/leases');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'property',
                        'unit',
                        'tenant',
                        'monthly_rent',
                        'status',
                        'start_date',
                        'end_date'
                    ]
                ],
                'meta' => ['total', 'page', 'per_page']
            ]);
        
        // Verify we got the expected number of leases
        $this->assertCount(3, $response->json('data'), 'Expected 3 leases from seeder');
        
        echo "\n✅ Leases API returned correct structure with 3 leases\n";
    }

    /**
     * Test that payments endpoint returns correct data structure
     *
     * @test
     */
    public function payments_endpoint_returns_correct_data_structure()
    {
        $user = User::where('email', 'admin@primepropertieskenya.com')->first();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/payments');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'lease_id',
                        'payment_type',
                        'amount',
                        'payment_method',
                        'status',
                        'payment_date'
                    ]
                ],
                'meta' => ['total', 'per_page', 'current_page', 'last_page']
            ]);
        
        // Verify we got payments
        $totalPayments = $response->json('meta.total');
        $this->assertGreaterThan(0, $totalPayments, 'Expected payments from seeder');
        
        echo "\n✅ Payments API returned correct structure with {$totalPayments} payments\n";
    }

    /**
     * Test filtering leases by status
     *
     * @test
     */
    public function leases_can_be_filtered_by_status()
    {
        $user = User::where('email', 'admin@primepropertieskenya.com')->first();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/leases?status=active');
        
        $response->assertStatus(200);
        
        // All returned leases should have status 'active'
        $leases = $response->json('data');
        foreach ($leases as $lease) {
            $this->assertEquals('active', $lease['status']);
        }
        
        echo "\n✅ Lease filtering by status works correctly\n";
    }

    /**
     * Test filtering payments by status
     *
     * @test
     */
    public function payments_can_be_filtered_by_status()
    {
        $user = User::where('email', 'admin@primepropertieskenya.com')->first();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/payments?status=completed');
        
        $response->assertStatus(200);
        
        // All returned payments should have status 'completed'
        $payments = $response->json('data');
        foreach ($payments as $payment) {
            $this->assertEquals('completed', $payment['status']);
        }
        
        echo "\n✅ Payment filtering by status works correctly\n";
    }

    /**
     * Test filtering payments by payment method
     *
     * @test
     */
    public function payments_can_be_filtered_by_payment_method()
    {
        $user = User::where('email', 'admin@primepropertieskenya.com')->first();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/payments?payment_method=mpesa');
        
        $response->assertStatus(200);
        
        // All returned payments should have payment_method 'mpesa'
        $payments = $response->json('data');
        foreach ($payments as $payment) {
            $this->assertEquals('mpesa', $payment['payment_method']);
        }
        
        echo "\n✅ Payment filtering by payment method works correctly\n";
    }
}
