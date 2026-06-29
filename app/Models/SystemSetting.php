<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getVal($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setVal($key, $value)
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Load all settings with {company_name} resolved for display.
     */
    public static function allResolved(): array
    {
        $settings = self::pluck('value', 'key')->all();
        $companyName = $settings['company_name'] ?? '';

        $textKeys = ['landing_hero_subtitle', 'landing_about_title', 'landing_about_description'];

        foreach ($textKeys as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = str_replace('{company_name}', $companyName, $settings[$key]);
            }
        }

        return $settings;
    }

    /**
     * Replace company name in text fields with {company_name} placeholder for storage.
     */
    public static function normalizeCompanyNamePlaceholder(string $text, string $companyName): string
    {
        if ($companyName !== '') {
            $text = str_replace($companyName, '{company_name}', $text);
        }

        return $text;
    }
}
