<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Allow 'web' as an order source so online orders forwarded from genz-web-apis
 * can be tagged distinctly from POS and foodpanda orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN source ENUM('pos','foodpanda','web') NOT NULL DEFAULT 'pos'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN source ENUM('pos','foodpanda') NOT NULL DEFAULT 'pos'");
    }
};
