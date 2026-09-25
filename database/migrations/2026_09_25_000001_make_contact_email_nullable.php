<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contact_messages MODIFY email VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('UPDATE contact_messages SET email = \'\' WHERE email IS NULL');
            DB::statement('ALTER TABLE contact_messages MODIFY email VARCHAR(255) NOT NULL');
        }
    }
};
