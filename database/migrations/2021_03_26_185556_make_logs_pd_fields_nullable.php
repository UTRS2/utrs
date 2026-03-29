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
        // revert to previous sizes / nullability (adjust if your original schema differs)
        DB::statement("ALTER TABLE `log_entries` 
            MODIFY `ip` VARCHAR(191) NOT NULL, 
            MODIFY `ua` TEXT NOT NULL");
    }
}
