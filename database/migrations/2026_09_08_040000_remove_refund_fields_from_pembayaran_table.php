<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS pembayaran_status_refund_index');
        }

        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->dropColumn([
                'status_refund',
                'nominal_refund',
                'refund_key_gateway',
                'waktu_refund_diajukan',
                'waktu_refund_dikonfirmasi',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->string('status_refund')->nullable()->index();
            $table->decimal('nominal_refund', 12, 2)->nullable();
            $table->string('refund_key_gateway')->nullable();
            $table->dateTime('waktu_refund_diajukan')->nullable();
            $table->dateTime('waktu_refund_dikonfirmasi')->nullable();
        });
    }
};
