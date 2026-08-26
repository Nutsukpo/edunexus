<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Display a listing of assets
     */
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'creator', 'currentAssignment.assignee']);

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by condition
        if ($request->has('condition') && $request->condition) {
            $query->where('condition', $request->condition);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('asset_code', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $assets = $query->paginate(15);

        // Statistics
        $stats = [
            'total' => Asset::count(),
            'available' => Asset::where('status', 'available')->count(),
            'assigned' => Asset::where('status', 'assigned')->count(),
            'maintenance' => Asset::where('status', 'maintenance')->count(),
            'damaged' => Asset::where('status', 'damaged')->count(),
            'disposed' => Asset::where('status', 'disposed')->count(),
            'total_value' => Asset::sum('purchase_price') ?? 0,
            'under_warranty' => Asset::where('warranty_expiry', '>=', now())->count(),
        ];

        // Filter data
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('assets.index', compact('assets', 'stats', 'categories', 'users'));
    }

    /**
     * Show the form for creating a new asset
     */
    public function create()
    {
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        return view('assets.create', compact('categories', 'users'));
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
            'model' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:available,assigned,maintenance,damaged,disposed',
            'condition' => 'required|in:new,good,fair,poor,damaged',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('assets/images', $filename, 'public');
                $data['image_path'] = $path;
            }

            // Handle document upload
            if ($request->hasFile('document')) {
                $document = $request->file('document');
                $filename = time() . '_' . Str::slug($request->name) . '.' . $document->getClientOriginalExtension();
                $path = $document->storeAs('assets/documents', $filename, 'public');
                $data['document_path'] = $path;
                $data['document_name'] = $document->getClientOriginalName();
            }

            $data['created_by'] = Auth::id();
            $data['current_value'] = $data['current_value'] ?? $data['purchase_price'];

            $asset = Asset::create($data);

            Log::info('Asset created', [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'created_by' => Auth::id()
            ]);

            return redirect()
                ->route('assets.index')
                ->with('success', 'Asset created successfully! Asset Code: ' . $asset->asset_code);

        } catch (\Exception $e) {
            Log::error('Error creating asset: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create asset: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified asset
     */
    public function show($id)
    {
        $asset = Asset::with([
            'category',
            'creator',
            'currentAssignment.assignee',
            'assignments.assignee',
            
        ])->findOrFail($id);

        $assignments = $asset->assignments()->with('assignee')->orderBy('assigned_date', 'desc')->paginate(10);
        

        return view('assets.show', compact('asset', 'assignments', ));
    }

    /**
     * Show the form for editing the specified asset
     */
    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('assets.edit', compact('asset', 'categories', 'users'));
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number,' . $asset->id,
            'model' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:available,assigned,maintenance,damaged,disposed',
            'condition' => 'required|in:new,good,fair,poor,damaged',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',
            'remove_image' => 'nullable|boolean',
            'remove_document' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($asset->image_path && Storage::disk('public')->exists($asset->image_path)) {
                    Storage::disk('public')->delete($asset->image_path);
                }
                
                $image = $request->file('image');
                $filename = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('assets/images', $filename, 'public');
                $data['image_path'] = $path;
            }

            // Remove image
            if ($request->has('remove_image') && $request->remove_image == 1) {
                if ($asset->image_path && Storage::disk('public')->exists($asset->image_path)) {
                    Storage::disk('public')->delete($asset->image_path);
                }
                $data['image_path'] = null;
            }

            // Handle document upload
            if ($request->hasFile('document')) {
                // Delete old document
                if ($asset->document_path && Storage::disk('public')->exists($asset->document_path)) {
                    Storage::disk('public')->delete($asset->document_path);
                }
                
                $document = $request->file('document');
                $filename = time() . '_' . Str::slug($request->name) . '.' . $document->getClientOriginalExtension();
                $path = $document->storeAs('assets/documents', $filename, 'public');
                $data['document_path'] = $path;
                $data['document_name'] = $document->getClientOriginalName();
            }

            // Remove document
            if ($request->has('remove_document') && $request->remove_document == 1) {
                if ($asset->document_path && Storage::disk('public')->exists($asset->document_path)) {
                    Storage::disk('public')->delete($asset->document_path);
                }
                $data['document_path'] = null;
                $data['document_name'] = null;
            }

            $data['updated_by'] = Auth::id();

            $asset->update($data);

            Log::info('Asset updated', [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'updated_by' => Auth::id()
            ]);

            return redirect()
                ->route('assets.show', $asset->id)
                ->with('success', 'Asset updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating asset: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update asset: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified asset
     */
    public function destroy($id)
    {
        try {
            $asset = Asset::findOrFail($id);

            // Check if asset is assigned
            if ($asset->status === 'assigned') {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete an assigned asset. Please return it first.');
            }

            // Delete files
            if ($asset->image_path && Storage::disk('public')->exists($asset->image_path)) {
                Storage::disk('public')->delete($asset->image_path);
            }
            if ($asset->document_path && Storage::disk('public')->exists($asset->document_path)) {
                Storage::disk('public')->delete($asset->document_path);
            }

            // Delete related records
            $asset->assignments()->delete();
            $asset->maintenance()->delete();
            $asset->delete();

            Log::info('Asset deleted', [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'deleted_by' => Auth::id()
            ]);

            return redirect()
                ->route('assets.index')
                ->with('success', 'Asset deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting asset: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to delete asset: ' . $e->getMessage());
        }
    }

    /**
     * Assign asset to user
     */
    public function assign(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        if (!$asset->isAvailable()) {
            return redirect()
                ->back()
                ->with('error', 'Asset is not available for assignment.');
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
            'expected_return_date' => 'nullable|date|after:today',
            'assignment_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $assignment = $asset->assignTo(
                $request->assigned_to,
                $request->assignment_notes
            );

            if ($request->expected_return_date) {
                $assignment->update(['expected_return_date' => $request->expected_return_date]);
            }

            Log::info('Asset assigned', [
                'asset_id' => $asset->id,
                'assigned_to' => $request->assigned_to,
                'assigned_by' => Auth::id()
            ]);

            return redirect()
                ->route('assets.show', $asset->id)
                ->with('success', 'Asset assigned successfully!');

        } catch (\Exception $e) {
            Log::error('Error assigning asset: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to assign asset: ' . $e->getMessage());
        }
    }

    /**
     * Return assigned asset
     */
    public function returnAsset($id)
    {
        try {
            $asset = Asset::findOrFail($id);

            if (!$asset->isAssigned()) {
                return redirect()
                    ->back()
                    ->with('error', 'Asset is not currently assigned.');
            }

            $asset->returnAsset();

            Log::info('Asset returned', [
                'asset_id' => $asset->id,
                'returned_by' => Auth::id()
            ]);

            return redirect()
                ->route('assets.show', $asset->id)
                ->with('success', 'Asset returned successfully!');

        } catch (\Exception $e) {
            Log::error('Error returning asset: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to return asset: ' . $e->getMessage());
        }
    }

    /**
     * Download asset document
     */
    public function downloadDocument($id)
    {
        $asset = Asset::findOrFail($id);

        if (!$asset->document_path || !Storage::disk('public')->exists($asset->document_path)) {
            abort(404, 'Document not found.');
        }

        $filename = $asset->document_name ?? basename($asset->document_path);

        return Storage::disk('public')->download($asset->document_path, $filename);
    }

    /**
     * Download asset image
     */
    public function downloadImage($id)
    {
        $asset = Asset::findOrFail($id);

        if (!$asset->image_path || !Storage::disk('public')->exists($asset->image_path)) {
            abort(404, 'Image not found.');
        }

        $filename = basename($asset->image_path);

        return Storage::disk('public')->download($asset->image_path, $filename);
    }
}