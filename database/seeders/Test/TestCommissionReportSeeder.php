<?php
namespace Database\Seeders\Test;

use App\Models\CommissionReport;
use App\Models\Exchange;
use App\Models\Product;
use Illuminate\Database\Seeder;

class TestCommissionReportSeeder extends Seeder
{
    public function run(): void
    {
        $lbank = Exchange::where('slug', 'lbank')->first();
        $product = Product::first();

        if (!$lbank) return;

        // Sample reports for different UIDs
        $sampleReports = [
            ['uid' => 'UID_USER_1', 'volume' => 15000.00, 'commission' => 45.50, 'status' => 'matched'],
            ['uid' => 'UID_USER_2', 'volume' => 8200.50, 'commission' => 20.10, 'status' => 'matched'],
            ['uid' => 'UID_USER_31', 'volume' => 5000.00, 'commission' => 0.00, 'status' => 'unmatched'], // User without a code
            ['uid' => 'UID_UNKNOWN_99', 'volume' => 1200.00, 'commission' => 0.00, 'status' => 'unknown_uid'], // Unknown user in the system
        ];

        foreach ($sampleReports as $report) {
            CommissionReport::create([
                'exchange_id' => $lbank->id,
                'uid' => $report['uid'],
                'trade_volume' => $report['volume'],
                'commission' => $report['commission'],
                'referral_code' => $report['status'] === 'matched' ? 'ROYAL_LBANK_01' : null,
                'product_id' => $report['status'] === 'matched' ? $product?->id : null,
                'status' => $report['status'],
                'reported_at' => now(),
            ]);
        }
    }
}

