<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->customers;
    }

    /**
    * Define the headers for the Excel file
    */
    public function headings(): array
    {
        return [
            'Business / Company Name',
            'Contact Person Name',
            'Mobile Number',
            'Email Address',
            'Website URL',
            'GST / Tax Number',
            'Remarks',
            'Status',
            'Total Inquiries',
            'Total Bookings',
            'Total Orders',
            'Address Line 1',
            'Address Line 2',
            'City',
            'Pin Code',
            'State',
            'Country',
            'Added On'
        ];
    }

    /**
    * Map the data for each row
    */
    public function map($customer): array
    {
        return [
            $customer->business_name ?? '-',
            $customer->name,
            (string) $customer->mobile, // Cast to string to prevent Excel math conversions
            $customer->email ?? '-',
            $customer->website ?? '-',
            $customer->gst_number ?? '-',
            $customer->remarks ?? '-',
            $customer->status ? 'Active' : 'Inactive',
            $customer->inquiries_count ?? 0,
            $customer->bookings_count ?? 0,
            $customer->orders_count ?? 0,
            $customer->address_line1 ?? '-',
            $customer->address_line2 ?? '-',
            $customer->city ?? '-',
            (string) $customer->pincode, // Cast to string to prevent leading zeros from disappearing
            $customer->state ?? '-',
            $customer->country ?? '-',
            $customer->created_at ? $customer->created_at->format('d M, Y') : '-'
        ];
    }

    /**
     * Format specific columns (e.g., forcing mobile numbers and pin codes to be pure text)
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            // Column C is Mobile Number, Column O is Pin Code. 
            // FORMAT_TEXT prevents Excel from converting to scientific notation or stripping leading zeros.
            'C' => NumberFormat::FORMAT_TEXT,
            'O' => NumberFormat::FORMAT_TEXT,
        ];
    }
}