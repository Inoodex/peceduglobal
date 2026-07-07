<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('created', $model->generateLogDescription('created'), null, $model->toArray());
        });

        static::updated(function ($model) {
            if (!$model->isLoggableUpdate()) return;
            $old = $model->getOriginal();
            $changes = [];
            foreach ($model->getDirty() as $key => $value) {
                if (!in_array($key, ['updated_at', 'remember_token'])) {
                    $changes[$key] = ['old' => $old[$key] ?? null, 'new' => $value];
                }
            }
            if (empty($changes)) return;
            $model->logActivity('updated', $model->generateLogDescription('updated'), $old, $model->toArray());
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->generateLogDescription('deleted'), $model->toArray(), null);
        });
    }

    protected function isLoggableUpdate(): bool
    {
        return true;
    }

    protected function logActivity(string $event, string $description, $oldValues = null, $newValues = null): void
    {
        $user = auth('api')->user();
        ActivityLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'loggable_type' => static::class,
            'loggable_id' => $this->getKey(),
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    protected function generateLogDescription(string $event): string
    {
        $name = method_exists($this, 'logTitle') ? $this->logTitle() : ($this->title ?? $this->name ?? $this->full_name ?? '#' . $this->getKey());
        $modelName = class_basename(static::class);
        return ucfirst("{$event} {$modelName} '{$name}'");
    }
}
