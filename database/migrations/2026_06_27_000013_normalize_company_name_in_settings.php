<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('system_settings')) {
            return;
        }

        $companyName = DB::table('system_settings')->where('key', 'company_name')->value('value');
        $defaultOldName = 'PT. PUTRI KEBUN LESTARI';
        $textKeys = ['landing_hero_subtitle', 'landing_about_title', 'landing_about_description'];

        foreach ($textKeys as $key) {
            $value = DB::table('system_settings')->where('key', $key)->value('value');

            if (! $value) {
                continue;
            }

            if ($companyName) {
                $value = str_replace($companyName, '{company_name}', $value);
            }

            if ($companyName && $companyName !== $defaultOldName) {
                $value = str_replace($defaultOldName, '{company_name}', $value);
            }

            DB::table('system_settings')->where('key', $key)->update(['value' => $value]);
        }
    }

    public function down(): void
    {
        //
    }
};
