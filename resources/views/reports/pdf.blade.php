<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        
        .header { width: 100%; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { max-height: 60px; }
        .company-info { text-align: right; float: right; }
        .title { font-size: 24px; font-weight: bold; color: #2563eb; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #666; }
        .location-title { font-size: 14px; font-weight: bold; color: #dc2626; margin-top: 5px; }
        
        .summary-box { width: 100%; margin-bottom: 30px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; }
        .summary-table { width: 100%; }
        .summary-item { text-align: center; width: 33%; }
        .summary-label { font-size: 10px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        .summary-value { font-size: 18px; font-weight: bold; margin-top: 5px; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-blue { color: #2563eb; }

        table.ledger { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.ledger th { background: #f1f5f9; padding: 10px; text-align: left; font-weight: bold; font-size: 10px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; }
        table.ledger td { padding: 10px; border-bottom: 1px solid #f1f5f9; }
        table.ledger tr:nth-child(even) { background-color: #fafafa; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    <div class="header">
        <div style="float:left;">
            @if($company['logo'])
                <img src="{{ public_path('storage/' . $company['logo']) }}" class="logo">
            @else
                <h2 style="margin:0;">{{ $company['name'] }}</h2>
            @endif
        </div>
        <div class="company-info">
            <strong>{{ $company['name'] }}</strong><br>
            {{ $company['address'] }}<br>
            {{ $company['phone'] }}
        </div>
        <div style="clear:both;"></div>
    </div>

    <div style="margin-bottom: 20px;">
        <div class="title">Financial Statement</div>
        <div class="subtitle">Period: {{ $start->format('d M, Y') }} - {{ $end->format('d M, Y') }}</div>
        <div class="location-title">Location: {{ $locationName }}</div>
    </div>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td class="summary-item">
                    <div class="summary-label">Total Revenue</div>
                    <div class="summary-value text-green">₹{{ number_format($totalIncome, 2) }}</div>
                </td>
                <td class="summary-item" style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                    <div class="summary-label">Total Expenses</div>
                    <div class="summary-value text-red">₹{{ number_format($totalExpense, 2) }}</div>
                </td>
                <td class="summary-item">
                    <div class="summary-label">Net Profit</div>
                    <div class="summary-value {{ $netProfit >= 0 ? 'text-blue' : 'text-red' }}">
                        ₹{{ number_format($netProfit, 2) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <h3 style="color: #334155; margin-bottom: 10px; font-size: 14px;">Transaction Detail</h3>
    <table class="ledger">
        <thead>
            <tr>
                <th width="15%">Date</th>
                <th width="15%">Ref ID</th>
                <th>Description / Party</th>
                <th width="15%">Category</th>
                <th width="15%" style="text-align: right;">Income</th>
                <th width="15%" style="text-align: right;">Expense</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledger as $row)
            <tr>
                <td>{{ $row->date->format('d-m-Y') }}</td>
                <td><span style="background: #f1f5f9; padding: 2px 5px; border-radius: 3px; font-size: 10px;">{{ $row->ref }}</span></td>
                <td>
                    <strong>{{ $row->desc }}</strong>
                    @if(session('active_location_id') === 'all')
                        <div style="font-size: 10px; color: #dc2626; margin-top: 3px;">{{ $row->location }}</div>
                    @endif
                </td>
                <td>{{ $row->category }}</td>
                <td style="text-align: right; color: #16a34a;">
                    @if($row->credit > 0) +{{ number_format($row->credit, 2) }} @endif
                </td>
                <td style="text-align: right; color: #dc2626;">
                    @if($row->debit > 0) -{{ number_format($row->debit, 2) }} @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #999;">No transactions found for this period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="4" style="text-align: right; padding: 15px 10px;">TOTALS</td>
                <td style="text-align: right; color: #16a34a; border-top: 2px solid #e2e8f0;">₹{{ number_format($totalIncome, 2) }}</td>
                <td style="text-align: right; color: #dc2626; border-top: 2px solid #e2e8f0;">₹{{ number_format($totalExpense, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }} | Page <span class="page-number"></span>
    </div>

</body>
</html>