<?php

namespace App\Services;

use App\Models\RolePermission;
use Illuminate\Support\Facades\Auth;

class PermissionChecker
{
    public static function allows(string $permission): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return RolePermission::isAllowed($user->role, $permission);
    }

    public static function denyUnless(string $permission): void
    {
        if (! self::allows($permission)) {
            abort(403, 'Anda tidak memiliki izin untuk fitur ini.');
        }
    }
}
