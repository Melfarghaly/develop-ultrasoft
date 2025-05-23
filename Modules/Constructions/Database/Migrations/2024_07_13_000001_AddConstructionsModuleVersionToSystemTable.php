<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddConstructionsModuleVersionToSystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $is_exist = DB::table('system')->where('key', 'constructions_version')->exists();

        if (! $is_exist) {
            DB::table('system')->insert([
                'key' => 'constructions_version',
                'value' => config('constructions.module_version', config('constructions.module_version')),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
} 