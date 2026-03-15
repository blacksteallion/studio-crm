<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    protected $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->expenses;
    }

    /**
    * Define the headers for the Excel file
    */
    public function headings(): array
    {
        return [
            'Expense ID',
            'Date',
            'Title / Payee',
            'Category',
            'Amount (₹)',
            'Studio Location',
            'Notes'
        ];
    }

    /**
    * Map the data for each row
    */
    public function map($expense): array
    {
        return [
            'EXP-' . $expense->id,
            $expense->expense_date ? $expense->expense_date->format('d M, Y') : '-',
            $expense->title,
            $expense->category,
            $expense->amount ?? 0,
            $expense->location->name ?? '-',
            $expense->notes ?? '-'
        ];
    }

    /**
     * Format specific columns
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_00,  // Clean formatting for Amount
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