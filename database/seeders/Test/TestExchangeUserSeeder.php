<?php
namespace Database\Seeders\Test;

use App\Models\Exchange;
use App\Models\ExchangeUser;
use App\Models\ReferralCode;
use Illuminate\Database\Seeder;

class TestExchangeUserSeeder extends Seeder
{
    public function run(): void
    {
        $lbank = Exchange::where('slug', 'lbank')->first();
        $bitunix = Exchange::where('slug', 'bitunix')->first();

        $lbankCode = ReferralCode::where('code', 'ROYAL_LBANK_01')->first();
        $bitunixCode = ReferralCode::where('code', 'ROYAL_BIT_02')->first();

        // LBank exchange users
        if ($lbank) {
            // LBank users with our referral code
            for ($i = 1; $i <= 20; $i++) {
                ExchangeUser::updateOrCreate(
                    ['exchange_id' => $lbank->id, 'uid' => 'LB_USER_' . $i],
                    ['referral_code_id' => $lbankCode?->id]
                );
            }
            // LBank users without our referral code
            for ($i = 21; $i <= 25; $i++) {
                ExchangeUser::updateOrCreate(
                    ['exchange_id' => $lbank->id, 'uid' => 'LB_USER_' . $i],
                    ['referral_code_id' => null]
                );
            }
        }

        // Bitunix exchange users
        if ($bitunix) {
            // Bitunix users with our referral code
            for ($i = 1; $i <= 15; $i++) {
                ExchangeUser::updateOrCreate(
                    ['exchange_id' => $bitunix->id, 'uid' => 'BX_USER_' . $i],
                    ['referral_code_id' => $bitunixCode?->id]
                );
            }
        }
    }
}


