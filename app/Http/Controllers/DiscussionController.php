<?php

namespace App\Http\Controllers;

use App\Models\DiscussionGroup;
use App\Models\DiscussionMessage;
use App\Models\DiscussionParticipant;
use App\Models\DiscussionAttachment;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class DiscussionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Get the logged-in staff
     */
    private function getStaff()
    {
        $user = Auth::user();
        $staff = Staff::where('email', $user->email)->first();
        
        if (!$staff && Schema::hasColumn('staff', 'user_id')) {
            $staff = Staff::where('user_id', $user->id)->first();
        }
        
        if (!$staff) {
            $staff = Staff::create([
                'first_name' => explode(' ', $user->name)[0] ?? $user->name,
                'last_name' => explode(' ', $user->name)[1] ?? 'Staff',
                'email' => $user->email,
                'staff_id' => 'STF-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            ]);
        }
        
        return $staff;
    }

    /**
     * Display all groups
     */
    public function index()
    {
        $staff = $this->getStaff();
        
        $groups = DiscussionGroup::whereHas('participants', function ($query) use ($staff) {
            $query->where('staff_id', $staff->id);
        })->with(['latestMessage', 'participants'])
          ->orderBy('last_message_at', 'desc')
          ->get();

        foreach ($groups as $group) {
            $group->unread_count = $group->getUnreadCount($staff->id);
        }

        return view('discussions.index', compact('groups'));
    }

    /**
     * Show the form for creating a new group
     */
    public function create()
    {
        $staff = $this->getStaff();
        $staffMembers = Staff::all();
        return view('discussions.create', compact('staff', 'staffMembers'));
    }

    /**
     * Store a new group
     */
    public function store(Request $request)
    {
        $staff = $this->getStaff();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:public,private,department',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:staff,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $validator->validated();
            $data['created_by'] = $staff->id;
            $data['settings'] = [
                'allow_media' => true,
                'allow_links' => true,
            ];

            $group = DiscussionGroup::create($data);

            // Add creator as admin
            $group->addParticipant($staff->id, 'admin');

            // Add other participants
            if (isset($data['participants']) && is_array($data['participants'])) {
                foreach ($data['participants'] as $participantId) {
                    if ($participantId != $staff->id) {
                        $group->addParticipant($participantId, 'member');
                    }
                }
            }

            Log::info('Discussion group created', [
                'group_id' => $group->id,
                'created_by' => $staff->id
            ]);

            return redirect()
                ->route('discussions.show', $group->slug)
                ->with('success', 'Group created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating group: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create group: ' . $e->getMessage());
        }
    }

   
    /**
     * Show a specific group
     */
    public function show($slug)
    {
        $staff = $this->getStaff(); // Make sure this is defined
        
        $group = DiscussionGroup::where('slug', $slug)
            ->with(['participants.staff', 'creator'])
            ->firstOrFail();

        // Check if staff is a participant
        if (!$group->isParticipant($staff->id)) {
            abort(403, 'You are not a member of this group.');
        }

        // Update last read time
        $group->participants()
            ->where('staff_id', $staff->id)
            ->update(['last_read_at' => now()]);

        // Get messages
        $messages = DiscussionMessage::where('group_id', $group->id)
            ->with(['sender', 'attachments', 'replies.sender'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Mark messages as read
        foreach ($messages as $message) {
            $message->markAsRead($staff->id);
        }

        // Pass staff to the view
        return view('discussions.show', compact('group', 'messages', 'staff'));
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, $slug)
    {
        $staff = $this->getStaff();
        
        $group = DiscussionGroup::where('slug', $slug)->firstOrFail();

        if (!$group->isParticipant($staff->id)) {
            abort(403, 'You are not a member of this group.');
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required_without:attachments|string|max:5000',
            'parent_id' => 'nullable|exists:discussion_messages,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mp3|max:20480',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $validator->validated();

            // Create message
            $message = DiscussionMessage::create([
                'group_id' => $group->id,
                'sender_id' => $staff->id,
                'parent_id' => $data['parent_id'] ?? null,
                'message' => $data['message'] ?? '',
                'type' => 'text',
                'read_by' => [$staff->id],
            ]);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('discussions/' . date('Y/m'), $filename, 'public');
                    
                    DiscussionAttachment::create([
                        'message_id' => $message->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            // Update group last message time
            $group->update(['last_message_at' => now()]);

            Log::info('Message sent', [
                'group_id' => $group->id,
                'message_id' => $message->id,
                'sender_id' => $staff->id
            ]);

            return redirect()
                ->route('discussions.show', $group->slug)
                ->with('success', 'Message sent!');

        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage($messageId)
    {
        $staff = $this->getStaff();
        $message = DiscussionMessage::with('group')->findOrFail($messageId);

        if ($message->sender_id !== $staff->id) {
            abort(403, 'You can only delete your own messages.');
        }

        $message->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'message' => 'This message has been deleted.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.'
        ]);
    }

    /**
     * Edit a message
     */
    public function editMessage(Request $request, $messageId)
    {
        $staff = $this->getStaff();
        $message = DiscussionMessage::findOrFail($messageId);

        if ($message->sender_id !== $staff->id) {
            abort(403, 'You can only edit your own messages.');
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $message->update([
            'message' => $request->message,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully.'
        ]);
    }

    /**
     * Add participant to group
     */
    public function addParticipant(Request $request, $slug)
    {
        $staff = $this->getStaff();
        $group = DiscussionGroup::where('slug', $slug)->firstOrFail();

        // Check if current user is admin
        $isAdmin = $group->participants()
            ->where('staff_id', $staff->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin) {
            abort(403, 'Only admins can add participants.');
        }

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($group->isParticipant($request->staff_id)) {
            return redirect()->back()
                ->with('error', 'Staff is already a participant.');
        }

        $group->addParticipant($request->staff_id, 'member');

        return redirect()
            ->route('discussions.show', $group->slug)
            ->with('success', 'Participant added successfully!');
    }

    /**
     * Remove participant from group
     */
    public function removeParticipant($slug, $staffId)
    {
        $staff = $this->getStaff();
        $group = DiscussionGroup::where('slug', $slug)->firstOrFail();

        // Check if current user is admin
        $isAdmin = $group->participants()
            ->where('staff_id', $staff->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin) {
            abort(403, 'Only admins can remove participants.');
        }

        // Don't allow removing the last admin
        $adminCount = $group->participants()->where('role', 'admin')->count();
        if ($adminCount <= 1) {
            $isLastAdmin = $group->participants()
                ->where('staff_id', $staffId)
                ->where('role', 'admin')
                ->exists();
            
            if ($isLastAdmin) {
                return redirect()->back()
                    ->with('error', 'Cannot remove the last admin.');
            }
        }

        $group->removeParticipant($staffId);

        return redirect()
            ->route('discussions.show', $group->slug)
            ->with('success', 'Participant removed successfully!');
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($attachmentId)
    {
        $attachment = DiscussionAttachment::with('message.group')->findOrFail($attachmentId);
        $staff = $this->getStaff();

        // Check if staff is a participant
        if (!$attachment->message->group->isParticipant($staff->id)) {
            abort(403, 'You are not a member of this group.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }
}