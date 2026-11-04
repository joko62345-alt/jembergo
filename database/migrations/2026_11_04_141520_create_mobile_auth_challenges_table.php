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
        Schema::create('mobile_auth_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('purpose', 32);
            $table->string('token');
            $table->json('payload');
            $table->timestamp('created_at');
            $table->unique(['email', 'purpose']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_auth_challenges');
    }
};
