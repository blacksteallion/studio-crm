<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class InquiriesExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, WithColumnWidths
{
    protected $inquiries;

    public function __construct($inquiries)
    {
        $this->inquiries = $inquiries;
    }

    public function collection()
    {
        return $this->inquiries;
    }

    public function headings(): array
    {
        return [
            'Inquiry ID',
            'Customer Name',
            'Mobile Number',
            'Email Address',
            'Business / Company Name',
            'Inquiry Source',
            'Customer Budget',
            'Primary Date',
            'Alternate Date',
            'From Time',
            'To Time',
            'Total Hours',
            'Studio Location',
            'Assign To Staff',
            'Status',
            'Follow Up Date',
            'Added On',
            'Requested Products / Services', // Column R
            'Total Estimate (₹)'             // Column S
        ];
    }

    public function map($inquiry): array
    {
        // Format items with bullet points and actual line breaks
        $itemsList = $inquiry->items->map(function($item) {
            return "• {$item->item_name} (Qty: {$item->quantity} | ₹" . number_format($item->total, 2) . ")";
        })->implode("\n");
        
        // Extract the Grand Total to its own numeric column
        $grandTotal = $inquiry->items->sum('total');

        return [
            'INQ-' . $inquiry->id,
            $inquiry->customer->name ?? 'N/A',
            (string) ($inquiry->customer->mobile ?? '-'), 
            $inquiry->customer->email ?? '-',
            $inquiry->business_name ?? $inquiry->customer->business_name ?? '-',
            $inquiry->leadSource->name ?? 'Direct',
            $inquiry->budget ?? '0',
            $inquiry->primary_date ? Carbon::parse($inquiry->primary_date)->format('d M, Y') : '-',
            $inquiry->alternate_date ? Carbon::parse($inquiry->alternate_date)->format('d M, Y') : '-',
            $inquiry->from_time ? Carbon::parse($inquiry->from_time)->format('h:i A') : '-',
            $inquiry->to_time ? Carbon::parse($inquiry->to_time)->format('h:i A') : '-',
            $inquiry->total_hours ?? '0',
            $inquiry->location->name ?? '-',
            $inquiry->assignedStaff->name ?? 'Unassigned',
            $inquiry->status ?? 'New',
            $inquiry->follow_up_date ? Carbon::parse($inquiry->follow_up_date)->format('d M, Y') : '-',
            $inquiry->created_at ? $inquiry->created_at->format('d M, Y') : '-',
            $itemsList ?: 'No items added',
            $grandTotal > 0 ? $grandTotal : 0
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // Protect Mobile Number
            'S' => NumberFormat::FORMAT_NUMBER_00, // Format Total Estimate as a clean number (e.g. 1500.00)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Force Column R (Products) to wrap text vertically instead of spilling over horizontally
            'R' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],
            // Align the rest of the row to the top so it looks clean
            1 => ['font' => ['bold' => true]], 
            'A:S' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],
        ];
    }

    /**
     * Set explicit column widths so the products don't wrap into multiple lines
     */
    public function columnWidths(): array
    {
        return [
            'R' => 65, // Set to a nice wide size for the products list
        ];
    }
}