<?php

namespace Tests\Feature;

use App\Models\CommissionReport;
use App\Models\Exchange;
use App\Models\ExchangeUser;
use App\Models\Product;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\Exchanges\LBank\LBankProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_matches_product_and_updates_an_existing_report(): void
    {
        $exchange = Exchange::create(['name' => 'LBank', 'slug' => 'lbank']);
        $product = Product::create(['title' => 'Test product']);
        $referral = ReferralCode::create([
            'exchange_id' => $exchange->id,
            'product_id' => $product->id,
            'code' => 'TEST-CODE',
        ]);
        ExchangeUser::create([
            'exchange_id' => $exchange->id,
            'uid' => 'USER-1',
            'referral_code_id' => $referral->id,
        ]);
        $row = [
            'uid' => 'USER-1',
            'trade_volume' => 100,
            'commission' => 5,
            'reported_at' => '2026-01-15 12:00:00',
        ];

        $provider = new LBankProvider();
        $this->assertSame(1, $provider->saveReports([$row])['created']);
        $row['commission'] = 7;
        $this->assertSame(1, $provider->saveReports([$row])['updated']);
        $this->assertDatabaseCount('commission_reports', 1);
        $this->assertDatabaseHas('commission_reports', [
            'uid' => 'USER-1',
            'status' => 'matched',
            'referral_code' => 'TEST-CODE',
            'product_id' => $product->id,
            'commission' => 7,
        ]);
    }

    public function test_chat_requires_authentication(): void
    {
        $this->postJson(route('ai.chat'), ['slug' => 'total-summary'])
            ->assertUnauthorized();
    }

    public function test_predefined_summary_respects_page_filters(): void
    {
        $exchange = Exchange::create(['name' => 'LBank', 'slug' => 'lbank']);
        $other = Exchange::create(['name' => 'Orbit', 'slug' => 'orbit']);
        foreach ([[$exchange, 'USER-1', 100, 5], [$other, 'USER-2', 300, 10]] as [$owner, $uid, $volume, $commission]) {
            CommissionReport::create([
                'exchange_id' => $owner->id,
                'uid' => $uid,
                'trade_volume' => $volume,
                'commission' => $commission,
                'status' => 'unknown_uid',
                'reported_at' => '2026-01-15 12:00:00',
            ]);
        }

        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('ai.chat'), [
            'slug' => 'total-summary',
            'filters' => ['exchange_id' => $exchange->id, 'uids' => 'USER-1'],
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['slug' => 'total-summary'])
            ->assertSee('100.00')
            ->assertSee('5.00')
            ->assertDontSee('300.00');

        $this->actingAs($user)->postJson(route('ai.chat'), ['slug' => 'total-summary'])
            ->assertOk()
            ->assertSee('400.00')
            ->assertSee('15.00');
    }
}
