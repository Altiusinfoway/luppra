@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">WhatsApp AI Bot</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('setting.invoice.view') }}">Settings</a></li>
                            <li class="breadcrumb-item active">WhatsApp AI Bot</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @if(isset($tablesReady) && !$tablesReady)
                <div class="col-12">
                    <div class="alert alert-warning mb-0">
                        WhatsApp bot tables are not created yet. Run <code>php artisan migrate</code> and refresh this page.
                    </div>
                </div>
            @endif

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Bot Configuration & Rules</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('setting.whatsapp-bot.save') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Enable AI Bot</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="wa_ai_bot_enabled" value="1"
                                            {{ ($settings['wa_ai_bot_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Model</label>
                                    <input type="text" class="form-control" name="wa_ai_bot_model"
                                        value="{{ old('wa_ai_bot_model', $settings['wa_ai_bot_model'] ?? 'gpt-4o-mini') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cooldown (seconds)</label>
                                    <input type="number" min="5" class="form-control" name="wa_ai_bot_cooldown_seconds"
                                        value="{{ old('wa_ai_bot_cooldown_seconds', $settings['wa_ai_bot_cooldown_seconds'] ?? 45) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Reply Delay (ms)</label>
                                    <input type="number" min="0" class="form-control" name="wa_ai_bot_reply_delay_ms"
                                        value="{{ old('wa_ai_bot_reply_delay_ms', $settings['wa_ai_bot_reply_delay_ms'] ?? 600) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" class="form-control" name="wa_ai_bot_business_name"
                                        value="{{ old('wa_ai_bot_business_name', $settings['wa_ai_bot_business_name'] ?? '') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Bot Tone</label>
                                    <input type="text" class="form-control" name="wa_ai_bot_tone"
                                        value="{{ old('wa_ai_bot_tone', $settings['wa_ai_bot_tone'] ?? 'professional, concise and helpful') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Business Context</label>
                                    <textarea class="form-control" rows="3" name="wa_ai_bot_business_context">{{ old('wa_ai_bot_business_context', $settings['wa_ai_bot_business_context'] ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">System Prompt (Training Prompt)</label>
                                    <textarea class="form-control" rows="5" name="wa_ai_bot_system_prompt">{{ old('wa_ai_bot_system_prompt', $settings['wa_ai_bot_system_prompt'] ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Fallback Reply</label>
                                    <input type="text" class="form-control" name="wa_ai_bot_fallback_text"
                                        value="{{ old('wa_ai_bot_fallback_text', $settings['wa_ai_bot_fallback_text'] ?? 'Thanks for your message. Our team will connect with you shortly.') }}">
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">Lead Stage Rules</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 180px;">Lead Stage</th>
                                            <th style="min-width: 120px;">Active</th>
                                            <th style="min-width: 180px;">Mode</th>
                                            <th>Prompt Hint (for AI mode)</th>
                                            <th>Template Text (for template mode)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stages as $stage)
                                            @php $rule = $rules[$stage->id] ?? null; @endphp
                                            <tr>
                                                <td>{{ $stage->name }}</td>
                                                <td>
                                                    <input type="checkbox" name="rules[{{ $stage->id }}][is_active]" value="1"
                                                        {{ $rule && (int) $rule->is_active === 1 ? 'checked' : '' }}>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="rules[{{ $stage->id }}][mode]">
                                                        <option value="ai" {{ $rule && $rule->mode === 'ai' ? 'selected' : '' }}>AI Reply</option>
                                                        <option value="template" {{ $rule && $rule->mode === 'template' ? 'selected' : '' }}>Template Reply</option>
                                                        <option value="handoff" {{ $rule && $rule->mode === 'handoff' ? 'selected' : '' }}>Handoff (No Auto Reply)</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="rules[{{ $stage->id }}][prompt_hint]"
                                                        value="{{ $rule->prompt_hint ?? '' }}"
                                                        placeholder="Example: push for quotation booking and qty details">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="rules[{{ $stage->id }}][template_text]"
                                                        value="{{ $rule->template_text ?? '' }}"
                                                        placeholder="Used when mode is Template Reply">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-primary" type="submit">Save Bot Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">FAQ Knowledge Base</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('setting.whatsapp-bot.knowledge.store') }}" class="row g-3 mb-4">
                            @csrf
                            <div class="col-md-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Delivery timeline">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Keywords (comma separated)</label>
                                <input type="text" name="keywords" class="form-control" placeholder="delivery,time,dispatch">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Priority</label>
                                <input type="number" min="0" name="sort_order" class="form-control" value="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success w-100" type="submit">Add Knowledge</button>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Answer</label>
                                <textarea name="answer" rows="2" class="form-control" placeholder="Standard response used by bot"></textarea>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Keywords</th>
                                        <th style="min-width: 320px;">Answer</th>
                                        <th>Priority</th>
                                        <th>Active</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($knowledge as $item)
                                        <tr>
                                            <td>{{ $item->title }}</td>
                                            <td>{{ $item->keywords ?: '-' }}</td>
                                            <td>{{ $item->answer }}</td>
                                            <td>{{ $item->sort_order }}</td>
                                            <td>
                                                @if((int) $item->is_active === 1)
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('setting.whatsapp-bot.knowledge.delete', $item->id) }}"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this knowledge item?')">Delete</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No knowledge items yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
