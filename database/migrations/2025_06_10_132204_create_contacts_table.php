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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique(); 
            $table->string('phone')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable(); 
            $table->string('profile_image')->nullable(); 
            $table->string('additional_file')->nullable(); 
            $table->softDeletes();


            $table->enum('merge_status', ['active', 'merged', 'inactive'])->default('active');
            // $table->unsignedBigInteger('merged_into_contact_id')->nullable(); 

            $table->timestamps(); 
            $table->boolean('is_merged')->default(false); 
            $table->foreignId('merged_into_contact_id')
                  ->nullable()
                  ->constrained('contacts') 
                  ->onDelete('set null');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};