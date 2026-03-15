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

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, WithColumnWidths
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function headings(): array
    {
        return [
            'Booking ID',
            'Customer Name',
            'Mobile Number',
            'Email Address',
            'Business / Company Name',
            'Booking Date',
            'Start Time',
            'End Time',
            'Total Hours',
            'Studio Location',
            'Assign To Staff',
            'Status',
            'Inquiry Source',
            'Notes',
            'Requested Products / Services', // Column O
            'Total Estimate (₹)'             // Column P
        ];
    }

    public function map($booking): array
    {
        // Format items with bullet points and actual line breaks
        $itemsList = $booking->items->map(function($item) {
            return "• {$item->item_name} (Qty: {$item->quantity} | ₹" . number_format($item->total, 2) . ")";
        })->implode("\n");
        
        // Extract the Grand Total to its own numeric column
        $grandTotal = $booking->items->sum('total');

        // Calculate total hours
        $totalHours = 0;
        if ($booking->start_time && $booking->end_time) {
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);
            if ($end->lessThan($start)) {
                $end->addDay(); // Handle overnight bookings
            }
            $totalHours = abs($end->diffInMinutes($start)) / 60;
        }

        return [
            'BKG-' . $booking->id,
            $booking->customer->name ?? 'N/A',
            (string) ($booking->customer->mobile ?? '-'), 
            $booking->customer->email ?? '-',
            $booking->customer->business_name ?? '-',
            $booking->booking_date ? Carbon::parse($booking->booking_date)->format('d M, Y') : '-',
            $booking->start_time ? Carbon::parse($booking->start_time)->format('h:i A') : '-',
            $booking->end_time ? Carbon::parse($booking->end_time)->format('h:i A') : '-',
            $totalHours,
            $booking->location->name ?? '-',
            $booking->assignedStaff->name ?? 'Unassigned',
            $booking->status,
            $booking->inquiry->leadSource->name ?? 'Direct',
            $booking->notes ?? '-',
            $itemsList ?: 'No items added',
            $grandTotal > 0 ? $grandTotal : 0
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // Protect Mobile Number
            'P' => NumberFormat::FORMAT_NUMBER_00, // Format Total Estimate as a clean number
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Force Column O (Products) to wrap text vertically
            'O' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],
            // Align the rest of the row to the top
            1 => ['font' => ['bold' => true]], 
            'A:P' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'O' => 65, // Set to a nice wide size for the products list
        ];
    }
}