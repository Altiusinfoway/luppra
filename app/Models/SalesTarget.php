<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'min_target',
        'max_target',
        'incentive',
        'incentive_mode',
        'incentive_value',
        'incentive_slabs',
    ];

    protected $casts = [
        'incentive_slabs' => 'array',
        'incentive_value' => 'float',
        'min_target' => 'float',
        'max_target' => 'float',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function incentiveSummary(): string
    {
        $mode = (string) ($this->incentive_mode ?: 'percent_over_target');
        $value = (float) ($this->incentive_value ?? 0);

        if ($mode === 'fixed_on_achieve') {
            return 'Fixed Rs ' . number_format($value, 2) . ' on target achievement';
        }

        if ($mode === 'percent_on_achieved') {
            return number_format($value, 2) . '% of achieved amount';
        }

        if ($mode === 'slab') {
            $slabs = $this->normalizeSlabs();
            if (empty($slabs)) {
                return 'Slab based incentive';
            }
            return 'Slab (' . count($slabs) . ' rules)';
        }

        return number_format($value, 2) . '% on amount above target';
    }

    public function normalizeSlabs(): array
    {
        $slabs = is_array($this->incentive_slabs) ? $this->incentive_slabs : [];

        $clean = collect($slabs)->map(function ($row) {
            $from = (float) ($row['from_pct'] ?? 0);
            $toRaw = $row['to_pct'] ?? 0;
            $to = $toRaw === null || $toRaw === '' ? null : (float) $toRaw;
            $type = (string) ($row['type'] ?? 'percent_over_target');
            $value = (float) ($row['value'] ?? 0);

            return [
                'from_pct' => max(0, $from),
                'to_pct' => $to === null ? null : max($from, $to),
                'type' => $type,
                'value' => max(0, $value),
            ];
        })->filter(function ($row) {
            return $row['value'] > 0;
        })->sortBy('from_pct')->values()->all();

        return $clean;
    }

    public function calculateDynamicIncentive(float $achievedAmount, ?float $overridePercent = null): float
    {
        $targetAmount = (float) ($this->max_target ?: $this->min_target ?: 0);
        $achievedAmount = max(0, $achievedAmount);
        $mode = (string) ($this->incentive_mode ?: 'percent_over_target');
        $value = (float) ($this->incentive_value ?? 0);

        // Backward compatible fallback.
        if ($overridePercent !== null && $mode === 'percent_over_target' && $value <= 0) {
            $value = max(0, $overridePercent);
        }

        if ($mode === 'fixed_on_achieve') {
            return $achievedAmount >= $targetAmount && $targetAmount > 0 ? $value : 0;
        }

        if ($mode === 'percent_on_achieved') {
            if ($achievedAmount < $targetAmount || $targetAmount <= 0) {
                return 0;
            }
            return ($achievedAmount * $value) / 100;
        }

        if ($mode === 'slab') {
            if ($targetAmount <= 0 || $achievedAmount <= 0) {
                return 0;
            }

            $achievedPct = ($achievedAmount / $targetAmount) * 100;
            $matched = null;
            foreach ($this->normalizeSlabs() as $slab) {
                $from = (float) ($slab['from_pct'] ?? 0);
                $to = $slab['to_pct'] ?? null;
                $inRange = $achievedPct >= $from && ($to === null || $achievedPct <= (float) $to);
                if ($inRange) {
                    $matched = $slab;
                }
            }

            if (!$matched) {
                return 0;
            }

            $slabType = (string) ($matched['type'] ?? 'percent_over_target');
            $slabValue = (float) ($matched['value'] ?? 0);

            if ($slabType === 'fixed_on_achieve') {
                return $slabValue;
            }
            if ($slabType === 'percent_on_achieved') {
                return ($achievedAmount * $slabValue) / 100;
            }
            $extra = max(0, $achievedAmount - $targetAmount);
            return ($extra * $slabValue) / 100;
        }

        // percent_over_target
        $extra = max(0, $achievedAmount - $targetAmount);
        return ($extra * $value) / 100;
    }
}
