<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::allResolved();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'landing_hero_title' => 'required|string|max:200',
            'landing_hero_subtitle' => 'required|string',
            'landing_about_title' => 'required|string|max:100',
            'landing_about_description' => 'required|string',
        ]);

        $oldCompanyName = SystemSetting::getVal('company_name');
        $newCompanyName = $request->company_name;

        SystemSetting::setVal('company_name', $newCompanyName);
        SystemSetting::setVal('landing_hero_title', $request->landing_hero_title);

        $textFields = [
            'landing_hero_subtitle' => $request->landing_hero_subtitle,
            'landing_about_title' => $request->landing_about_title,
            'landing_about_description' => $request->landing_about_description,
        ];

        foreach ($textFields as $key => $value) {
            if ($oldCompanyName && $oldCompanyName !== $newCompanyName) {
                $value = str_replace($oldCompanyName, '{company_name}', $value);
            }
            SystemSetting::setVal($key, SystemSetting::normalizeCompanyNamePlaceholder($value, $newCompanyName));
        }

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $logo = $request->file('company_logo');
            $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
            
            // Store directly in public folder for easy access like original code
            $logo->move(public_path(), $logoName);
            
            // Delete old logo if it's not the default logo.png
            $oldLogo = SystemSetting::getVal('company_logo');
            if ($oldLogo && $oldLogo !== 'logo.png' && file_exists(public_path($oldLogo))) {
                @unlink(public_path($oldLogo));
            }
            
            SystemSetting::setVal('company_logo', $logoName);
        }

        AuditLogger::log('update', 'settings', 'Pengaturan sistem diperbarui');

        return redirect()->route('admin.settings.index')->with('success', 'Konfigurasi sistem berhasil diperbarui!');
    }
}
