<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PAYMENT_COLUMNS = [
        'order_id',
        'snap_token',
        'transaction_id',
        'transaction_status',
        'payment_type',
        'gross_amount',
        'paid_at',
        'qris_url',
        'qris_expires_at',
        'metode_pembayaran',
        'referensi_gateway',
        'waktu_pembayaran',
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('pembayaran', 'id_perubahan')) {
            Schema::table('pembayaran', function (Blueprint $table): void {
                $table->foreignId('id_perubahan')
                    ->nullable()
                    ->after('id_pemesanan')
                    ->constrained('perubahan_pemesanan', 'id_perubahan')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('pembayaran', 'pembayaran_id_perubahan_unique')) {
            Schema::table('pembayaran', function (Blueprint $table): void {
                $table->unique('id_perubahan', 'pembayaran_id_perubahan_unique');
            });
        }

        if (! Schema::hasIndex('pembayaran', 'pembayaran_id_pemesanan_index')) {
            Schema::table('pembayaran', function (Blueprint $table): void {
                $table->index('id_pemesanan', 'pembayaran_id_pemesanan_index');
            });
        }

        if (Schema::hasIndex('pembayaran', 'pembayaran_id_pemesanan_unique')) {
            Schema::table('pembayaran', function (Blueprint $table): void {
                $table->dropUnique('pembayaran_id_pemesanan_unique');
            });
        }

        $changes = DB::table('perubahan_pemesanan')
            ->where(function ($query): void {
                foreach (self::PAYMENT_COLUMNS as $column) {
                    $query->orWhereNotNull($column);
                }
            })
            ->orderBy('id_perubahan')
            ->get();

        foreach ($changes as $change) {
            DB::table('pembayaran')->insert([
                'id_pemesanan' => $change->id_pemesanan,
                'id_perubahan' => $change->id_perubahan,
                'order_id' => $change->order_id,
                'snap_token' => $change->snap_token,
                'transaction_id' => $change->transaction_id,
                'transaction_status' => $change->transaction_status,
                'payment_type' => $change->payment_type,
                'gross_amount' => $change->gross_amount,
                'paid_at' => $change->paid_at,
                'qris_url' => $change->qris_url,
                'qris_expires_at' => $change->qris_expires_at,
                'metode_pembayaran' => $change->metode_pembayaran,
                'referensi_gateway' => $change->referensi_gateway,
                'waktu_pembayaran' => $change->waktu_pembayaran,
                'nominal' => $change->nominal,
                'status_pembayaran' => $change->status,
                'created_at' => $change->created_at,
                'updated_at' => $change->updated_at,
            ]);
        }

        Schema::table('perubahan_pemesanan', function (Blueprint $table): void {
            $table->dropUnique(['order_id']);
            $table->dropColumn(self::PAYMENT_COLUMNS);
        });
    }

    public function down(): void
    {
        Schema::table('perubahan_pemesanan', function (Blueprint $table): void {
            $table->string('order_id')->nullable()->unique();
            $table->text('snap_token')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_status')->nullable();
            $table->string('payment_type')->nullable();
            $table->decimal('gross_amount', 12, 2)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->text('qris_url')->nullable();
            $table->dateTime('qris_expires_at')->nullable();
            $table->string('metode_pembayaran')->nullable();
            $table->string('referensi_gateway')->nullable();
            $table->dateTime('waktu_pembayaran')->nullable();
        });

        $changePayments = DB::table('pembayaran')
            ->whereNotNull('id_perubahan')
            ->get();

        foreach ($changePayments as $payment) {
            DB::table('perubahan_pemesanan')
                ->where('id_perubahan', $payment->id_perubahan)
                ->update([
                    'order_id' => $payment->order_id,
                    'snap_token' => $payment->snap_token,
                    'transaction_id' => $payment->transaction_id,
                    'transaction_status' => $payment->transaction_status,
                    'payment_type' => $payment->payment_type,
                    'gross_amount' => $payment->gross_amount,
                    'paid_at' => $payment->paid_at,
                    'qris_url' => $payment->qris_url,
                    'qris_expires_at' => $payment->qris_expires_at,
                    'metode_pembayaran' => $payment->metode_pembayaran,
                    'referensi_gateway' => $payment->referensi_gateway,
                    'waktu_pembayaran' => $payment->waktu_pembayaran,
                ]);
        }

        DB::table('pembayaran')->whereNotNull('id_perubahan')->delete();

        Schema::table('pembayaran', function (Blueprint $table): void {
            $table->unique('id_pemesanan', 'pembayaran_id_pemesanan_unique');
            $table->dropUnique('pembayaran_id_perubahan_unique');
            $table->dropForeign(['id_perubahan']);
            $table->dropColumn('id_perubahan');
            $table->dropIndex('pembayaran_id_pemesanan_index');
        });
    }
};
