<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SetDatabaseTimezone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Set MySQL timezone to match system timezone
        DB::statement("SET time_zone = '-07:00';");
        
        // You can also set it permanently by updating MySQL config
        // For now, this will ensure the session uses the correct timezone
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to UTC
        DB::statement("SET time_zone = '+00:00';");
    }
}
