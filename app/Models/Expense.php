<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', // <--- Added Location ID
        'title',
        'amount',
        'category',
        'expense_date',
        'receipt_path',
        'description',
        'reference_no'
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    // --- NEW: Location Relationship ---
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // --- STANDARD CATEGORIES CONFIGURATION ---
    public static function categories()
    {
        return [
            'Operational' => [
                'Rent & Utilities',
                'Internet & Phone',
                'Software Subscriptions',
                'Office Supplies',
                'Professional Fees (Legal/Audit)',
            ],
            'Production' => [
                'Equipment Rental',
                'Props & Sets',
                'Printing & Materials',
                'Outsourced Labor/Freelancers',
            ],
            'Travel & Staff' => [
                'Staff Salary',
                'Travel & Transport',
                'Meals & Entertainment',
                'Fuel/Petrol',
            ],
            'Marketing' => [
                'Facebook/Google Ads',
                'Marketing Materials',
                'Website Hosting',
            ],
            'Financial' => [
                'Bank Charges',
                'Tax Payments',
                'Loan Repayment',
            ]
        ];
    }
}