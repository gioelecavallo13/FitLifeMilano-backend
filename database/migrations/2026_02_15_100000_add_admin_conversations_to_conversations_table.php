<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable()->change();
            $table->foreignId('client_id')->nullable()->change();
            $table->foreignId('admin_id')->nullable()->after('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('other_user_id')->nullable()->after('admin_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['admin_id', 'other_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique(['admin_id', 'other_user_id']);
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['other_user_id']);
            $table->foreignId('coach_id')->nullable(false)->change();
            $table->foreignId('client_id')->nullable(false)->change();
        });
    }
};
