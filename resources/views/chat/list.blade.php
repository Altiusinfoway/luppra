@extends('layouts.app')

@section('content')
@section('page-css')
<style>
.chat-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
}

.chat-suite .hero-shell,
.chat-suite .chat-shell {
    border: 1px solid rgba(255, 255, 255, 0.78);
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
}

.chat-suite .hero-shell {
    border-radius: 28px;
    background:
        radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
    margin-bottom: 1rem;
}

.chat-suite .chat-shell {
    border-radius: 22px;
    overflow: hidden;
}

.chat-suite .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.76);
    border: 1px solid #dbeafe;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.chat-suite .hero-title {
    font-size: clamp(2rem, 3vw, 2.7rem);
    line-height: 1.05;
    letter-spacing: -0.04em;
    font-weight: 800;
    margin: 1rem 0 .45rem;
    color: #0f172a;
}

.chat-suite .hero-subtitle {
    color: #64748b;
}

.chat-suite .status-banner {
    border: 1px solid #dbe4f0;
    border-radius: 18px;
    padding: 0.95rem 1rem;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
}

.chat-suite .status-banner .banner-label {
    display: block;
    margin-bottom: 0.3rem;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    opacity: .82;
}

.chat-suite .status-banner.status-danger {
    border-color: #fecaca;
    background: linear-gradient(180deg, #fef2f2 0%, #fffafa 100%);
    color: #b91c1c;
}

.chat-suite .status-banner.status-info {
    border-color: #bfdbfe;
    background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
    color: #1d4ed8;
}

.wa-shell {
    height: calc(100vh - 180px);
    min-height: 560px;
    background: #f3f7fb;
    border: 0;
    border-radius: 0;
    overflow: hidden;
    display: flex;
}

.wa-sidebar {
    width: 34%;
    min-width: 320px;
    max-width: 420px;
    border-right: 1px solid #d9dbdd;
    display: flex;
    flex-direction: column;
    background: #fff;
}

.wa-chat-pane {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #efeae2;
    position: relative;
}

.wa-panel-header {
    height: 64px;
    padding: 0 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border-bottom: 1px solid #d9dbdd;
}

.wa-device-title {
    font-size: 14px;
    font-weight: 600;
    color: #111b21;
}

.wa-device-sub {
    font-size: 12px;
    color: #667781;
}

.wa-search-wrap {
    padding: 10px 12px;
    background: #fff;
    border-bottom: 1px solid #f0f2f5;
}

.wa-search-input {
    border: 0;
    background: #f0f2f5;
    border-radius: 8px;
    font-size: 13px;
    padding: 9px 12px;
}

.wa-search-input:focus {
    box-shadow: none;
    background: #fff;
    border: 1px solid #d1d7db;
}

.wa-contact-list {
    flex: 1;
    overflow-y: auto;
    background: #fff;
}

.wa-contact {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    border-bottom: 1px solid #f0f2f5;
    cursor: pointer;
}

.wa-contact:hover {
    background: #f5f6f6;
}

.wa-contact.active {
    background: #e9edef;
}

.wa-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    margin-right: 12px;
    background: #dfe5e7;
    object-fit: cover;
}

.wa-contact-main {
    flex: 1;
    min-width: 0;
}

.wa-contact-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.wa-contact-name {
    font-size: 14px;
    font-weight: 500;
    color: #111b21;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.wa-contact-time {
    font-size: 11px;
    color: #667781;
}

.wa-contact-preview {
    flex: 1;
    min-width: 0;
    font-size: 12px;
    color: #667781;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.wa-unread {
    min-width: 20px;
    height: 20px;
    border-radius: 999px;
    background: #25d366;
    color: #fff;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
}

.wa-thread-header {
    height: 64px;
    padding: 0 16px;
    background: #f8fafc;
    border-bottom: 1px solid #d9dbdd;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.wa-thread-name {
    font-size: 15px;
    font-weight: 600;
    color: #111b21;
}

