<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = ['role', 'permission', 'allowed'];

    protected function casts(): array
    {
        return ['allowed' => 'boolean'];
    }

    public static function isAllowed(string $role, string $permission): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $record = self::where('role', $role)->where('permission', $permission)->first();

        if ($record) {
            return $record->allowed;
        }

        return true;
    }

    public static function syncDefaults(): void
    {
        $permissions = config('permissions', []);

        foreach ($permissions as $role => $items) {
            foreach (array_keys($items) as $permission) {
                self::firstOrCreate(
                    ['role' => $role, 'permission' => $permission],
                    ['allowed' => true]
                );
            }
        }
    }
}
