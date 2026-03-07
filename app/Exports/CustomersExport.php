<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
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
            'Customer ID',
            'Full Name',
            'Business Name',
            'Mobile',
            'Email',
            'City',
            'State',
            'Country',
            'Status',
            'Total Inquiries',
            'Total Bookings',
            'Total Orders',
            'Added On'
        ];
    }

    /**
    * Map the data for each row
    */
    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->name,
            $customer->business_name ?? '-',
            $customer->mobile,
            $customer->email ?? '-',
            $customer->city ?? '-',
            $customer->state ?? '-',
            $customer->country ?? '-',
            $customer->status ? 'Active' : 'Inactive',
            $customer->inquiries_count ?? 0,
            $customer->bookings_count ?? 0,
            $customer->orders_count ?? 0,
            $customer->created_at ? $customer->created_at->format('d M, Y') : '-'
        ];
    }
}