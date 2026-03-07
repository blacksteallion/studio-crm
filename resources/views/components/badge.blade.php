@props(['status'])

@php
    $status = ucfirst($status);
    
    // Define Color Maps based on your Business Logic
    $colors = [
        'green' => ['Paid', 'Completed', 'Won', 'Converted', 'Slot Reserved', 'Active', 'Qualified'],
        'red'   => ['Unpaid', 'Cancelled', 'Lost', 'High', 'Urgent'],
        'blue'  => ['New', 'Scheduled', 'Booked', 'Low'],
        'yellow'=> ['Pending', 'Partially Paid', 'In Progress', 'Medium'],
        'gray'  => ['Draft', 'Closed', 'Archived', 'Unknown'],
    ];

    // Default to Gray
    $theme = 'bg-gray-100 text-gray-600 border-gray-200';

    // Find the matching color
    foreach ($colors as $color => $statuses) {
        if (in_array($status, $statuses)) {
            switch ($color) {
                case 'green':
                    $theme = 'bg-green-100 text-green-700 border-green-200';
                    break;
                case 'red':
                    $theme = 'bg-red-100 text-red-700 border-red-200';
                    break;
                case 'blue':
                    $theme = 'bg-blue-100 text-blue-700 border-blue-200';
                    break;
                case 'yellow':
                    $theme = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                    break;
            }
            break;
        }
    }
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase border $theme"]) }}>
    {{ $status }}
</span>