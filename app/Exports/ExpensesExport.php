<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping
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
            'Reference Number',
            'Recorded On'
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
            number_format($expense->amount, 2, '.', ''),
            $expense->reference_no ?? '-',
            $expense->created_at ? $expense->created_at->format('d M, Y h:i A') : '-'
        ];
    }
}