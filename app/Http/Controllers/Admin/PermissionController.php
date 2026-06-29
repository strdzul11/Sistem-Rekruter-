<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        RolePermission::syncDefaults();

        $permissions = config('permissions');
        $matrix = [];

        foreach ($permissions as $role => $items) {
            foreach ($items as $key => $label) {
                $matrix[$key]['label'] = $label;
                $matrix[$key][$role] = RolePermission::isAllowed($role, $key);
            }
        }

        return view('admin.permissions.index', compact('matrix', 'permissions'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'nullable|boolean',
        ]);

        $definitions = config('permissions');

        foreach ($definitions as $role => $items) {
            foreach (array_keys($items) as $permission) {
                $allowed = (bool) ($request->input("permissions.{$role}.{$permission}", false));
                RolePermission::updateOrCreate(
                    ['role' => $role, 'permission' => $permission],
                    ['allowed' => $allowed]
                );
            }
        }

        AuditLogger::log('update', 'permissions', 'Hak akses role diperbarui');

        return back()->with('success', 'Hak akses role berhasil diperbarui.');
    }
}
