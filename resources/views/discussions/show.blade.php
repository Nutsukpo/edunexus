@extends('layouts.master')

@section('title', $group->name)

@section('content')
<style>
    .chat-container {
        height: 500px;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
    }
    .message {
        max-width: 70%;
        margin-bottom: 10px;
        padding: 10px 15px;
        border-radius: 18px;
        word-wrap: break-word;
    }
    .message-sent {
        align-self: flex-end;
        background: #007bff;
        color: white;
        border-bottom-right-radius: 4px;
    }
    .message-received {
        align-self: flex-start;
        background: white;
        color: #333;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .message-time {
        font-size: 10px;
        opacity: 0.7;
        margin-top: 4px;
    }
    .message-sender {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }
    .attachment-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        margin-top: 5px;
        cursor: pointer;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <!-- Group Info -->
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $group->avatar_url }}" alt="{{ $group->name }}" 
                         class="rounded-circle mb-3" width="80" height="80">
                    <h5>{{ $group->name }}</h5>
                    <p class="text-muted small">{{ $group->description ?? 'No description' }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge badge-secondary">{{ $group->participant_count }} members</span>
                        <span class="badge badge-info">{{ ucfirst($group->type) }}</span>
                    </div>
                </div>
            </div>

            <!-- Participants -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Participants</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($group->participants as $participant)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-user-circle mr-2"></i>
                                    {{ $participant->staff->first_name ?? '' }} {{ $participant->staff->last_name ?? '' }}
                                </div>
                                <span class="badge badge-{{ $participant->role == 'admin' ? 'primary' : 'secondary' }}">
                                    {{ ucfirst($participant->role) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $group->name }}</h5>
                </div>
                <div class="card-body p-0">
                    <!-- Chat Messages -->
                    <div class="chat-container" id="chatContainer">
                        @foreach($messages as $message)
                            @if($message->is_deleted)
                                <div class="message message-received text-muted">
                                    <em>This message has been deleted</em>
                                    <div class="message-time">{{ $message->created_at->diffForHumans() }}</div>
                                </div>
                            @else
                            @php
                                $currentStaff = auth()->user()->staff;
                                $isSent = $currentStaff?->id === $message->sender_id;
                            @endphp
                                <div class="message {{ $isSent ? 'message-sent' : 'message-received' }}">
                                    @if(!$isSent)
                                        <div class="message-sender">
                                            {{ $message->sender->first_name ?? '' }} {{ $message->sender->last_name ?? '' }}
                                        </div>
                                    @endif
                                    <div class="message-text">{{ $message->message }}</div>
                                    
                                    <!-- Attachments -->
                                    @if($message->attachments->isNotEmpty())
                                        <div class="mt-2">
                                            @foreach($message->attachments as $attachment)
                                                @if(in_array($attachment->file_type, ['jpg','jpeg','png','gif']))
                                                    <img src="{{ Storage::url($attachment->file_path) }}" 
                                                         alt="Attachment" class="attachment-preview">
                                                @else
                                                    <a href="{{ route('discussions.attachment.download', $attachment->id) }}" 
                                                       class="btn btn-sm btn-light btn-block text-left">
                                                        <i class="fas fa-file"></i> {{ $attachment->file_name }}
                                                        ({{ number_format($attachment->file_size / 1024, 1) }} KB)
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="message-time d-flex justify-content-between">
                                        <span>{{ $message->created_at->format('h:i A') }}</span>
                                        @if($isSent && !$message->is_deleted)
                                            <span>
                                                <button class="btn btn-sm btn-link text-white p-0 mr-2" 
                                                        onclick="editMessage({{ $message->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-link text-white p-0" 
                                                        onclick="deleteMessage({{ $message->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Reply/Edit Modal -->
                    <div class="modal fade" id="editMessageModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Message</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <textarea id="editMessageText" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="saveEditMessage()">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-3 border-top">
                        <form action="{{ route('discussions.message.send', $group->slug) }}" method="POST" 
                              enctype="multipart/form-data" id="messageForm">
                            @csrf
                            <div class="form-group mb-0">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <label class="input-group-text" for="attachments">
                                            <i class="fas fa-paperclip"></i>
                                        </label>
                                        <input type="file" name="attachments[]" id="attachments" 
                                               class="d-none" multiple>
                                    </div>
                                    <input type="text" name="message" class="form-control" 
                                           placeholder="Type your message..." required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted" id="fileNames"></small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Scroll to bottom on load
    document.getElementById('chatContainer').scrollTop = document.getElementById('chatContainer').scrollHeight;

    // File input display
    document.getElementById('attachments').addEventListener('change', function() {
        let names = Array.from(this.files).map(f => f.name).join(', ');
        document.getElementById('fileNames').textContent = names ? 'Attached: ' + names : '';
    });

    // Edit message
    let editingMessageId = null;

    function editMessage(id) {
        editingMessageId = id;
        let messageText = document.querySelector(`[data-message-id="${id}"]`)?.textContent || '';
        document.getElementById('editMessageText').value = messageText;
        $('#editMessageModal').modal('show');
    }

    function saveEditMessage() {
        let newText = document.getElementById('editMessageText').value;
        if (!newText.trim()) {
            alert('Message cannot be empty.');
            return;
        }

        fetch(`{{ url('discussions/message') }}/${editingMessageId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: newText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update message.');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }

    // Delete message
    function deleteMessage(id) {
        if (!confirm('Are you sure you want to delete this message?')) return;

        fetch(`{{ url('discussions/message') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete message.');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }

    // Auto-scroll on new message
    const chatContainer = document.getElementById('chatContainer');
    const observer = new MutationObserver(() => {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    });
    observer.observe(chatContainer, { childList: true, subtree: true });
</script>
@endpush
@endsection