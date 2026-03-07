<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->orders;
    }

    /**
    * Define the headers for the Excel file
    */
    public function headings(): array
    {
        return [
            'Invoice Number',
            'Booking ID',
            'Customer Name',
            'Business Name',
            'Invoice Date',
            'Due Date',
            'Subtotal (₹)',
            'Tax (₹)',
            'Discount (₹)',
            'Total Amount (₹)',
            'Status',
            'Notes'
        ];
    }

    /**
    * Map the data for each row
    */
    public function map($order): array
    {
        return [
            $order->invoice_number,
            $order->booking_id ? 'BKG-' . $order->booking_id : '-',
            $order->customer->name ?? '-',
            $order->customer->business_name ?? '-',
            $order->invoice_date ? $order->invoice_date->format('d M, Y') : '-',
            $order->due_date ? $order->due_date->format('d M, Y') : '-',
            number_format($order->subtotal, 2, '.', ''),
            number_format($order->tax, 2, '.', ''),
            number_format($order->discount, 2, '.', ''),
            number_format($order->total_amount, 2, '.', ''),
            $order->status,
            $order->notes ?? '-'
        ];
    }
}