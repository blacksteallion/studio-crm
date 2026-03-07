<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffExport implements FromCollection, WithHeadings, WithMapping
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
            'ID',
            'Full Name',
            'Email Address',
            'Mobile Number',
            'Role',
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
            $staff->id,
            $staff->name,
            $staff->email,
            $staff->mobile,
            ucfirst($staff->role),
            $staff->status ? 'Active' : 'Inactive',
            $staff->created_at->format('d M, Y')
        ];
    }
}