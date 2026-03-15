<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
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
            'Invoice Date',
            'Due Date',
            'Booking ID',
            'Customer Name',
            'Mobile Number',
            'Business / Company Name',
            'Subtotal (Excl. Tax)',
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
            $order->invoice_date ? $order->invoice_date->format('d M, Y') : '-',
            $order->due_date ? $order->due_date->format('d M, Y') : '-',
            $order->booking_id ? 'BKG-' . $order->booking_id : '-',
            $order->customer->name ?? '-',
            (string) ($order->customer->mobile ?? '-'), // Cast to string to prevent Excel math conversions
            $order->customer->business_name ?? '-',
            $order->subtotal ?? 0,
            $order->tax ?? 0,
            $order->discount ?? 0,
            $order->total_amount ?? 0,
            $order->status,
            $order->notes ?? '-'
        ];
    }

    /**
     * Format specific columns
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT,       // Protect Mobile Number from scientific notation
            'H' => NumberFormat::FORMAT_NUMBER_00,  // Clean formatting for Subtotal
            'I' => NumberFormat::FORMAT_NUMBER_00,  // Clean formatting for Tax
            'J' => NumberFormat::FORMAT_NUMBER_00,  // Clean formatting for Discount
            'K' => NumberFormat::FORMAT_NUMBER_00,  // Clean formatting for Total Amount
        ];
    }

    /**
     * Apply styles to the sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Make the header row bold
            1 => ['font' => ['bold' => true]], 
        ];
    }
}