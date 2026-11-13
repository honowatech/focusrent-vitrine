<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query', 80);
            $table->string('normalized', 80)->index();
            $table->string('locale', 8);
            $table->string('source', 16);
            $table->string('token', 32)->nullable()->unique();
            $table->unsignedInteger('results_count')->default(0);
            $table->string('clicked_id', 120)->nullable();
            $table->string('clicked_type', 32)->nullable();
            $table->unsignedSmallInteger('clicked_position')->nullable();
            $table->string('visitor_id', 64)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->timestamp('searched_at')->index();
            $table->timestamps();

            $table->index(['searched_at', 'locale', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
