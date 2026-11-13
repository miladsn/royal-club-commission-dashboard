<?php
namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Test\TestDatabaseSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Create the main admin/test user for Filament panel access
        User::updateOrCreate(
            ['email' => 'admin@royalclub.test'],
            [
                'name' => 'Royal Admin',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        //Run the permanent base seeders (real Royal Club exchanges and products)
        $this->call([
            ExchangeSeeder::class,
            ProductSeeder::class,
        ]);

        //Run demo seeders (referral codes, 35 test exchange users, and commission reports)
        //  This line can be commented out in production.
        $this->call([
            TestDatabaseSeeder::class,
        ]);
    }
}
