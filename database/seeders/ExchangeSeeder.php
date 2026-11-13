<?php
namespace Database\Seeders;

use App\Models\Exchange;
use Illuminate\Database\Seeder;

class ExchangeSeeder extends Seeder
{
    public function run(): void
    {
        $exchanges = [
            ['name' => 'LBank', 'type' => 'exchange', 'slug' => 'lbank', 'sync_method' => 'api', 'is_active' => true],
            ['name' => 'Orbit', 'type' => 'exchange', 'slug' => 'orbit', 'sync_method' => 'api', 'is_active' => true],
            ['name' => 'Bitunix', 'type' => 'exchange', 'slug' => 'bitunix', 'sync_method' => 'excel', 'is_active' => true],
        ];


        foreach ($exchanges as $exchange) {
            Exchange::updateOrCreate(
                ['slug' => $exchange['slug']],
                $exchange
            );
        }
    }
}
