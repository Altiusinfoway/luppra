<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use UsesTenantConnection;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'event_key',
        'subject_type',
        'subject_id',
        'reference_type',
        'reference_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForSubject(Builder $query, Model|string $subject, int|string|null $subjectId = null): Builder
    {
        [$resolvedType, $resolvedId] = static::resolveTypeAndId($subject, $subjectId);

        return $query->where('subject_type', $resolvedType)
            ->where('subject_id', $resolvedId);
    }

    public function scopeForReference(Builder $query, Model|string $reference, int|string|null $referenceId = null): Builder
    {
        [$resolvedType, $resolvedId] = static::resolveTypeAndId($reference, $referenceId);

        return $query->where('reference_type', $resolvedType)
            ->where('reference_id', $resolvedId);
    }

    public function scopeForRecordHistory(Builder $query, Model|string $subject, int|string|null $subjectId = null): Builder
    {
        [$resolvedType, $resolvedId] = static::resolveTypeAndId($subject, $subjectId);

        return $query->where(function (Builder $historyQuery) use ($resolvedType, $resolvedId) {
            $historyQuery->where(function (Builder $directQuery) use ($resolvedType, $resolvedId) {
                $directQuery->where('subject_type', $resolvedType)
                    ->where('subject_id', $resolvedId);
            })->orWhere(function (Builder $relatedQuery) use ($resolvedType, $resolvedId) {
                $relatedQuery->where('reference_type', $resolvedType)
                    ->where('reference_id', $resolvedId);
            });
        });
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public static function resolveTypeAndId(Model|string $subject, int|string|null $subjectId = null): array
    {
        if ($subject instanceof Model) {
            return [$subject->getMorphClass(), (int) $subject->getKey()];
        }

        if ($subjectId === null) {
            throw new \InvalidArgumentException('The record id is required when passing a subject type string.');
        }

        if (is_string($subject) && class_exists($subject) && is_subclass_of($subject, Model::class)) {
            /** @var \Illuminate\Database\Eloquent\Model $instance */
            $instance = new $subject();

            return [$instance->getMorphClass(), (int) $subjectId];
        }

        return [(string) $subject, (int) $subjectId];
    }
}
