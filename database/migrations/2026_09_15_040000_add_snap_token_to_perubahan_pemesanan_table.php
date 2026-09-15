<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perubahan_pemesanan', function (Blueprint $table): void {
            $table->text('snap_token')->nullable()->after('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('perubahan_pemesanan', function (Blueprint $table): void {
            $table->dropColumn('snap_token');
        });
    }
};
