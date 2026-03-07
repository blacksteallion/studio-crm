<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $payments;

    public function __construct($payments)
    {
        $this->payments = $payments;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->payments;
    }

    /**
    * Define the headers for the Excel file
    */
    public function headings(): array
    {
        return [
            'Payment ID',
            'Transaction Date',
            'Invoice Number',
            'Customer Name',
            'Payment Method',
            'Reference Number',
            'Amount (₹)',
            'Notes'
        ];
    }

    /**
    * Map the data for each row
    */
    public function map($payment): array
    {
        return [
            'PAY-' . $payment->id,
            $payment->transaction_date ? $payment->transaction_date->format('d M, Y') : '-',
            $payment->order->invoice_number ?? 'Deleted Invoice',
            $payment->order->customer->name ?? 'Deleted Customer',
            $payment->payment_method,
            $payment->reference_number ?? '-',
            number_format($payment->amount, 2, '.', ''),
            $payment->notes ?? '-'
        ];
    }
}