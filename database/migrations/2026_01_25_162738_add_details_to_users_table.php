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
        // Rimuoviamo il vecchio campo 'name' che crea conflitto
        $table->dropColumn('name'); 
        
        // Aggiungiamo i nuovi campi
        $table->string('first_name')->after('id');
        $table->string('last_name')->after('first_name');
        $table->enum('role', ['admin', 'coach', 'client'])->default('client')->after('password');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('name')->after('id');
        $table->dropColumn(['first_name', 'last_name', 'role']);
    });
}
};
