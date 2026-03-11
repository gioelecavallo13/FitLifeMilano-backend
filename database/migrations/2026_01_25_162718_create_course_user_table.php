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
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        
        // Relazione con il Coach (che è un User)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        $table->decimal('price', 8, 2); // Costo (es. 50.00)
        $table->string('day_of_week'); // Lunedì, Martedì...
        $table->time('start_time');
        $table->time('end_time');
        $table->integer('capacity'); // Posti totali
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_user');
    }
};
