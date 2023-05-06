<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_statement', function (Blueprint $table) {
            $table->id();
            $table->string('date')->nullable();
            $table->string('narration')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('money_in')->nullable();
            $table->string('money_out')->nullable();
            $table->string('balance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_statement');
    }
};