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
        DB::table('admin_pariwisata')
            ->whereNotIn('status_akun', ['AKTIF', 'NONAKTIF'])
            ->update(['status_akun' => 'NONAKTIF']);

        Schema::table('admin_pariwisata', function (Blueprint $table) {
            $table->enum('status_akun', ['AKTIF', 'NONAKTIF'])->default('AKTIF')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_pariwisata', function (Blueprint $table) {
            $table->string('status_akun')->default('AKTIF')->change();
        });
    }
};
