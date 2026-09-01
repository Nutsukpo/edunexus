<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements
     */
    public function index(Request $request)
    {
        $query = Announcement::with('creator');

        // Filter by type
        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        // Filter by audience
        if ($request->has('audience') && $request->audience != 'all') {
            $query->where('audience', $request->audience);
        }

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            if ($request->status === 'published') {
                $query->published();
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $announcements = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get counts for dashboard
        $counts = [
            'total' => Announcement::count(),
            'published' => Announcement::published()->count(),
            'drafts' => Announcement::where('is_published', false)->count(),
            'urgent' => Announcement::whereIn('priority', ['high', 'urgent'])->count(),
            
        ];

        // Get types for filter
        $types = Announcement::select('type')->distinct()->pluck('type');

        return view('Announcements.index', compact('announcements', 'counts', 'types'));
    }

    /**
     * Show the form for creating a new announcement
     */
    public function create()
    {
        return view('Announcements.create');
    }

    /**
     * Store a newly created announcement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,academic,event,urgent,exam',
            'audience' => 'required|in:all,students,staff,parents,teachers',
            'priority' => 'required|in:low,normal,high,urgent',
            'publish_date' => 'nullable|date|after_or_equal:today',
            'expiry_date' => 'nullable|date|after:publish_date',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('announcements', 'public');
                $validated['image'] = $path;
            }

            $validated['created_by'] = auth()->id();

            // Set default values for boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_featured'] = $request->has('is_featured');

            $announcement = Announcement::create($validated);

            Log::info('Announcement created', [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('announcements.index')
                ->with('success', 'Announcement created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating announcement: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to create announcement. Please try again.');
        }
    }

    /**
     * Display the specified announcement
     */
    public function show(Announcement $announcement)
    {
        return view('Announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement
     */
    public function edit(Announcement $announcement)
    {
        return view('Announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,academic,event,urgent,exam',
            'audience' => 'required|in:all,students,staff,parents,teachers',
            'priority' => 'required|in:low,normal,high,urgent',
            'publish_date' => 'nullable|date|after_or_equal:today',
            'expiry_date' => 'nullable|date|after:publish_date',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($announcement->image && Storage::exists('public/' . $announcement->image)) {
                    Storage::delete('public/' . $announcement->image);
                }
                $path = $request->file('image')->store('announcements', 'public');
                $validated['image'] = $path;
            }

            // Handle image removal
            if ($request->has('remove_image') && $request->remove_image == 1) {
                if ($announcement->image && Storage::exists('public/' . $announcement->image)) {
                    Storage::delete('public/' . $announcement->image);
                }
                $validated['image'] = null;
            }

            // Update boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_featured'] = $request->has('is_featured');

            $announcement->update($validated);

            Log::info('Announcement updated', [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'updated_by' => auth()->id(),
            ]);

            return redirect()
                ->route('Announcements.index')
                ->with('success', 'Announcement updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating announcement: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to update announcement. Please try again.');
        }
    }

    /**
     * Remove the specified announcement
     */
    public function destroy(Announcement $announcement)
    {
        try {
            // Delete image if exists
            if ($announcement->image && Storage::exists('public/' . $announcement->image)) {
                Storage::delete('public/' . $announcement->image);
            }

            $announcement->delete();

            Log::info('Announcement deleted', [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'deleted_by' => auth()->id(),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Announcement deleted successfully!'
                ]);
            }

            return redirect()
                ->route('announcements.index')
                ->with('success', 'Announcement deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting announcement: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete announcement.'
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete announcement.');
        }
    }

    /**
     * Toggle announcement status (publish/unpublish)
     */
    public function toggleStatus(Announcement $announcement)
    {
        try {
            // Check if announcement is expired
            if ($announcement->isExpired()) {
                return back()->with('error', 'Cannot publish an expired announcement.');
            }

            $announcement->update([
                'is_published' => !$announcement->is_published,
            ]);

            $status = $announcement->is_published ? 'published' : 'unpublished';

            Log::info("Announcement {$status}", [
                'id' => $announcement->id,
                'title' => $announcement->title,
            ]);

            return redirect()
                ->route('Announcements.index')
                ->with('success', "Announcement {$status} successfully!");

        } catch (\Exception $e) {
            Log::error('Error toggling announcement status: ' . $e->getMessage());
            return back()->with('error', 'Failed to toggle announcement status.');
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Announcement $announcement)
    {
        try {
            $announcement->update([
                'is_featured' => !$announcement->is_featured,
            ]);

            $status = $announcement->is_featured ? 'featured' : 'unfeatured';

            Log::info("Announcement {$status}", [
                'id' => $announcement->id,
                'title' => $announcement->title,
            ]);

            return redirect()
                ->route('Announcements.index')
                ->with('success', "Announcement {$status}!");

        } catch (\Exception $e) {
            Log::error('Error toggling featured status: ' . $e->getMessage());
            return back()->with('error', 'Failed to toggle featured status.');
        }
    }

    /**
     * Mark announcement as expired
     */
    public function expire(Announcement $announcement)
    {
        try {
            if ($announcement->isExpired()) {
                return redirect()
                    ->route('announcements.index')
                    ->with('info', 'Announcement is already expired.');
            }

            $announcement->update([
                'expiry_date' => now(),
                'is_published' => false,
            ]);

            Log::info('Announcement expired manually', [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'expired_by' => auth()->id(),
            ]);

            return redirect()
                ->route('Announcements.index')
                ->with('success', 'Announcement expired successfully!');

        } catch (\Exception $e) {
            Log::error('Error expiring announcement: ' . $e->getMessage());
            return back()->with('error', 'Failed to expire announcement.');
        }
    }

    /**
     * Bulk expire announcements
     */
    public function bulkExpire(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:announcements,id',
        ]);

        try {
            $announcements = Announcement::whereIn('id', $validated['ids'])->get();
            $count = 0;

            foreach ($announcements as $announcement) {
                if (!$announcement->isExpired()) {
                    $announcement->update([
                        'expiry_date' => now(),
                        'is_published' => false,
                    ]);
                    $count++;
                }
            }

            Log::info('Bulk expire announcements', [
                'count' => $count,
                'expired_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} announcement(s) expired successfully.",
                'expired' => $count,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk expire error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to expire announcements.',
            ], 500);
        }
    }

    /**
     * Display published announcements for public view
     */
    public function publicIndex(Request $request)
    {
        $query = Announcement::published();

        // Filter by type
        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        // Filter by audience
        if ($request->has('audience') && $request->audience != 'all') {
            $query->where('audience', $request->audience);
        }

        // Get featured announcements
        $featured = (clone $query)->featured()->take(3)->get();

        // Get recent announcements
        $recent = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get types for filter
        $types = Announcement::select('type')->distinct()->pluck('type');

        return view('Announcements.public', compact('featured', 'recent', 'types'));
    }

    /**
     * Get announcements for API (AJAX)
     */
    public function getAnnouncements(Request $request)
    {
        $query = Announcement::published()
            ->forAudience($request->audience ?? 'all')
            ->orderBy('created_at', 'desc');

        if ($request->has('limit')) {
            $query->limit($request->limit);
        }

        $announcements = $query->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }

    /**
     * Bulk delete announcements
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:announcements,id',
        ]);

        try {
            // Delete images for announcements
            $announcements = Announcement::whereIn('id', $validated['ids'])->get();
            foreach ($announcements as $announcement) {
                if ($announcement->image && Storage::exists('public/' . $announcement->image)) {
                    Storage::delete('public/' . $announcement->image);
                }
            }

            $deleted = Announcement::whereIn('id', $validated['ids'])->delete();

            Log::info('Bulk delete announcements', [
                'count' => $deleted,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $deleted . ' announcement(s) deleted.',
                'deleted' => $deleted,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcements.',
            ], 500);
        }
    }

    /**
     * Auto-expire announcements that have passed their expiry date
     * This can be called via a scheduled task
     */
    public function autoExpire()
    {
        try {
            $expired = Announcement::where('expiry_date', '<=', now())
                ->where('is_published', true)
                ->update(['is_published' => false]);

            Log::info('Auto-expired announcements', [
                'count' => $expired,
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$expired} announcement(s) auto-expired.",
                'expired' => $expired,
            ]);

        } catch (\Exception $e) {
            Log::error('Auto-expire error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-expire announcements.',
            ], 500);
        }
    }

    /**
     * Restore an expired announcement
     */
    public function restore(Announcement $announcement)
    {
        try {
            if (!$announcement->isExpired()) {
                return redirect()
                    ->route('Announcements.index')
                    ->with('info', 'Announcement is not expired.');
            }

            $announcement->update([
                'expiry_date' => null,
                'is_published' => true,
            ]);

            Log::info('Announcement restored', [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'restored_by' => auth()->id(),
            ]);

            return redirect()
                ->route('announcements.index')
                ->with('success', 'Announcement restored successfully!');

        } catch (\Exception $e) {
            Log::error('Error restoring announcement: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore announcement.');
        }
    }
}