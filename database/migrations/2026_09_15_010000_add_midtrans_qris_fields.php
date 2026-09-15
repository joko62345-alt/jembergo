<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->string('order_id')->nullable()->unique()->after('id_pemesanan');
            $table->string('transaction_id')->nullable()->index()->after('order_id');
            $table->string('transaction_status')->nullable()->after('status_pembayaran');
            $table->string('payment_type')->nullable()->after('transaction_status');
            $table->decimal('gross_amount', 12, 2)->nullable()->after('nominal');
            $table->dateTime('paid_at')->nullable()->after('waktu_pembayaran');
            $table->text('qris_url')->nullable()->after('referensi_gateway');
            $table->dateTime('qris_expires_at')->nullable()->after('qris_url');
        });

        Schema::table('perubahan_pemesanan', function (Blueprint $table): void {
            $table->string('order_id')->nullable()->unique()->after('id_pemesanan');
            $table->string('transaction_id')->nullable()->index()->after('order_id');
            $table->string('transaction_status')->nullable()->after('status');
            $table->string('payment_type')->nullable()->after('transaction_status');
            $table->decimal('gross_amount', 12, 2)->nullable()->after('nominal');
            $table->dateTime('paid_at')->nullable()->after('waktu_pembayaran');
            $table->text('qris_url')->nullable()->after('referensi_gateway');
            $table->dateTime('qris_expires_at')->nullable()->after('qris_url');
        });
    }

    public function down(): void
    {
        Schema::table('perubahan_pemesanan', function (Blueprint $table): void {
            $table->dropUnique(['order_id']);
            $table->dropColumn(['order_id', 'transaction_id', 'transaction_status', 'payment_type', 'gross_amount', 'paid_at', 'qris_url', 'qris_expires_at']);
        });

        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->dropUnique(['order_id']);
            $table->dropColumn(['order_id', 'transaction_id', 'transaction_status', 'payment_type', 'gross_amount', 'paid_at', 'qris_url', 'qris_expires_at']);
        });
    }
};
