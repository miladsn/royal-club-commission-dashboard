<?php
namespace Database\Seeders\Test;

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TestReferralCodeSeeder::class,
            TestExchangeUserSeeder::class,
            TestCommissionReportSeeder::class,
        ]);
    }
}

