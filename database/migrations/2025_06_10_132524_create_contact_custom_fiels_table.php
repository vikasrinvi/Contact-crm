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
        Schema::create('contact_custom_fields', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');
            
            $table->foreignId('custom_field_definition_id')->constrained('custom_field_definitions')->onDelete('cascade');
            $table->text('value')->nullable(); 
            $table->timestamps();

           
            $table->unique(['contact_id', 'custom_field_definition_id'], 'contact_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_custom_fields');
    }
};