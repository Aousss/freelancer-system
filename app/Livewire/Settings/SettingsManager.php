<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Models\User;
use App\Services\InvoiceAggregatorService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SettingsManager extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'currency'; // 'currency', 'invoicing', 'profile', 'banking'

    public string $successMessage = '';

    // Currency & Financials
    public string $currency = 'MYR';

    public string $currencySymbol = 'RM';

    public string $currencyPosition = 'prefix'; // 'prefix', 'suffix'

    public float $defaultTaxRate = 0.00;

    public float $defaultHourlyRate = 150.00;

    // Invoicing & Numbering
    public string $invoicePrefix = 'INV-';

    public int $invoiceDueDays = 14;

    public string $invoiceNotesTemplate = "Payment due within 14 days of issue date.\nPlease remit payment to the bank account specified.";

    public string $nextCalculatedInvoiceNumber = '';

    // Business Profile
    public string $freelancerName = 'Alex Rivers';

    public string $businessName = 'Alex Rivers Studio';

    public string $businessSubtitle = 'Software Engineering & Consulting';

    public string $businessEmail = 'alex@rivers.dev';

    public string $businessPhone = '+60 12-345 6789';

    public string $businessRegistration = 'SSM: 202601002345 (003145678-X)';

    public string $businessAddress = "Level 28, Menara Ilham\nNo. 8, Jalan Binjai\n50450 Kuala Lumpur, Malaysia";

    // Bank Account & Remittance
    public string $bankName = 'Maybank (Malayan Banking Berhad)';

    public string $bankAccountName = 'Alex Rivers';

    public string $bankAccountNumber = '5140 1234 5678';

    public string $bankSwiftDuitnow = 'DuitNow ID: 900101-14-5555 / MBBEMYKL';

    public function mount(InvoiceAggregatorService $aggregator): void
    {
        if (request()->routeIs('profile')) {
            $this->activeTab = 'profile';
        }

        $this->currency = Setting::get('currency', 'MYR');
        $this->currencySymbol = Setting::get('currency_symbol', 'RM');
        $this->currencyPosition = Setting::get('currency_position', 'prefix');
        $this->defaultTaxRate = (float) Setting::get('default_tax_rate', 0.00);
        $this->defaultHourlyRate = (float) Setting::get('default_hourly_rate', 150.00);

        $this->invoicePrefix = Setting::get('invoice_prefix', 'INV-');
        $this->invoiceDueDays = (int) Setting::get('invoice_due_days', 14);
        $this->invoiceNotesTemplate = Setting::get('invoice_notes_template', "Payment due within 14 days of issue date.\nPlease remit payment to the bank account specified.");

        $this->freelancerName = Setting::get('freelancer_name', 'Alex Rivers');
        $this->businessName = Setting::get('business_name', 'Alex Rivers Studio');
        $this->businessSubtitle = Setting::get('business_subtitle', 'Software Engineering & Consulting');
        $this->businessEmail = Setting::get('business_email', 'alex@rivers.dev');
        $this->businessPhone = Setting::get('business_phone', '+60 12-345 6789');
        $this->businessRegistration = Setting::get('business_registration', 'SSM: 202601002345 (003145678-X)');
        $this->businessAddress = Setting::get('business_address', "Level 28, Menara Ilham\nNo. 8, Jalan Binjai\n50450 Kuala Lumpur, Malaysia");

        $this->bankName = Setting::get('bank_name', 'Maybank (Malayan Banking Berhad)');
        $this->bankAccountName = Setting::get('bank_account_name', 'Alex Rivers');
        $this->bankAccountNumber = Setting::get('bank_account_number', '5140 1234 5678');
        $this->bankSwiftDuitnow = Setting::get('bank_swift_duitnow', 'DuitNow ID: 900101-14-5555 / MBBEMYKL');

        $this->nextCalculatedInvoiceNumber = $aggregator->generateNextInvoiceNumber();
    }

    public function selectQuickCurrency(string $code, string $symbol): void
    {
        $this->currency = $code;
        $this->currencySymbol = $symbol;
    }

    public function saveSettings(InvoiceAggregatorService $aggregator): void
    {
        $this->validate([
            'currency' => ['required', 'string', 'size:3'],
            'currencySymbol' => ['required', 'string', 'max:10'],
            'currencyPosition' => ['required', 'in:prefix,suffix'],
            'defaultTaxRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'defaultHourlyRate' => ['required', 'numeric', 'min:0'],
            'invoicePrefix' => ['required', 'string', 'max:20'],
            'invoiceDueDays' => ['required', 'integer', 'min:1', 'max:180'],
            'freelancerName' => ['required', 'string', 'max:100'],
            'businessName' => ['required', 'string', 'max:150'],
            'businessEmail' => ['required', 'email', 'max:150'],
        ]);

        Setting::set('currency', strtoupper(trim($this->currency)), 'financial');
        Setting::set('currency_symbol', trim($this->currencySymbol), 'financial');
        Setting::set('currency_position', $this->currencyPosition, 'financial');
        Setting::set('default_tax_rate', $this->defaultTaxRate, 'financial');
        Setting::set('default_hourly_rate', $this->defaultHourlyRate, 'financial');

        Setting::set('invoice_prefix', trim($this->invoicePrefix), 'invoicing');
        Setting::set('invoice_due_days', $this->invoiceDueDays, 'invoicing');
        Setting::set('invoice_notes_template', $this->invoiceNotesTemplate, 'invoicing');

        Setting::set('freelancer_name', trim($this->freelancerName), 'profile');
        Setting::set('business_name', trim($this->businessName), 'profile');
        Setting::set('business_subtitle', trim($this->businessSubtitle), 'profile');
        Setting::set('business_email', trim($this->businessEmail), 'profile');
        Setting::set('business_phone', trim($this->businessPhone), 'profile');
        Setting::set('business_registration', trim($this->businessRegistration), 'profile');
        Setting::set('business_address', $this->businessAddress, 'profile');

        User::query()->first()?->update([
            'name' => trim($this->freelancerName),
            'email' => trim($this->businessEmail),
        ]);

        Setting::set('bank_name', trim($this->bankName), 'banking');
        Setting::set('bank_account_name', trim($this->bankAccountName), 'banking');
        Setting::set('bank_account_number', trim($this->bankAccountNumber), 'banking');
        Setting::set('bank_swift_duitnow', trim($this->bankSwiftDuitnow), 'banking');

        $this->nextCalculatedInvoiceNumber = $aggregator->generateNextInvoiceNumber();
        $this->successMessage = 'System settings saved successfully!';
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Settings Saved',
            'description' => "Preferences and currency ({$this->currency}) updated successfully.",
        ]);
    }

    public function render()
    {
        return view('livewire.settings.settings-manager');
    }
}
