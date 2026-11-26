<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE `jenis_tiket` ADD CONSTRAINT `jenis_tiket_harga_max_check` CHECK (`harga` <= 999999)'
        );
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE `jenis_tiket` DROP CHECK `jenis_tiket_harga_max_check`'
        );
    }
};
