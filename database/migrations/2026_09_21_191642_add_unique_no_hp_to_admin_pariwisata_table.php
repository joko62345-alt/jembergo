<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_pariwisata', function (Blueprint $table) {
            $table->unique('no_hp', 'admin_pariwisata_no_hp_unique');
        });
    }

    public function down(): void
    {
        Schema::table('admin_pariwisata', function (Blueprint $table) {
            $table->dropUnique('admin_pariwisata_no_hp_unique');
        });
    }
};
