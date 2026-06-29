<?php

namespace App\Providers;

use App\Models\RolePermission;
use App\Services\MailConfigurator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
            \Illuminate\Support\Facades\View::share('settings', \App\Models\SystemSetting::allResolved());
            MailConfigurator::applyFromSettings();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('role_permissions')) {
            RolePermission::syncDefaults();
        }
    }
}
