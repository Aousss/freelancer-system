<?php

namespace Tests\Feature;

use App\Livewire\Settings\SettingsManager;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DemoFreelanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoFreelanceSeeder::class);
        $this->actingAs(User::first());
    }

    public function test_settings_page_renders_successfully(): void
    {
        $response = $this->get('/settings');
        $response->assertOk()
            ->assertSeeText('System & Billing Settings')
            ->assertSeeText('Currency & Financials');
    }

    public function test_can_save_currency_and_system_settings(): void
    {
        Livewire::test(SettingsManager::class)
            ->set('currency', 'USD')
            ->set('currencySymbol', '$')
            ->set('currencyPosition', 'prefix')
            ->set('defaultTaxRate', 8.5)
            ->set('defaultHourlyRate', 200.0)
            ->set('invoicePrefix', 'ACME-')
            ->set('invoiceDueDays', 30)
            ->set('freelancerName', 'Firdaus Developer')
            ->set('businessName', 'Firdaus Tech Studio')
            ->set('businessEmail', 'firdaus@example.com')
            ->set('bankName', 'CIMB Bank')
            ->set('bankAccountNumber', '8888 1234 5678')
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'System settings saved successfully!')
            ->assertDispatched('toast');

        // Assert database records
        $this->assertEquals('USD', Setting::get('currency'));
        $this->assertEquals('$', Setting::get('currency_symbol'));
        $this->assertEquals('8.5', Setting::get('default_tax_rate'));
        $this->assertEquals('ACME-', Setting::get('invoice_prefix'));
        $this->assertEquals('30', Setting::get('invoice_due_days'));
        $this->assertEquals('Firdaus Developer', Setting::get('freelancer_name'));
        $this->assertEquals('Firdaus Tech Studio', Setting::get('business_name'));
        $this->assertEquals('CIMB Bank', Setting::get('bank_name'));
    }

    public function test_quick_currency_picker_updates_code_and_symbol(): void
    {
        Livewire::test(SettingsManager::class)
            ->call('selectQuickCurrency', 'EUR', '€')
            ->assertSet('currency', 'EUR')
            ->assertSet('currencySymbol', '€')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertEquals('EUR', Setting::get('currency'));
        $this->assertEquals('€', Setting::get('currency_symbol'));
    }
}
