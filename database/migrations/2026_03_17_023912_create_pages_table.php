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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('icon')->default('question-mark-circle');
            $table->string('route')->nullable();
            $table->integer('order')->nullable();
            $table->boolean('state')->default(1);
            $table->enum('type',['header','parent','page'])->default('page');
            $table->string('permission_name')->nullable();
            $table->foreignId('page_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
