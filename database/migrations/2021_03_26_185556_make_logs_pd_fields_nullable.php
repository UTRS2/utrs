<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeLogsPdFieldsNullable extends Migration
{
    public function up()
    {
        // ensure ip is large enough and nullable, ua nullable
        DB::statement("ALTER TABLE `log_entries` 
            MODIFY `ip` VARCHAR(400) NULL, 
            MODIFY `ua` TEXT NULL");
    }

    public function down()
    {
        // keep nullable so rollback doesn't fail when rows have NULL ip/ua
        DB::statement("ALTER TABLE `log_entries` 
            MODIFY `ip` VARCHAR(400) NULL, 
            MODIFY `ua` TEXT NULL");
    }
}
