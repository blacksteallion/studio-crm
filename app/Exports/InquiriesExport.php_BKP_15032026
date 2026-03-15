<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InquiriesExport implements FromCollection, WithHeadings, WithMapping
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
            'Business Name',
            'Mobile',
            'Status',
            'Assigned Staff',
            'Booking Date',
            'Start Time',
            'End Time',
            'Inquiry Source',
            'Notes'
        ];
    }

    public function map($booking): array
    {
        return [
            'BKG-' . $booking->id,
            $booking->customer->name ?? 'N/A',
            $booking->customer->business_name ?? 'Individual',
            $booking->customer->mobile ?? 'N/A',
            $booking->status,
            $booking->assignedStaff->name ?? 'Unassigned',
            $booking->booking_date ? $booking->booking_date->format('d M, Y') : '-',
            $booking->start_time ? $booking->start_time->format('h:i A') : '-',
            $booking->end_time ? $booking->end_time->format('h:i A') : '-',
            $booking->inquiry->leadSource->name ?? 'Direct',
            $booking->notes ?? ''
        ];
    }
}