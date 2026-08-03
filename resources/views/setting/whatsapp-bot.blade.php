@extends('layouts.app')

@section('page-css')
<style>
    .settings-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
    }
    .settings-suite .hero-shell,
    .settings-suite .settings-shell {
        border: 1px solid rgba(255, 255, 255, 0.78);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .hero-shell {
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.14), transparent 28%),
            radial-gradient(circle at left center, rgba(37, 99, 235, 0.16), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
        margin-bottom: 1rem;
    }
    .settings-suite .settings-shell {
        border-radius: 22px;
    }
    .settings-suite .hero-eyebrow {
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
    .settings-suite .hero-title {
        font-size: clamp(2rem, 3vw, 2.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        margin: 1rem 0 .45rem;
        color: #0f172a;
    }
    .settings-suite .hero-subtitle {
        color: #64748b;
    }
    .settings-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    .settings-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .settings-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }
    .settings-suite .section-intro {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 16px 18px;
        margin-bottom: 22px;
    }
    .settings-suite .table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
    }
    .settings-suite .status-banner {
        border: 1px solid #dce4ee;
        border-radius: 18px;
        padding: 1rem 1.15rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .settings-suite .status-banner.status-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fff7d6 100%);
        border-color: #fde68a;
        color: #92400e;
    }
</style>
@endsection

@section('content')
<div class="page-content settings-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Automation</span>
                                <h1 class="hero-title">WhatsApp AI Bot</h1>
                                <p class="hero-subtitle mb-0">Configure AI replies, stage rules, and knowledge-base responses inside the same modern admin shell as the refreshed CRM.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('setting.invoice.view') }}">Settings</a></li>
                                        <li class="breadcrumb-item active">WhatsApp AI Bot</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @if(isset($tablesReady) && !$tablesReady)
                <div class="col-12">
                    <div class="status-banner status-warning mb-0">
                        WhatsApp bot tables are not created yet. Run <code>php artisan migrate</code> and refresh this page.
                    </div>
                </div>
            @endif

            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Automation</span>
                        <h3>AI Bot</h3>
                        <p class="text-muted mb-0 mt-2">Control WhatsApp reply behavior and customer-response tone from one settings workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Rules</span>
                        <h3>{{ isset($stages) ? count($stages) : 0 }}</h3>
                        <p class="text-muted mb-0 mt-2">Lead stage rules and fallback prompts stay grouped with the broader bot configuration.</p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card settings-shell">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Bot Configuration & Rules</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('setting.whatsapp-bot.save') }}">
                            @csrf
                            <div class="section-intro">
                                <h6 class="mb-1">Bot configuration</h6>
                                <p class="text-muted mb-0">Set the AI model, timing, business context, and message style before adjusting stage-specific reply behavior.</p>
                            </div>
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
                            <div class="table-responsive table-wrap">
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
                <div class="card settings-shell">
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

                        <div class="table-responsive table-wrap">
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
