<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class StudentAnnouncementController extends Controller
{
    /**
     * Display announcements available to students.
     *
     * Visibility rules:
     * - Must be published.
     * - Audience must be "all" or "students".
     * - Publish date must have arrived.
     * - Expired announcements are hidden.
     */
    public function index(Request $request)
    {
        $student = auth('student')->user();

        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Base student announcement query
        |--------------------------------------------------------------------------
        */
        $query = Announcement::query()
            ->where('is_published', true)

            // Only announcements intended for everyone or students.
            ->whereIn('audience', ['all', 'students'])

            // Do not display announcements scheduled for the future.
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_date')
                    ->orWhere('publish_date', '<=', $now);
            })

            // Do not display expired announcements.
            ->where(function ($q) use ($now) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', $now);
            });

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Announcement type
        |--------------------------------------------------------------------------
        */
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        /*
        |--------------------------------------------------------------------------
        | Priority
        |--------------------------------------------------------------------------
        */
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        /*
        |--------------------------------------------------------------------------
        | Featured announcements
        |--------------------------------------------------------------------------
        */
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        /*
        |--------------------------------------------------------------------------
        | Date range
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_from')) {
            $query->whereDate(
                'publish_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'publish_date',
                '<=',
                $request->input('date_to')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get announcements
        |--------------------------------------------------------------------------
        |
        | No pagination is used.
        |
        */
        $announcements = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->orderByDesc('publish_date')
            ->orderByDesc('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Available announcement types
        |--------------------------------------------------------------------------
        |
        | These are generated only from announcements that students can
        | currently see.
        |
        */
        $types = Announcement::query()
            ->where('is_published', true)
            ->whereIn('audience', ['all', 'students'])
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_date')
                    ->orWhere('publish_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', $now);
            })
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */
        $total = $announcements->count();

        $featured = $announcements
            ->where('is_featured', true)
            ->count();

        $urgent = $announcements
            ->whereIn('priority', ['high', 'urgent'])
            ->count();

        return view('students.announcements', compact(
            'student',
            'announcements',
            'types',
            'total',
            'featured',
            'urgent'
        ));
    }

    /**
     * Display a single announcement.
     */
    public function show($id)
    {
        $student = auth('student')->user();

        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Only allow students to open announcements they are actually
        | permitted to see.
        |--------------------------------------------------------------------------
        */
        $announcement = Announcement::query()
            ->where('id', $id)
            ->where('is_published', true)
            ->whereIn('audience', ['all', 'students'])

            // Publication date must have arrived.
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_date')
                    ->orWhere('publish_date', '<=', $now);
            })

            // Expired announcements cannot be opened.
            ->where(function ($q) use ($now) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', $now);
            })

            ->firstOrFail();

        return view(
            'students.announcements.show',
            compact('student', 'announcement')
        );
    }
}