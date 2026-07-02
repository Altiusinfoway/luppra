<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RazorpaySettingsController extends Controller
{
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

        $creatorId = (int) auth()->user()->creatorId();
        $rows = DB::table('settings')
            ->where('created_by', $creatorId)
            ->whereIn('name', ['razorpay_enabled', 'razorpay_key_id', 'razorpay_key_secret'])
            ->get()
            ->pluck('value', 'name');

        $settings = [
            'razorpay_enabled' => (int) ($rows['razorpay_enabled'] ?? 0),
            'razorpay_key_id' => (string) ($rows['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', '')),
            'razorpay_key_secret' => (string) ($rows['razorpay_key_secret'] ?? ''),
        ];

        return view('setting.razorpay', compact('settings'));
    }

    public function save(Request $request)
    {
        if ($deny = $this->denyIfNotSuperAdmin()) {
            return $deny;
        }

        $data = $request->validate([
            'razorpay_enabled' => 'nullable|boolean',
            'razorpay_key_id' => 'nullable|string|max:190',
            'razorpay_key_secret' => 'nullable|string|max:255',
        ]);

        $enabled = $request->boolean('razorpay_enabled');
        if ($enabled && (empty($data['razorpay_key_id']) || empty($data['razorpay_key_secret']))) {
            return redirect()->back()->with('error', 'Razorpay Key ID and Secret are required when gateway is enabled.');
        }

        $creatorId = (int) auth()->user()->creatorId();
        $now = now();

        $payload = [
            'razorpay_enabled' => $enabled ? '1' : '0',
            'razorpay_key_id' => (string) ($data['razorpay_key_id'] ?? ''),
            'razorpay_key_secret' => (string) ($data['razorpay_key_secret'] ?? ''),
        ];

        foreach ($payload as $name => $value) {
            DB::table('settings')->updateOrInsert(
                ['name' => $name, 'created_by' => $creatorId],
                ['value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        return redirect()->route('setting.razorpay.index')->with('success', 'Razorpay settings updated successfully.');
    }
}

