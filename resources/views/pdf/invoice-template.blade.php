<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 25mm 20mm 25mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-title {
            font-size: 20pt;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 9pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3px;
        }
        .invoice-title {
            font-size: 24pt;
            font-weight: 800;
            text-align: right;
            color: #3b82f6;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .invoice-meta {
            text-align: right;
            font-size: 9.5pt;
            color: #64748b;
            margin-top: 5px;
        }
        .meta-boxes {
            width: 100%;
            margin-bottom: 30px;
        }
        .meta-boxes table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-box {
            vertical-align: top;
            width: 50%;
        }
        .box-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .client-name {
            font-size: 13pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .address-text {
            font-size: 9.5pt;
            color: #475569;
            white-space: pre-line;
        }
        .meta-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .meta-row {
            margin-bottom: 5px;
        }
        .meta-label {
            font-size: 9pt;
            color: #64748b;
            width: 110px;
            display: inline-block;
        }
        .meta-value {
            font-size: 9.5pt;
            font-weight: 600;
            color: #0f172a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background: #f1f5f9;
            color: #475569;
            font-size: 8.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }
        .items-table td {
            padding: 11px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5pt;
            color: #334155;
        }
        .item-description {
            font-weight: 500;
            color: #0f172a;
        }
        .totals-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .totals-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 12px;
            font-size: 9.5pt;
        }
        .totals-label {
            text-align: right;
            color: #64748b;
        }
        .totals-value {
            text-align: right;
            font-weight: 600;
            color: #0f172a;
            width: 110px;
        }
        .grand-total td {
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
        }
        .grand-total .totals-value {
            color: #2563eb;
        }
        .notes-section {
            background: #f8fafc;
            border-left: 3px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 0 4px 4px 0;
            margin-top: 20px;
        }
        .notes-title {
            font-size: 8.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 4px;
        }
        .notes-text {
            font-size: 9pt;
            color: #64748b;
            white-space: pre-line;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-draft { background: #f1f5f9; color: #475569; }
        .status-sent { background: #ede9fe; color: #6d28d9; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-overdue { background: #ffe4e6; color: #be123c; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="vertical-align: top;">
                    <h1 class="brand-title">{{ \App\Models\Setting::get('business_name', 'FreelanceOS') }}</h1>
                    <div class="brand-subtitle">{{ \App\Models\Setting::get('business_subtitle', 'Software Engineering & Consulting') }}</div>
                    @if($reg = \App\Models\Setting::get('business_registration'))
                        <div style="font-size: 8pt; color: #94a3b8; margin-top: 2px;">{{ $reg }}</div>
                    @endif
                    @if($email = \App\Models\Setting::get('business_email'))
                        <div style="font-size: 8.5pt; color: #64748b; margin-top: 2px;">{{ $email }}</div>
                    @endif
                </td>
                <td style="vertical-align: top; text-align: right;">
                    <h2 class="invoice-title">INVOICE</h2>
                    <div class="invoice-meta">#{{ $invoice->invoice_number }}</div>
                    <div style="margin-top: 6px;">
                        <span class="status-badge status-{{ $invoice->status->value ?? 'draft' }}">
                            {{ strtoupper($invoice->status->value ?? 'DRAFT') }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-boxes">
        <table>
            <tr>
                <td class="meta-box" style="padding-right: 20px;">
                    <div class="box-title">Billed To</div>
                    <div class="client-name">{{ $invoice->client->name ?? 'Client' }}</div>
                    @if(!empty($invoice->client->contact_name))
                        <div style="font-size: 9.5pt; color: #475569; margin-bottom: 2px;">Attn: {{ $invoice->client->contact_name }}</div>
                    @endif
                    @if(!empty($invoice->client->email))
                        <div style="font-size: 9.5pt; color: #64748b; margin-bottom: 4px;">{{ $invoice->client->email }}</div>
                    @endif
                    @if(!empty($invoice->client->address))
                        <div class="address-text">{{ $invoice->client->address }}</div>
                    @endif
                </td>
                <td class="meta-box" style="padding-left: 20px;">
                    <div class="meta-details">
                        <div class="meta-row">
                            <span class="meta-label">Invoice Number:</span>
                            <span class="meta-value">{{ $invoice->invoice_number }}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Issue Date:</span>
                            <span class="meta-value">{{ $invoice->issue_date->format('M d, Y') }}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Due Date:</span>
                            <span class="meta-value">{{ $invoice->due_date->format('M d, Y') }}</span>
                        </div>
                        @if($invoice->project)
                        <div class="meta-row">
                            <span class="meta-label">Project:</span>
                            <span class="meta-value">{{ $invoice->project->name }}</span>
                        </div>
                        @endif
                        <div class="meta-row">
                            <span class="meta-label">Currency:</span>
                            <span class="meta-value">{{ $invoice->currency }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 55%;">Description</th>
                <th class="text-right" style="width: 15%;">Hours</th>
                <th class="text-right" style="width: 15%;">Rate</th>
                <th class="text-right" style="width: 15%;">Amount ({{ $invoice->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
            <tr>
                <td class="item-description">{{ $item->description }}</td>
                <td class="text-right">{{ number_format($item->quantity_hours, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_rate, 2) }}</td>
                <td class="text-right" style="font-weight: 600;">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">No billable line items attached.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="totals-label">Subtotal:</td>
                <td class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if((float) $invoice->tax_rate > 0)
            <tr>
                <td class="totals-label">Tax ({{ number_format($invoice->tax_rate, 1) }}%):</td>
                <td class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="totals-label">Total Due:</td>
                <td class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if(!empty($invoice->notes) || \App\Models\Setting::get('bank_name'))
    <div class="notes-section">
        <div class="notes-title">Payment Instructions & Remittance</div>
        @if(!empty($invoice->notes))
            <div class="notes-text" style="margin-bottom: 6px;">{{ $invoice->notes }}</div>
        @endif
        @if($bank = \App\Models\Setting::get('bank_name'))
            <div style="font-size: 8.5pt; color: #334155; margin-top: 6px; border-top: 1px dashed #cbd5e1; padding-top: 6px;">
                <strong>Bank:</strong> {{ $bank }} &nbsp;|&nbsp;
                <strong>Account:</strong> {{ \App\Models\Setting::get('bank_account_number') }} ({{ \App\Models\Setting::get('bank_account_name') }})
                @if($swift = \App\Models\Setting::get('bank_swift_duitnow'))
                    <br><span style="color: #64748b;">{{ $swift }}</span>
                @endif
            </div>
        @endif
    </div>
    @endif
</body>
</html>
