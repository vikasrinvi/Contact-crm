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
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('field_name')->unique();
            $table->string('field_type'); 
            $table->boolean('is_required')->default(false); 
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('custom_field_definitions');
    }
};