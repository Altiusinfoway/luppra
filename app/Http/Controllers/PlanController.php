<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    private const AVAILABLE_MODULES = [
        'sales' => 'Sales',
        'accounts' => 'Accounts',
        'hr' => 'HR',
        'whatsapp' => 'WhatsApp',
        'bulk_message' => 'Bulk Message',
        'all' => 'All Modules',
    ];

    private function denyIfNotSuperAdmin()
    {
        if (!auth()->check() || auth()->user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return null;
    }

    public function index()
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $creatorId = auth()->user()->creatorId();
        $plans = Plan::query()
            ->where('created_by', $creatorId)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $availableModules = self::AVAILABLE_MODULES;

        return view('setting.plans', compact('plans', 'availableModules'));
    }

    public function store(Request $request)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $creatorId = auth()->user()->creatorId();
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('plans', 'name')->where(fn ($q) => $q->where('created_by', $creatorId)),
            ],
            'code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,one_time',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'user_limit' => 'nullable|integer|min:1',
            'whatsapp_limit' => 'nullable|integer|min:1',
            'modules' => 'nullable|array',
            'modules.*' => 'in:sales,accounts,hr,whatsapp,bulk_message,all',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $modules = collect($data['modules'] ?? [])
            ->map(fn ($v) => strtolower(trim((string) $v)))
            ->filter()
            ->values()
            ->all();

        if (in_array('all', $modules, true)) {
            $modules = ['*'];
        }

        Plan::query()->create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'trial_days' => (int) ($data['trial_days'] ?? 0),
            'user_limit' => $data['user_limit'] ?? null,
            'whatsapp_limit' => $data['whatsapp_limit'] ?? null,
            'modules' => $modules,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'created_by' => $creatorId,
        ]);

        return redirect()->route('setting.plans.index')->with('success', 'Plan created successfully.');
    }

    public function update(Request $request, Plan $plan)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $creatorId = auth()->user()->creatorId();
        if ((int) $plan->created_by !== (int) $creatorId) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('plans', 'name')
                    ->where(fn ($q) => $q->where('created_by', $creatorId))
                    ->ignore($plan->id),
            ],
            'code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,one_time',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'user_limit' => 'nullable|integer|min:1',
            'whatsapp_limit' => 'nullable|integer|min:1',
            'modules' => 'nullable|array',
            'modules.*' => 'in:sales,accounts,hr,whatsapp,bulk_message,all',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $modules = collect($data['modules'] ?? [])
            ->map(fn ($v) => strtolower(trim((string) $v)))
            ->filter()
            ->values()
            ->all();

        if (in_array('all', $modules, true)) {
            $modules = ['*'];
        }

        $plan->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'trial_days' => (int) ($data['trial_days'] ?? 0),
            'user_limit' => $data['user_limit'] ?? null,
            'whatsapp_limit' => $data['whatsapp_limit'] ?? null,
            'modules' => $modules,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('setting.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        if ((int) $plan->created_by !== (int) auth()->user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $plan->delete();

        return redirect()->route('setting.plans.index')->with('success', 'Plan deleted successfully.');
    }
}
