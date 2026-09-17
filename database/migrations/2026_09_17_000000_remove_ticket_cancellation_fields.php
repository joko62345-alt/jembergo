<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiket', function (Blueprint $table): void {
            $table->dropColumn('waktu_pembatalan');
        });
    }

    public function down(): void
    {
        Schema::table('tiket', function (Blueprint $table): void {
            $table->dateTime('waktu_pembatalan')->nullable();
        });
    }
};
