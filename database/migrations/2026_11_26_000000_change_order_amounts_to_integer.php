<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Суммы заказа: float → целые копейки (BIGINT).
 * FLOAT в MySQL хранит ~7 значащих цифр, поэтому крупные суммы в копейках искажались.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->update([
            'amount_total' => DB::raw('ROUND(amount_total)'),
            'amount_payed' => DB::raw('ROUND(amount_payed)'),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('amount_total')->change();
            $table->bigInteger('amount_payed')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('amount_total')->change();
            $table->float('amount_payed')->change();
        });
    }
};
