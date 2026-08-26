<?php
// app/Http/Controllers/FeeCategoryController.php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeeCategoryController extends Controller
{
    /**
     * Display a listing of fee categories.
     */
    public function index()
    {
        $categories = FeeCategory::orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);
        
        return view('admin.fees.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new fee category.
     */
    public function create()
    {
        return view('admin.fees.categories.create');
    }

    /**
     * Store a newly created fee category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:fee_categories,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category = FeeCategory::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('fee-categories.index')
            ->with('success', "Fee category '{$category->name}' created successfully.");
    }

    /**
     * Display the specified fee category.
     */
    public function show($id)
    {
        $category = FeeCategory::with('feeItems.feeStructure')
            ->findOrFail($id);
        
        return view('admin.fees.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified fee category.
     */
    public function edit($id)
    {
        $category = FeeCategory::findOrFail($id);
        return view('admin.fees.categories.edit', compact('category'));
    }

    /**
     * Update the specified fee category.
     */
    public function update(Request $request, $id)
    {
        $category = FeeCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:fee_categories,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('fee-categories.index')
            ->with('success', "Fee category '{$category->name}' updated successfully.");
    }

    /**
     * Remove the specified fee category.
     */
    public function destroy($id)
    {
        $category = FeeCategory::findOrFail($id);
        
        // Check if category is being used
        if ($category->feeItems()->count() > 0) {
            return back()->with('error', 
                "Cannot delete '{$category->name}' because it is being used by fee items.");
        }
        
        $category->delete();

        return redirect()->route('fee-categories.index')
            ->with('success', "Fee category '{$category->name}' deleted successfully.");
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus($id)
    {
        $category = FeeCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'activated' : 'deactivated';

        return redirect()->route('fee-categories.index')
            ->with('success', "Fee category '{$category->name}' {$status} successfully.");
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return back()->with('error', 'No categories selected for deletion.');
        }

        $categories = FeeCategory::whereIn('id', $ids)->get();
        $deletedCount = 0;
        $errorCount = 0;

        foreach ($categories as $category) {
            if ($category->feeItems()->count() > 0) {
                $errorCount++;
                continue;
            }
            $category->delete();
            $deletedCount++;
        }

        $message = "{$deletedCount} categories deleted successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} categories could not be deleted because they are in use.";
        }

        return back()->with('success', $message);
    }

    /**
     * API endpoint to get categories for dropdown.
     */
    public function getCategories(Request $request)
    {
        $search = $request->get('q');
        
        $categories = FeeCategory::where('is_active', true)
            ->when($search, function($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        return response()->json($categories);
    }
}