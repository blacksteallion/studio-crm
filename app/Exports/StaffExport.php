<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StaffExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $staffMembers;

    public function __construct($staffMembers)
    {
        $this->staffMembers = $staffMembers;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->staffMembers;
    }

    /**
    * Define the headers for the Excel file
    */
    public function headings(): array
    {
        return [
            'Full Name',
            'Mobile Number',
            'Email Address (Login ID)',
            'Role',
            'Assigned Locations',
            'Account Status',
            'Joined Date'
        ];
    }

    /**
    * Map the data for each row
    */
    public function map($staff): array
    {
        return [
            $staff->name,
            (string) $staff->mobile, // Cast to string to help prevent Excel math conversions
            $staff->email,
            ucfirst($staff->role),
            $staff->locations->pluck('name')->implode(', '), // Plucks all assigned location names and comma-separates them
            $staff->status ? 'Active' : 'Inactive',
            $staff->created_at->format('d M, Y')
        ];
    }

    /**
     * Format specific columns (e.g., forcing mobile numbers to be pure text)
     * * @return array
     */
    public function columnFormats(): array
    {
        return [
            // Column B is the Mobile Number. FORMAT_TEXT prevents Excel from converting it to scientific notation like 1E+10
            'B' => NumberFormat::FORMAT_TEXT, 
        ];
    }
}