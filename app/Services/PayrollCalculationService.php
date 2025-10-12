<?php

namespace App\Services;

/**
 * Payroll Calculation Service
 * Handles statutory deductions and salary calculations for Zambian payroll
 */
class PayrollCalculationService
{
    // Statutory rates
    const NAPSA_RATE = 0.05; // 5%

    const NHIMA_RATE = 0.01; // 1%

    // PAYE Tax Bands (Zambian Tax Rates 2024)
    const PAYE_BANDS = [
        ['min' => 0, 'max' => 4800, 'rate' => 0.00, 'cumulative' => 0],
        ['min' => 4800, 'max' => 6900, 'rate' => 0.25, 'cumulative' => 0],
        ['min' => 6900, 'max' => 11600, 'rate' => 0.30, 'cumulative' => 525],
        ['min' => 11600, 'max' => PHP_INT_MAX, 'rate' => 0.37, 'cumulative' => 1935],
    ];

    /**
     * Calculate NAPSA deduction (5% of basic salary, capped at ZMW 2,301.60)
     */
    public function calculateNAPSA(float $basicSalary): float
    {
        $napsa = $basicSalary * self::NAPSA_RATE;

        // Cap at maximum NAPSA ceiling (5% of ZMW 46,032)
        $maxNapsa = 2301.60;

        return min($napsa, $maxNapsa);
    }

    /**
     * Calculate NHIMA deduction (1% of gross salary)
     */
    public function calculateNHIMA(float $grossSalary): float
    {
        return $grossSalary * self::NHIMA_RATE;
    }

    /**
     * Calculate PAYE (Pay As You Earn) tax based on taxable income
     */
    public function calculatePAYE(float $grossSalary, float $napsa): float
    {
        // Taxable income = Gross - NAPSA
        $taxableIncome = $grossSalary - $napsa;

        // If below tax-free threshold
        if ($taxableIncome <= self::PAYE_BANDS[0]['max']) {
            return 0;
        }

        $tax = 0;

        foreach (self::PAYE_BANDS as $band) {
            if ($taxableIncome <= $band['min']) {
                break;
            }

            $taxableInBand = min($taxableIncome, $band['max']) - $band['min'];
            $tax = $band['cumulative'] + ($taxableInBand * $band['rate']);
        }

        return round($tax, 2);
    }

    /**
     * Calculate all statutory deductions
     */
    public function calculateStatutoryDeductions(float $basicSalary, array $allowances = []): array
    {
        // Calculate gross salary
        $totalAllowances = collect($allowances)->sum('amount');
        $grossSalary = $basicSalary + $totalAllowances;

        // Calculate statutory deductions
        $napsa = $this->calculateNAPSA($basicSalary);
        $nhima = $this->calculateNHIMA($grossSalary);
        $paye = $this->calculatePAYE($grossSalary, $napsa);

        return [
            [
                'type' => 'NAPSA',
                'amount' => round($napsa, 2),
                'description' => '5% of basic salary (Employee contribution)',
            ],
            [
                'type' => 'NHIMA',
                'amount' => round($nhima, 2),
                'description' => '1% of gross salary',
            ],
            [
                'type' => 'PAYE',
                'amount' => round($paye, 2),
                'description' => 'Pay As You Earn Tax',
            ],
        ];
    }

    /**
     * Calculate complete payroll breakdown
     */
    public function calculatePayroll(
        float $basicSalary,
        array $allowances = [],
        array $additionalDeductions = []
    ): array {
        // Calculate gross salary
        $totalAllowances = collect($allowances)->sum('amount');
        $grossSalary = $basicSalary + $totalAllowances;

        // Get statutory deductions
        $statutoryDeductions = $this->calculateStatutoryDeductions($basicSalary, $allowances);

        // Merge all deductions
        $allDeductions = array_merge($statutoryDeductions, $additionalDeductions);
        $totalDeductions = collect($allDeductions)->sum('amount');

        // Calculate net salary
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'basic_salary' => round($basicSalary, 2),
            'allowances' => $allowances,
            'total_allowances' => round($totalAllowances, 2),
            'gross_salary' => round($grossSalary, 2),
            'deductions' => $allDeductions,
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round($netSalary, 2),
            'statutory_breakdown' => [
                'napsa' => $statutoryDeductions[0]['amount'],
                'nhima' => $statutoryDeductions[1]['amount'],
                'paye' => $statutoryDeductions[2]['amount'],
            ],
        ];
    }
}
