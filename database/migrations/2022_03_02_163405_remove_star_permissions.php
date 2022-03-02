<?php

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;

class RemoveStarPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // first, migrate manually granted access (developer and tooladmin) from the '*' wiki to the global one
        // Don't bother with automatically assigned permissions, those will be fixed automatically
        $manualPermissions = Permission::query()
            ->where('wiki', '=', '*')
            ->where(function (Builder $query) {
                $query->where('developer', '=', '1')
                    ->orWhere('tooladmin', '=', '1');
            });

        foreach ($manualPermissions->get() as $permission) {
            /** @var Permission $permission */
            $newPerm = Permission::firstOrNew([
                'user_id' => $permission->user_id,
                'wiki' => 'global',
            ]);

            if ($permission->tooladmin) {
                $newPerm->tooladmin = true;
            }
            if ($permission->developer) {
                $newPerm->developer = true;
            }

            $newPerm->save();
        }

        Permission::query()
            ->where('wiki', '=', '*')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration can not be reversed easily. Hopefully you had backups!
    }
}
