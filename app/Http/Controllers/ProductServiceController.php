<?php

namespace App\Http\Controllers;

use App\Models\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductServiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view products', only: ['index']),
            new Middleware('can:create products', only: ['create', 'store']),
            new Middleware('can:edit products', only: ['edit', 'update']),
            new Middleware('can:delete products', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = ProductService::query();

        // 1. General Auto-Search (Name, Type, Pricing, Price)
        // Triggers when typing in the main search bar
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('pricing_model', 'like', "%{$search}%")
                  ->orWhere('price', 'like', "%{$search}%");
            });
        }

        // 2. Advanced Search Filters
        // Triggers when using the slide-down filter panel
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('pricing_model')) {
            $query->where('pricing_model', $request->pricing_model);
        }
        if ($request->filled('price')) {
            // Searches for items with price less than or equal to input
            $query->where('price', '<=', $request->price);
        }
        if ($request->filled('gst_rate')) {
            $query->where('gst_rate', $request->gst_rate);
        }

        // Order by name and paginate
        $items = $query->orderBy('name')->paginate(20);
        
        return view('product_services.index', compact('items'));
    }

    public function create()
    {
        return view('product_services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Service,Product',
            'pricing_model' => 'required|in:Hourly,Fixed,Per Unit',
            'price' => 'required|numeric|min:0',
            'gst_rate' => 'required|numeric|min:0|max:28', // Validates GST input
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        ProductService::create($request->all());

        return redirect()->route('product_services.index')
                         ->with('success', 'Item created successfully.');
    }

    public function edit(ProductService $productService)
    {
        return view('product_services.edit', compact('productService'));
    }

    public function update(Request $request, ProductService $productService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Service,Product',
            'pricing_model' => 'required|in:Hourly,Fixed,Per Unit',
            'price' => 'required|numeric|min:0',
            'gst_rate' => 'required|numeric|min:0|max:28', // Validates GST input
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $productService->update($request->all());

        return redirect()->route('product_services.index')
                         ->with('success', 'Item updated successfully.');
    }

    public function destroy(ProductService $productService)
    {
        $productService->delete();
        return redirect()->route('product_services.index')
                         ->with('success', 'Item deleted successfully.');
    }
}