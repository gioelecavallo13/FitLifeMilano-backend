<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migra profile_photo da path filesystem a BLOB nel database.
     * I dati esistenti (path) vengono azzerati: gli utenti dovranno ricaricare la foto.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->binary('profile_photo')->nullable()->after('role');
            $table->string('profile_photo_mime', 50)->nullable()->after('profile_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_photo', 'profile_photo_mime']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('role');
        });
    }
};