.wa-thread-sub {
    font-size: 12px;
    color: #667781;
}

.wa-refresh-btn {
    border: 1px solid #d1d7db;
    background: #fff;
    border-radius: 8px;
    font-size: 12px;
    padding: 6px 10px;
}

.wa-message-history {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
}

.wa-empty-state {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #667781;
}

.wa-empty-state img {
    width: 160px;
    opacity: 0.35;
    margin-bottom: 12px;
}

.wa-row {
    display: flex;
    margin-bottom: 10px;
}

.wa-row.right {
    justify-content: flex-end;
}

.wa-bubble {
    max-width: 72%;
    font-size: 13px;
    line-height: 1.35;
    border-radius: 8px;
    padding: 8px 10px 6px;
    box-shadow: 0 1px 0 rgba(11, 20, 26, 0.06);
    word-break: break-word;
}

.wa-bubble.left {
    background: #fff;
}

.wa-bubble.right {
    background: #d9fdd3;
}

.wa-bubble-time {
    display: block;
    font-size: 10px;
    color: #667781;
    margin-top: 4px;
    text-align: right;
}

.wa-composer {
    padding: 10px 12px;
    background: #f8fafc;
    border-top: 1px solid #d9dbdd;
}

.wa-composer .input-group {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
}

.wa-composer input,
.wa-composer select {
    border: 0 !important;
    box-shadow: none !important;
    font-size: 13px;
}

.wa-composer .btn-send {
    border: 0;
    border-radius: 0;
    background: #25d366;
    color: #fff;
    padding: 0 16px;
}

.wa-attach-btn {
    border: 0;
    background: #fff;
    color: #54656f;
    padding: 0 12px;
}

.wa-file-pill {
    display: none;
    margin-top: 8px;
    padding: 6px 10px;
    border-radius: 16px;
    background: #e9edef;
    font-size: 12px;
    color: #111b21;
}

.wa-file-pill .remove-file {
    margin-left: 8px;
    color: #d9534f;
    cursor: pointer;
}

.wa-media-box {
    margin-bottom: 6px;
}

.wa-media-loading,
.wa-media-fallback {
    font-size: 12px;
    color: #667781;
}

.wa-inline-image,
.wa-inline-video {
    max-width: 260px;
    width: 100%;
    border-radius: 8px;
    display: block;
}

.wa-inline-audio {
    width: 240px;
    display: block;
}

.wa-doc-card {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 220px;
    max-width: 280px;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.7);
    color: #111b21;
    text-decoration: none;
}

