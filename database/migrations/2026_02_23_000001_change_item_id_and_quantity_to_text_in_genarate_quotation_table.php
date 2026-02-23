<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `genarate_quotation` MODIFY `item_id` TEXT NULL");
        DB::statement("ALTER TABLE `genarate_quotation` MODIFY `quantity` TEXT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `genarate_quotation` MODIFY `item_id` BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `genarate_quotation` MODIFY `quantity` INT NULL");
    }
};
