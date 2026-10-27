<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $customersByPhone = DB::table('customer')
            ->whereNotNull('no_hp')
            ->orderBy('id_customer')
            ->get(['id_customer', 'no_hp'])
            ->groupBy('no_hp');

        foreach ($customersByPhone as $customers) {
            if ($customers->count() <= 1) {
                continue;
            }

            $duplicateIds = $customers->skip(1)->pluck('id_customer');

            DB::table('customer')
                ->whereIn('id_customer', $duplicateIds)
                ->update(['no_hp' => null]);
        }

        Schema::table('customer', function (Blueprint $table): void {
            $table->unique('no_hp', 'customer_no_hp_unique');
        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table): void {
            $table->dropUnique('customer_no_hp_unique');
        });
    }
};
