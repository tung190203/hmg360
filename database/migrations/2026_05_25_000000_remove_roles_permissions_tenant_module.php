<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules')->where('slug', 'roles_permissions')->delete();

        $permissionIds = DB::table('permissions')
            ->where('module', 'roles_permissions')
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }

    public function down(): void
    {
        // Module files were removed; restoring the DB record alone would create a broken menu entry.
    }
};
