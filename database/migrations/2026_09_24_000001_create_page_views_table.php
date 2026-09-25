<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path', 190);
            $table->string('page', 64)->nullable();
            $table->string('locale', 8)->nullable();
            $table->date('viewed_on');
            $table->string('visitor', 64);
            $table->timestamps();

            $table->index(['viewed_on', 'path']);
            $table->index('visitor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