.wa-doc-icon {
    min-width: 42px;
    height: 42px;
    border-radius: 8px;
    background: #ef4444;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

.wa-doc-meta {
    min-width: 0;
}

.wa-doc-name {
    font-size: 13px;
    font-weight: 600;
    word-break: break-word;
}

.wa-doc-type,
.wa-caption {
    font-size: 12px;
    color: #54656f;
    margin-top: 4px;
    word-break: break-word;
}

@media (max-width: 991px) {
    .wa-shell {
        flex-direction: column;
        height: auto;
        min-height: 0;
    }
    .wa-sidebar {
        width: 100%;
        max-width: none;
        min-width: 0;
        height: 300px;
    }
    .wa-chat-pane {
        min-height: 420px;
    }
}
</style>
@endsection

<div class="page-content chat-suite">
    <div class="container-fluid">
        <div class="hero-shell">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="hero-eyebrow">Conversation Center</span>
                        <h1 class="hero-title">Chat Inbox</h1>
                        <p class="hero-subtitle mb-0">Manage WhatsApp conversations inside the same cleaner admin shell while preserving the familiar chat layout for fast communication work.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-lg-end">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Chat</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card chat-shell">
            <div class="card-body p-0">
                @if(!empty($device))
                <div class="wa-shell">
                    <aside class="wa-sidebar">
                        <div class="wa-panel-header">
                            <div>
                                <div class="wa-device-title">{{ $device->name ?? 'WhatsApp' }}</div>
                                <div class="wa-device-sub">{{ __('Chats') }}</div>
                            </div>
                        </div>

                        <div class="wa-search-wrap">
                            <input type="text" class="form-control wa-search-input filter-row" data-target=".wa-contact" placeholder="{{ __('Search or start new chat') }}">
                        </div>

                        <div class="d-flex justify-content-center qr-area p-4">
                            <div class="text-center">
                                <div class="spinner-grow text-success" role="status"></div>
                                <p class="mb-0 mt-2"><strong>{{ __('Loading chats...') }}</strong></p>
                            </div>
                        </div>

                        <div class="status-banner status-danger server_disconnect none m-3" role="alert">
                            <span class="banner-label">{{ __('Connection issue') }}</span>
                            {{ __('Server disconnected') }}
                        </div>

                        <div class="wa-contact-list contact-list"></div>
                    </aside>

                    <section class="wa-chat-pane">
                        <div class="wa-thread-header">
                            <div>
                                <div class="wa-thread-name selected-contact-label">{{ __('Select a chat') }}</div>
                                <div class="wa-thread-sub selected-contact-sub">{{ __('Messages are end-to-end encrypted') }}</div>
                            </div>
                            <button type="button" class="wa-refresh-btn" id="refresh-messages">{{ __('Refresh') }}</button>
                        </div>

                        <div class="wa-message-history" id="message-history">
                            <div class="wa-empty-state initial-chat-image">
                                <img src="{{ asset('public/uploads/whatsapp-bg.png') }}" alt="whatsapp">
                                <p class="mb-0">{{ __('Select a contact to start messaging') }}</p>
                            </div>
                        </div>

                        <form method="post" class="ajaxform wa-composer" action="{{ route('chat.send-message',$device->uuid) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="reciver" value="" class="reciver-number">
                            <input type="file" id="chat-file-input" name="file" class="d-none" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx,.mp4,.mov,.avi,.mkv,.mp3,.wav,.ogg,.aac,.webm">
                            <div class="input-group sendble-row none">
                                <select class="form-control" name="selecttype" id="select-type" style="max-width: 130px;">
                                    <option value="plain-text">{{ __('Text') }}</option>
                                    @if(count($templates) > 0)
                                    <option value="template">{{ __('Template') }}</option>
                                    @endif
                                </select>

                                @if(count($templates) > 0)
                                <select class="form-control none" name="template" id="templates" style="max-width: 180px;">
                                    @foreach($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->title }}</option>
                                    @endforeach
                                </select>
                                @endif

                                <button class="wa-attach-btn" type="button" id="open-file-picker" title="{{ __('Attach') }}">&#128206;</button>
                                <input type="text" name="message" class="form-control" id="plain-text" placeholder="{{ __('Type a message') }}">
                                <button class="btn btn-send submit-button" type="submit">{{ __('Send') }}</button>
                            </div>
                            <div class="wa-file-pill sendble-row" id="selected-file-pill">
                                <span id="selected-file-name"></span>
                                <span class="remove-file" id="remove-selected-file">&times;</span>
                            </div>
                        </form>
                    </section>
                </div>
                @else
                <div class="p-3">
                    <div class="status-banner status-info mb-0">
                        <span class="banner-label">{{ __('Plan access') }}</span>
                        {{ __('Chat list access feature is not available in your subscription plan') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <input type="hidden" id="uuid" value="{{$device->uuid}}">
        <input type="hidden" id="base_url" value="{{ url('/') }}">
    </div>
</div>
@endsection

@section('page-script')
<script type="text/javascript" src="{{ asset('public/build/assets/js/pages/user/chat/list.js') }}?v={{ filemtime(public_path('build/assets/js/pages/user/chat/list.js')) }}"></script>
@endsection
