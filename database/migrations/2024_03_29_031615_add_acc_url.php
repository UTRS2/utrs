<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add url field to acc table
        Schema::table('accs', function (Blueprint $table) {
            $table->string('url')->nullable();
        });

        // change the acc_id field to be nullable  
        Schema::table('accs', function (Blueprint $table) {
            $table->unsignedBigInteger('acc_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop url field from acc table
        Schema::table('accs', function (Blueprint $table) {
            $table->dropColumn('url');
        });

        // change the acc_id field to be not nullable
        Schema::table('accs', function (Blueprint $table) {
            $table->unsignedBigInteger('acc_id')->nullable(false)->change();
        });
    }
};
