<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('customer')
            ->whereNotIn('auth_provider', ['manual', 'google'])
            ->update(['auth_provider' => 'manual']);

        Schema::table('customer', function (Blueprint $table) {
            $table->enum('auth_provider', ['manual', 'google'])->default('manual')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->string('auth_provider')->default('manual')->change();
        });
    }
};
