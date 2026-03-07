<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->invoice_number }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #334155; margin: 0; padding: 40px; }
        
        /* Layout Helpers */
        .w-full { width: 100%; }
        .w-half { width: 50%; }
        .text-right { text-align: right; }
        
        /* Colors */
        .text-slate-800 { color: #1e293b; }
        .text-gray-500 { color: #64748b; }
        .text-blue-600 { color: #2563eb; }
        .text-green-600 { color: #16a34a; }
        .text-red-600 { color: #dc2626; }
        .bg-gray-50 { background-color: #f8fafc; }
        
        /* HEADER STYLES - FIXED FONT WEIGHTS */
        .header-title { 
            font-size: 28px; 
            font-weight: bold; 
            color: #1e293b; 
            margin-bottom: 5px; 
            text-transform: uppercase; 
        }
        .invoice-number { font-size: 14px; color: #64748b; margin-bottom: 10px; }
        
        /* Status Badge */
        .status-badge { 
            display: inline-block; padding: 6px 15px; border-radius: 6px; 
            font-size: 11px; font-weight: bold; text-transform: uppercase; 
        }
        .status-Paid { background-color: #dcfce7; color: #166534; }
        .status-Unpaid { background-color: #fee2e2; color: #991b1b; }
        .status-Partially { background-color: #dbeafe; color: #1e40af; } 

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 5px; font-size: 10px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px 5px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        
        /* Sections */
        .header-section { margin-bottom: 40px; }
        .billing-section { margin-bottom: 40px; border-top: 1px solid #e2e8f0; padding-top: 30px; }
        .section-title { font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.05em; }
        
        .company-name { 
            font-size: 20px; 
            font-weight: bold; 
            color: #1e293b; 
            margin-bottom: 8px; 
        }
        .address-block { font-size: 13px; line-height: 1.6; color: #475569; }

        .meta-table td { padding: 4px 0; border: none; }
        .meta-label { color: #64748b; width: 100px; }
        .meta-value { color: #1e293b; font-weight: bold; text-align: right; }
        .meta-link { color: #2563eb; text-decoration: none; }

        .totals-box { width: 45%; margin-left: auto; margin-top: 20px; }
        .notes-box { margin-top: 40px; background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #f1f5f9; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 20px 40px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; background: white; }
    </style>
</head>
<body>

    <div class="header-section">
        <table class="w-full">
            <tr>
                <td style="border: none; vertical-align: top;">
                    <div class="header-title">Invoice</div>
                    <div class="invoice-number">{{ $order->invoice_number }}</div>
                    <div class="status-badge status-{{ explode(' ', $order->status)[0] }}">
                        {{ $order->status }}
                    </div>
                </td>
                
                <td style="border: none; vertical-align: top; text-align: right;">
                    <table style="width: auto; margin-left: auto;" class="meta-table">
                        <tr>
                            <td class="meta-label">Invoice Date:</td>
                            <td class="meta-value">{{ $order->invoice_date->format('d M, Y') }}</td>
                        </tr>
                        @if($order->due_date)
                        <tr>
                            <td class="meta-label">Due Date:</td>
                            <td class="meta-value">{{ $order->due_date->format('d M, Y') }}</td>
                        </tr>
                        @endif
                        @if($order->booking_id)
                        <tr>
                            <td class="meta-label">Reference:</td>
                            <td class="meta-value"><span class="meta-link">Booking #BKG-{{ $order->booking_id }}</span></td>
                        </tr>
                        @endif
                        <tr>
                            <td class="meta-label">Location:</td>
                            <td class="meta-value">{{ $order->location->name ?? 'Unassigned' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="billing-section">
        <table class="w-full">
            <tr>
                <td class="w-half" style="border: none; padding-right: 40px; vertical-align: top;">
                    <div class="section-title">Bill From</div>
                    
                    {{-- Studio Name --}}
                    <div class="company-name">
                        {{ $settings['company_name'] ?? 'TC Studio' }} 
                    </div>
                    
                    {{-- Studio Address --}}
                    <div class="address-block">
                        @if(!empty($settings['company_address']))
                            {!! nl2br(e($settings['company_address'])) !!}<br>
                        @else
                            101 Tech Park, SG Highway<br>Ahmedabad, Gujarat 380054<br>
                        @endif
                        
                        {{ $settings['company_email'] ?? 'contact@techcelerity.in' }}<br>
                        
                        @if(!empty($settings['company_phone']))
                            {{ $settings['company_phone'] }}
                        @endif
                    </div>
                </td>

                <td class="w-half" style="border: none; padding-left: 20px; vertical-align: top;">
                    <div class="section-title">Bill To</div>
                    
                    {{-- Business Name (Primary) or Name (Fallback) --}}
                    <div class="company-name">
                        {{ $order->customer->business_name ?: $order->customer->name }}
                    </div>

                    {{-- Customer Details --}}
                    <div class="address-block">
                        {{-- Address 1 --}}
                        @if(!empty($order->customer->address_1))
                            {{ $order->customer->address_1 }}<br>
                        @endif

                        {{-- Address 2, City, Zip --}}
                        @if(!empty($order->customer->address_2) || !empty($order->customer->city) || !empty($order->customer->zip))
                            {{ !empty($order->customer->address_2) ? $order->customer->address_2 . ', ' : '' }}
                            {{ $order->customer->city }} {{ $order->customer->zip }}<br>
                        @endif

                        {{-- Email --}}
                        @if(!empty($order->customer->email))
                            {{ $order->customer->email }}<br>
                        @endif

                        {{-- Mobile --}}
                        @if(!empty($order->customer->mobile))
                            {{ $order->customer->mobile }}<br>
                        @endif

                        {{-- GST --}}
                        @if(!empty($order->customer->gst_number))
                            GST: {{ $order->customer->gst_number }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="w-full" style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th style="width: 40%; padding-left: 0;">Description</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 15%;">Price</th>
                <th class="text-right" style="width: 15%;">GST</th>
                <th class="text-right" style="width: 20%; padding-right: 0;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td class="font-bold text-slate-800" style="padding-left: 0;">{{ $item->item_name }}</td>
                <td class="text-center text-gray-500">{{ (float)$item->quantity }}</td>
                <td class="text-right text-gray-500">₹{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right text-gray-500">
                    ₹{{ number_format($item->gst_amount, 2) }}
                    <div class="text-xs">({{ $item->gst_rate }}%)</div>
                </td>
                <td class="text-right font-bold text-slate-800" style="padding-right: 0;">₹{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-box">
        <table class="w-full">
            <tr class="totals-row">
                <td class="text-gray-500 text-sm" style="border: none;">Subtotal</td>
                <td class="text-right font-bold text-slate-800 text-sm" style="border: none;">₹{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->tax > 0)
            <tr class="totals-row">
                <td class="text-gray-500 text-sm" style="border: none;">Total Tax</td>
                <td class="text-right font-bold text-red-600 text-sm" style="border: none;">+ ₹{{ number_format($order->tax, 2) }}</td>
            </tr>
            @endif
            @if($order->discount > 0)
            <tr class="totals-row">
                <td class="text-gray-500 text-sm" style="border: none;">Discount</td>
                <td class="text-right font-bold text-green-600 text-sm" style="border: none;">- ₹{{ number_format($order->discount, 2) }}</td>
            </tr>
            @endif
            <tr class="totals-row">
                <td class="text-slate-800 font-bold" style="border-top: 1px solid #e2e8f0; padding-top: 10px;">Grand Total</td>
                <td class="text-right font-bold text-blue-600" style="border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 16px;">₹{{ number_format($order->total_amount, 2) }}</td>
            </tr>
            
            @if($order->total_amount > $order->balance_due)
            <tr class="totals-row">
                <td class="text-gray-500 text-sm" style="border: none; padding-top: 5px;">Amount Paid</td>
                <td class="text-right font-bold text-green-600 text-sm" style="border: none; padding-top: 5px;">- ₹{{ number_format($order->total_amount - $order->balance_due, 2) }}</td>
            </tr>
            @endif

            <tr class="totals-row">
                <td style="border-top: 1px dashed #e2e8f0; padding-top: 5px; font-weight: bold; color: {{ $order->balance_due > 0 ? '#dc2626' : '#16a34a' }};">
                    Balance Due
                </td>
                <td style="border-top: 1px dashed #e2e8f0; padding-top: 5px; text-align: right; font-weight: bold; color: {{ $order->balance_due > 0 ? '#dc2626' : '#16a34a' }};">
                    ₹{{ number_format($order->balance_due, 2) }}
                </td>
            </tr>
        </table>
    </div>

    @if(isset($settings['invoice_footer_text']) && !empty($settings['invoice_footer_text']))
        <div class="notes-box">
            <div class="section-title">Terms & Conditions</div>
            <div class="text-sm text-slate-800" style="line-height: 1.5;">{!! nl2br(e($settings['invoice_footer_text'])) !!}</div>
        </div>
    @endif

    @if($order->notes)
        <div class="notes-box" style="{{ isset($settings['invoice_footer_text']) ? 'margin-top: 15px;' : 'margin-top: 40px;' }}">
            <div class="section-title">Notes</div>
            <div class="text-sm text-slate-800" style="line-height: 1.5;">{{ $order->notes }}</div>
        </div>
    @endif

    @if($order->payments->isNotEmpty())
    <div style="margin-top: 30px;">
        <h3 class="section-title" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px;">Payment History</h3>
        <table class="w-full">
            <thead>
                <tr>
                    <th style="width: 25%; border: none;">Date</th>
                    <th style="width: 25%; border: none;">Method</th>
                    <th style="width: 25%; border: none;">Reference</th>
                    <th class="text-right" style="width: 25%; border: none;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->payments as $payment)
                <tr>
                    <td style="border-bottom: 1px solid #f1f5f9; color: #475569;">{{ $payment->transaction_date->format('d M, Y') }}</td>
                    <td style="border-bottom: 1px solid #f1f5f9; color: #475569;">{{ $payment->payment_method }}</td>
                    <td style="border-bottom: 1px solid #f1f5f9; font-family: monospace; color: #64748b;">{{ $payment->reference_number ?? '-' }}</td>
                    <td class="text-right font-bold text-green-600" style="border-bottom: 1px solid #f1f5f9;">₹{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Thank you for your business! &bull; {{ $settings['company_website'] ?? 'Generated on ' . date('d M, Y') }}
    </div>

</body>
</html>