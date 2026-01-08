<?php

namespace App\Services;

use Carbon\Carbon;

class ProRatedRentCalculator
{
    /**
     * Calculate pro-rated rent based on move-in date
     * 
     * Business Rule:
     * - Move-in on day 1-15: Full month rent
     * - Move-in on day 16-31: Half month rent
     *
     * @param string $moveInDate
     * @param float $monthlyRent
     * @return array
     */
    public function calculate(string $moveInDate, float $monthlyRent): array
    {
        $date = Carbon::parse($moveInDate);
        $dayOfMonth = $date->day;
        
        if ($dayOfMonth <= 15) {
            return [
                'amount' => $monthlyRent,
                'is_prorated' => false,
                'note' => 'Full month rent - moved in before 15th',
                'calculation_details' => [
                    'move_in_day' => $dayOfMonth,
                    'monthly_rent' => $monthlyRent,
                    'prorated_amount' => $monthlyRent,
                ]
            ];
        } else {
            $proratedAmount = $monthlyRent / 2;
            $daysInMonth = $date->daysInMonth;
            $proratedDays = $daysInMonth - $dayOfMonth + 1;
            
            return [
                'amount' => $proratedAmount,
                'is_prorated' => true,
                'prorated_days' => $proratedDays,
                'note' => "Half month rent - moved in on day {$dayOfMonth}",
                'calculation_details' => [
                    'move_in_day' => $dayOfMonth,
                    'days_in_month' => $daysInMonth,
                    'prorated_days' => $proratedDays,
                    'monthly_rent' => $monthlyRent,
                    'prorated_amount' => $proratedAmount,
                ]
            ];
        }
    }

    /**
     * Calculate first payment (prorated rent + deposit)
     *
     * @param string $moveInDate
     * @param float $monthlyRent
     * @param float $depositAmount
     * @return array
     */
    public function calculateFirstPayment(string $moveInDate, float $monthlyRent, float $depositAmount): array
    {
        $rentCalculation = $this->calculate($moveInDate, $monthlyRent);
        
        return [
            'total_amount' => $rentCalculation['amount'] + $depositAmount,
            'breakdown' => [
                'rent' => $rentCalculation['amount'],
                'deposit' => $depositAmount,
            ],
            'is_prorated' => $rentCalculation['is_prorated'],
            'note' => $rentCalculation['note'],
            'calculation_details' => $rentCalculation['calculation_details'],
        ];
    }
}
