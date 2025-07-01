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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); 
            $table->unsignedBigInteger('auditable_id'); 
            $table->index(['auditable_type', 'auditable_id']); 
            $table->string('event'); 
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); 

            $table->json('old_values')->nullable(); 
            $table->json('new_values')->nullable(); 
            $table->json('custom_details')->nullable(); 

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('url', 2048)->nullable();

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};