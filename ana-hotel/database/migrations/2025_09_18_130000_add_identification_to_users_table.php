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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'identification_type')) {
                $table->enum('identification_type', ['passport', 'id_card', 'driving_license'])->nullable()->after('profile_photo_path');
            }
            if (!Schema::hasColumn('users', 'identification_number')) {
                $table->string('identification_number', 100)->nullable()->after('identification_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'identification_number')) {
                $table->dropColumn('identification_number');
            }
            if (Schema::hasColumn('users', 'identification_type')) {
                $table->dropColumn('identification_type');
            }
        });
    }
};
