<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SuspendPrivacy extends Migration
{
    public function up()
    {
        // 1) add temporary columns
        Schema::table('appeals', function (Blueprint $table) {
            $table->integer('privacylevel_tmp')->nullable();
            $table->tinyInteger('privacyreview_tmp')->nullable();
        });

        // 2) migrate/clean existing values into tmp columns
        // - privacylevel: keep numeric values, else NULL
        // - privacyreview: map common truthy/falsy strings to 1/0, else NULL
        DB::statement("
            UPDATE appeals
            SET
              privacylevel_tmp = CASE WHEN privacylevel REGEXP '^[0-9]+$' THEN CAST(privacylevel AS SIGNED) ELSE NULL END,
              privacyreview_tmp = CASE
                WHEN LOWER(TRIM(COALESCE(privacyreview, ''))) IN ('1','true','t','yes','y') THEN 1
                WHEN LOWER(TRIM(COALESCE(privacyreview, ''))) IN ('0','false','f','no','n') THEN 0
                ELSE NULL
              END
        ");

        // 3) swap columns in one ALTER (drop old, rename tmp to final)
        // Note: this raw ALTER avoids using Schema::renameColumn/change which need doctrine/dbal
        DB::statement("
            ALTER TABLE appeals
              DROP COLUMN privacylevel,
              DROP COLUMN privacyreview,
              CHANGE COLUMN privacylevel_tmp privacylevel INT NULL,
              CHANGE COLUMN privacyreview_tmp privacyreview TINYINT(1) NULL
        ");
    }

    public function down()
    {
        // reverse: create temporary columns of previous types (best effort)
        Schema::table('appeals', function (Blueprint $table) {
            $table->string('privacylevel_tmp', 32)->nullable();
            $table->string('privacyreview_tmp', 8)->nullable();
        });

        // copy/cast values back to string forms
        DB::statement("
            UPDATE appeals
            SET
              privacylevel_tmp = CASE WHEN privacylevel IS NULL THEN NULL ELSE CAST(privacylevel AS CHAR) END,
              privacyreview_tmp = CASE WHEN privacyreview IS NULL THEN NULL WHEN privacyreview = 1 THEN '1' ELSE '0' END
        ");

        // swap back (drop numeric, rename tmp to string)
        DB::statement("
            ALTER TABLE appeals
              DROP COLUMN privacylevel,
              DROP COLUMN privacyreview,
              CHANGE COLUMN privacylevel_tmp privacylevel VARCHAR(32) NULL,
              CHANGE COLUMN privacyreview_tmp privacyreview VARCHAR(8) NULL
        ");
    }
}