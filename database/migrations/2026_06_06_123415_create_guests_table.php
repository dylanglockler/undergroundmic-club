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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stage_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('method'); // email | calendar | text
            $table->string('contact'); // email address or phone number
            $table->string('reminder_time')->default('1day'); // 1week | 1day | dayof
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
