<?php

namespace App\Http\Controllers;

use App\Models\LeadStage;
use App\Models\WhatsappBotKnowledge;
use App\Models\WhatsappBotRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WhatsappBotController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = Auth::user()->creatorId();
        $tablesReady = Schema::hasTable('whatsapp_bot_rules') && Schema::hasTable('whatsapp_bot_knowledge');

        $settings = DB::connection(app()->bound('currentTenant') ? 'tenant' : 'landlord')->table('settings')
            ->where('created_by', $creatorId)
            ->whereIn('name', [
                'wa_ai_bot_enabled',
                'wa_ai_bot_model',
                'wa_ai_bot_cooldown_seconds',
                'wa_ai_bot_reply_delay_ms',
                'wa_ai_bot_business_name',
                'wa_ai_bot_business_context',
                'wa_ai_bot_tone',
                'wa_ai_bot_fallback_text',
                'wa_ai_bot_system_prompt',
            ])
            ->pluck('value', 'name')
            ->toArray();

        $stages = LeadStage::orderBy('order')->get(['id', 'name']);
        $rules = collect();
        $knowledge = collect();

        if ($tablesReady) {
            $rules = WhatsappBotRule::where('created_by', $creatorId)->get()->keyBy('lead_stage_id');
            $knowledge = WhatsappBotKnowledge::where('created_by', $creatorId)->orderBy('sort_order')->orderByDesc('id')->get();
        }

        return view('setting.whatsapp-bot', compact('settings', 'stages', 'rules', 'knowledge', 'tablesReady'));
    }

    public function save(Request $request)
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (!Schema::hasTable('whatsapp_bot_rules') || !Schema::hasTable('whatsapp_bot_knowledge')) {
            return redirect()->route('setting.whatsapp-bot.index')->with('error', 'Bot tables are missing. Please run: php artisan migrate');
        }

        $validated = $request->validate([
            'wa_ai_bot_enabled' => 'nullable|boolean',
            'wa_ai_bot_model' => 'required|string|max:80',
            'wa_ai_bot_cooldown_seconds' => 'required|integer|min:5|max:3600',
            'wa_ai_bot_reply_delay_ms' => 'required|integer|min:0|max:10000',
            'wa_ai_bot_business_name' => 'nullable|string|max:120',
            'wa_ai_bot_business_context' => 'nullable|string|max:2000',
            'wa_ai_bot_tone' => 'nullable|string|max:120',
            'wa_ai_bot_fallback_text' => 'nullable|string|max:500',
            'wa_ai_bot_system_prompt' => 'nullable|string|max:8000',
            'rules' => 'nullable|array',
        ]);

        $creatorId = Auth::user()->creatorId();

        $this->upsertSetting($creatorId, 'wa_ai_bot_enabled', $request->boolean('wa_ai_bot_enabled') ? '1' : '0');
        $this->upsertSetting($creatorId, 'wa_ai_bot_model', $validated['wa_ai_bot_model']);
        $this->upsertSetting($creatorId, 'wa_ai_bot_cooldown_seconds', (string) $validated['wa_ai_bot_cooldown_seconds']);
        $this->upsertSetting($creatorId, 'wa_ai_bot_reply_delay_ms', (string) $validated['wa_ai_bot_reply_delay_ms']);
        $this->upsertSetting($creatorId, 'wa_ai_bot_business_name', $validated['wa_ai_bot_business_name'] ?? '');
        $this->upsertSetting($creatorId, 'wa_ai_bot_business_context', $validated['wa_ai_bot_business_context'] ?? '');
        $this->upsertSetting($creatorId, 'wa_ai_bot_tone', $validated['wa_ai_bot_tone'] ?? '');
        $this->upsertSetting($creatorId, 'wa_ai_bot_fallback_text', $validated['wa_ai_bot_fallback_text'] ?? '');
        $this->upsertSetting($creatorId, 'wa_ai_bot_system_prompt', $validated['wa_ai_bot_system_prompt'] ?? '');

        $rulesPayload = $request->input('rules', []);
        $stageIds = LeadStage::pluck('id')->toArray();

        foreach ($stageIds as $stageId) {
            $row = $rulesPayload[$stageId] ?? null;
            if (!$row) {
                continue;
            }

            $mode = in_array(($row['mode'] ?? 'ai'), ['ai', 'template', 'handoff'], true) ? $row['mode'] : 'ai';
            $isActive = isset($row['is_active']) ? 1 : 0;
            $promptHint = (string) ($row['prompt_hint'] ?? '');
            $templateText = (string) ($row['template_text'] ?? '');

            WhatsappBotRule::updateOrCreate(
                [
                    'created_by' => $creatorId,
                    'lead_stage_id' => $stageId,
                ],
                [
                    'mode' => $mode,
                    'is_active' => $isActive,
                    'prompt_hint' => $promptHint,
                    'template_text' => $templateText,
                ]
            );
        }

        return redirect()->route('setting.whatsapp-bot.index')->with('success', 'WhatsApp bot settings updated successfully.');
    }

    public function storeKnowledge(Request $request)
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (!Schema::hasTable('whatsapp_bot_knowledge')) {
            return redirect()->route('setting.whatsapp-bot.index')->with('error', 'Knowledge table is missing. Please run: php artisan migrate');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'keywords' => 'nullable|string|max:255',
            'answer' => 'required|string|max:4000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        WhatsappBotKnowledge::create([
            'created_by' => Auth::user()->creatorId(),
            'title' => $validated['title'],
            'keywords' => $validated['keywords'] ?? '',
            'answer' => $validated['answer'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active') ? 1 : 0,
        ]);

        return redirect()->route('setting.whatsapp-bot.index')->with('success', 'Knowledge item added.');
    }

    public function updateKnowledge(Request $request, $id)
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (!Schema::hasTable('whatsapp_bot_knowledge')) {
            return redirect()->route('setting.whatsapp-bot.index')->with('error', 'Knowledge table is missing. Please run: php artisan migrate');
        }

        $item = WhatsappBotKnowledge::where('created_by', Auth::user()->creatorId())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'keywords' => 'nullable|string|max:255',
            'answer' => 'required|string|max:4000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $item->update([
            'title' => $validated['title'],
            'keywords' => $validated['keywords'] ?? '',
            'answer' => $validated['answer'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active') ? 1 : 0,
        ]);

        return redirect()->route('setting.whatsapp-bot.index')->with('success', 'Knowledge item updated.');
    }

    public function deleteKnowledge($id)
    {
        if (!Auth::user()->can('manage company settings')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (!Schema::hasTable('whatsapp_bot_knowledge')) {
            return redirect()->route('setting.whatsapp-bot.index')->with('error', 'Knowledge table is missing. Please run: php artisan migrate');
        }

        $item = WhatsappBotKnowledge::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $item->delete();

        return redirect()->route('setting.whatsapp-bot.index')->with('success', 'Knowledge item deleted.');
    }

    private function upsertSetting(int $creatorId, string $name, string $value): void
    {
        DB::connection(app()->bound('currentTenant') ? 'tenant' : 'landlord')->table('settings')->updateOrInsert(
            ['name' => $name, 'created_by' => $creatorId],
            ['value' => $value]
        );
    }
}
