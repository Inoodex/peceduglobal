<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->observeModelsInAppModels();
    }

    private function observeModelsInAppModels(): void
    {
        foreach (glob(app_path('Models') . '/*.php') as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (! class_exists($class) || $class === ActivityLog::class) {
                continue;
            }

            $class::observe(ActivityLogObserver::class);
        }
    }
}
