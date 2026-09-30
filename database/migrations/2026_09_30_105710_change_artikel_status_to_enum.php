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
        DB::table('artikel')
            ->whereNotIn('status', ['DRAFT', 'PUBLISHED'])
            ->update(['status' => 'DRAFT']);

        Schema::table('artikel', function (Blueprint $table) {
            $table->enum('status', ['DRAFT', 'PUBLISHED'])->default('DRAFT')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->string('status')->default('DRAFT')->change();
        });
    }
};
