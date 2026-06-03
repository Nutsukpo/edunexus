<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;

class FeeCategoryController extends Controller
{
    public function index()
    {
        $categories = FeeCategory::latest()->get();

        return view(
            'fee-categories.index',
            compact('categories')
        );
    }

    public function create()
    {
        return view('fee-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        FeeCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('fee-categories.index')
            ->with('success', 'Fee Category Created Successfully');
    }

    public function edit(FeeCategory $feeCategory)
    {
        return view(
            'fee-categories.edit',
            compact('feeCategory')
        );
    }

    public function update(Request $request, FeeCategory $feeCategory)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $feeCategory->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('fee-categories.index')
            ->with('success', 'Fee Category Updated Successfully');
    }

    public function destroy(FeeCategory $feeCategory)
    {
        $feeCategory->delete();

        return back()
            ->with('success', 'Fee Category Deleted Successfully');
    }
}