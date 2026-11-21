<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->decimal('uang_diterima', 12, 2)->nullable()->after('nominal');
            $table->decimal('uang_kembalian', 12, 2)->nullable()->after('uang_diterima');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->dropColumn(['uang_diterima', 'uang_kembalian']);
        });
    }
};
