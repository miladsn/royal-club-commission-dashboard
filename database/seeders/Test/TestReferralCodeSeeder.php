<?php
namespace Database\Seeders\Test;

use App\Models\Exchange;
use App\Models\Product;
use App\Models\ReferralCode;
use Illuminate\Database\Seeder;

class TestReferralCodeSeeder extends Seeder
{
    public function run(): void
    {
        $exchanges = Exchange::all();
        $products = Product::all();

        if ($exchanges->isEmpty() || $products->isEmpty()) {
            return;
        }

        $counter = 1;
        foreach ($exchanges as $exchange) {
            foreach ($products as $index => $product) {
                // Keep previous codes for compatibility with TestExchangeUserSeeder
                if ($exchange->slug === 'lbank' && $index === 0) {
                    $code = 'ROYAL_LBANK_01';
                } elseif ($exchange->slug === 'bitunix' && $index === 0) {
                    $code = 'ROYAL_BIT_02';
                } else {
                    $code = strtoupper('ROYAL_' . $exchange->slug . '_' . ($index + 1));
                }

                ReferralCode::updateOrCreate(
                    [
                        'exchange_id' => $exchange->id,
                        'code' => $code,
                    ],
                    [
                        'product_id' => $product->id,
                    ]
                );
            }
        }
    }
}
